<?php
require_once(dirname(__FILE__) . '/../install/db_installer.php');
require_once(dirname(__FILE__) . '/../install/mysql_db_installer.php');
require_once(dirname(__FILE__) . '/../classes/recipe.php');

class BaseDBTest extends PHPUnit\Framework\TestCase {

	public $installer;

	function tearDown(): void {
		$this->installer->uninstall();
	}

	function getInitialUser() {
		return "andrew";
	}

	function setUp(): void {
		$installer = new MysqlDBInstaller();
		$this->installer = $installer;
		$installer->databaseName = "rbook_test";
		$installer->adminUser = "root";
		$installer->password = "foobar";
		$installer->action = "fresh";
		$installer->databaseHost = "localhost";
		$installer->initialUser = $this->getInitialUser();
		$installer->initialEmail = "andrew@foo.com";
		$installer->install();
		error_reporting(E_ALL);
	}
}
