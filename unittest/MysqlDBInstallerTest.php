<?php
require_once(dirname(__FILE__) . '/../install/db_installer.php');
require_once(dirname(__FILE__) . '/../install/mysql_db_installer.php');

class MysqlDBInstallerTest extends PHPUnit\Framework\TestCase {

	function testConstructor() {
		$installer = new MysqlDBInstaller();
		$this->assertNotNull($installer->databaseName, "Database name is null");
	}

	function testInstall() {
	}
}
