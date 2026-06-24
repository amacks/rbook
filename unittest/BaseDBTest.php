<?php
require_once(dirname(__FILE__) . '/../install/db_installer.php');
require_once(dirname(__FILE__) . '/../install/mysql_db_installer.php');
require_once(dirname(__FILE__) . '/../classes/recipe.php');

class BaseDBTest extends PHPUnit\Framework\TestCase {

	public $installer;

	function tearDown(): void {
		// Close the PDO connection before dropping the database to avoid lock waits.
		if (function_exists('rb_reset_pdo')) rb_reset_pdo();
		$this->installer->uninstall();
	}

	function getInitialUser() {
		return "andrew";
	}

	function setUp(): void {
		// Reset PDO singleton so each test gets a fresh connection to the recreated DB.
		if (function_exists('rb_reset_pdo')) rb_reset_pdo();
		$installer = new MysqlDBInstaller();
		$this->installer = $installer;
		$installer->databaseName = "rbook_test";
		$installer->adminUser = "root";
		$installer->password = getenv('TEST_DB_PASSWORD') ?: "foobar";
		$installer->action = "fresh";
		// Use 127.0.0.1 to force TCP (not Unix socket) — works with Docker.
		// Override with TEST_DB_HOST env var if needed.
		$installer->databaseHost = getenv('TEST_DB_HOST') ?: "127.0.0.1";
		$installer->databasePort = getenv('TEST_DB_PORT') ?: "3306";
		$installer->initialUser = $this->getInitialUser();
		$installer->initialEmail = "andrew@foo.com";
		// Skip createDatabaseUser() — test connects directly as adminUser.
		$installer->dbUserName = '';
		$installer->install();
		error_reporting(E_ALL);
	}
}
