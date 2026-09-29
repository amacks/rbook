<?php
/**
 * PHPUnit bootstrap for rbook.
 *
 * Defines app constants from environment variables so the model classes
 * can connect to the test database, then loads shared infrastructure helpers.
 *
 * Environment variables (all optional — sensible defaults for local Docker):
 *   TEST_DB_HOST      MySQL host for tests (default: 127.0.0.1)
 *   TEST_DB_NAME      MySQL database name  (default: rbook_test)
 *   TEST_DB_USER      MySQL username       (default: root)
 *   TEST_DB_PASSWORD  MySQL password       (default: foobar)
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Define DB constants used by helpers/db.php (rb_get_pdo) before any class loads
define('DBHOST',     getenv('TEST_DB_HOST')     ?: '127.0.0.1');
define('DBNAME',     getenv('TEST_DB_NAME')     ?: 'rbook_test');
define('DBUSER',     getenv('TEST_DB_USER')     ?: 'root');
define('DBPASSWORD', getenv('TEST_DB_PASSWORD') ?: 'foobar');
define('DBPORT',     getenv('TEST_DB_PORT')     ?: '3306');

// Minimal app constants needed by classes at load time
define('APPROOT',    '/');
define('DEBUG',      false);
define('LANGUAGE',   'en');

// PDO wrapper (RbDb, RbResult, DB_FETCHMODE_* constants, rb_get_pdo())
require_once __DIR__ . '/../helpers/db.php';

// i18n message lookup — getMessage() used by Recipe constructor and installer
require_once __DIR__ . '/../helpers/resources.php';

// Logging helper — rb_log() called from base_record.php
require_once __DIR__ . '/../helpers/eh.php';
