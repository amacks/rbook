<?php
/*
 * rbook Recipe Management System
 *
 * PDO database helper — replaces PEAR DB.
 * Provides RbDb and RbResult, which expose a PEAR DB-compatible interface
 * over PDO so existing callers need minimal changes.
 */

// PEAR DB fetch-mode constants — kept for backward compatibility with callers.
if (!defined('DB_FETCHMODE_ASSOC')) {
    define('DB_FETCHMODE_ASSOC', PDO::FETCH_ASSOC);
}
if (!defined('DB_FETCHMODE_ORDERED')) {
    define('DB_FETCHMODE_ORDERED', PDO::FETCH_NUM);
}

/**
 * Returns the shared PDO connection for this request.
 * A single PDO instance is reused across all getDb() calls within one request.
 */
/** @var PDO|null Singleton PDO connection, resettable for tests. */
$GLOBALS['_rb_pdo'] = null;

function rb_get_pdo(): PDO {
    if ($GLOBALS['_rb_pdo'] === null) {
        $dsn = 'mysql:host=' . DBHOST . ';port=' . (defined('DBPORT') ? DBPORT : '3306') . ';dbname=' . DBNAME . ';charset=utf8mb4';
        $GLOBALS['_rb_pdo'] = new PDO($dsn, DBUSER, DBPASSWORD, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $GLOBALS['_rb_pdo'];
}

/**
 * Resets the PDO singleton — used in tests to get a fresh connection
 * after the test database is dropped and recreated between test cases.
 */
function rb_reset_pdo(): void {
    if ($GLOBALS['_rb_pdo'] !== null) {
        try {
            if ($GLOBALS['_rb_pdo']->inTransaction()) {
                $GLOBALS['_rb_pdo']->rollBack();
            }
        } catch (Throwable $e) {
            // ignore — connection may already be gone
        }
    }
    unset($GLOBALS['_rb_pdo']);
    $GLOBALS['_rb_pdo'] = null;
}

/**
 * Wraps a PDOStatement to provide the fetchInto/fetchRow interface
 * that PEAR DB callers expect.
 */
class RbResult {
    private PDOStatement $stmt;

    public function __construct(PDOStatement $stmt) {
        $this->stmt = $stmt;
    }

    /**
     * Fetches the next row into $row and returns true, or returns false at EOF.
     * Mirrors PEAR DB's fetchInto($row, DB_FETCHMODE_ASSOC|DB_FETCHMODE_ORDERED).
     */
    public function fetchInto(&$row, int $mode = DB_FETCHMODE_ASSOC): bool {
        $fetchMode = ($mode === PDO::FETCH_NUM) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
        $row = $this->stmt->fetch($fetchMode);
        return $row !== false;
    }

    /**
     * Fetches and returns the next row, or false at EOF.
     * Mirrors PEAR DB's fetchRow(DB_FETCHMODE_ASSOC|DB_FETCHMODE_ORDERED).
     */
    public function fetchRow(int $mode = DB_FETCHMODE_ASSOC) {
        $fetchMode = ($mode === PDO::FETCH_NUM) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
        return $this->stmt->fetch($fetchMode);
    }

    public function rowCount(): int {
        return $this->stmt->rowCount();
    }
}

/**
 * Thin PDO wrapper that provides a PEAR DB-compatible interface.
 *
 * Key differences from PEAR DB:
 *  - nextId() is gone; use AUTO_INCREMENT + lastInsertId() instead.
 *  - Errors throw PDOExceptions (caught in BaseRecord::runQuery).
 *  - disconnect() is a no-op; the PDO connection is a per-request singleton.
 *  - autoCommit(false) is gone; call beginTransaction() to start a transaction.
 */
class RbDb {
    private PDO $pdo;
    private ?PDOStatement $lastStmt = null;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Executes a query, optionally with positional parameters.
     * Returns an RbResult on success; throws PDOException on failure.
     */
    public function query(string $sql, $params = null): RbResult {
        if ($params !== null) {
            $params = is_array($params) ? $params : [$params];
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $this->pdo->query($sql);
        }
        $this->lastStmt = $stmt;
        return new RbResult($stmt);
    }

    /**
     * Executes a query with LIMIT and OFFSET appended.
     * Replaces PEAR DB's limitQuery($sql, $offset, $limit, $params).
     */
    public function limitQuery(string $sql, int $offset, int $limit, $params = null): RbResult {
        $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        return $this->query($sql, $params);
    }

    /**
     * Prepares a statement and returns the raw PDOStatement.
     * Callers can then pass this to execute().
     */
    public function prepare(string $sql): PDOStatement {
        return $this->pdo->prepare($sql);
    }

    /**
     * Executes a previously prepared PDOStatement with the given parameters.
     * Replaces PEAR DB's execute($stmt, $params).
     */
    public function execute(PDOStatement $stmt, $params): RbResult {
        $params = is_array($params) ? $params : [$params];
        $stmt->execute($params);
        $this->lastStmt = $stmt;
        return new RbResult($stmt);
    }

    /**
     * Returns the auto-generated ID from the last INSERT.
     * Replaces PEAR DB's nextId() sequences.
     */
    public function lastInsertId(): int {
        return (int)$this->pdo->lastInsertId();
    }

    /** Starts a transaction if one is not already active. */
    public function beginTransaction(): void {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    /** Commits the active transaction (if any). */
    public function commit(): void {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    /** Rolls back the active transaction (if any). */
    public function rollback(): void {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     * No-op — the PDO connection is a per-request singleton and is not
     * explicitly closed.  Retained so existing callers compile unchanged.
     */
    public function disconnect(): void {}

    /** Returns the row count affected by the last INSERT/UPDATE/DELETE. */
    public function affectedRows(): int {
        return $this->lastStmt ? $this->lastStmt->rowCount() : 0;
    }

    /**
     * Escapes a string for safe use inside a quoted LIKE pattern.
     * Replaces PEAR DB's escapeSimple().
     */
    public function escapeSimple(string $str): string {
        return str_replace(
            ["\\",   "\0",  "\n",  "\r",  "\x1a", "'",   '"'],
            ["\\\\", "\\0", "\\n", "\\r", "\\Z",  "\\'", "\\\""],
            $str
        );
    }
}
