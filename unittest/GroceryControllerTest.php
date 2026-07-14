<?php
require_once(dirname(__FILE__) . '/BaseDBTest.php');
require_once(dirname(__FILE__) . '/../install/db_installer.php');
require_once(dirname(__FILE__) . '/../install/mysql_db_installer.php');
require_once(dirname(__FILE__) . '/../classes/base_record.php');
require_once(dirname(__FILE__) . '/../classes/user.php');
require_once(dirname(__FILE__) . '/../classes/grocery_list.php');
require_once(dirname(__FILE__) . '/../controllers/grocery_controller.php');

/**
 * Regression coverage for GroceryController::saveCommon(), which reads
 * posted "gi0", "gi1", ... fields until it hits the first missing/empty
 * one. That termination check must not trigger a PHP8 "Undefined array
 * key" warning for the (expected) first missing index.
 */
class GroceryControllerTest extends BaseDBTest {

	function tearDown(): void {
		foreach (array_keys($_REQUEST) as $key) {
			if (str_starts_with($key, 'gi')) {
				unset($_REQUEST[$key]);
			}
		}
		parent::tearDown();
	}

	function testSaveCommonPersistsItemsWithoutWarning() {
		$user = User::loadOne(array('username' => 'root'));
		$this->assertNotNull($user);

		$_REQUEST['gi0'] = 'eggs';
		$_REQUEST['gi1'] = 'milk';
		$_REQUEST['gi2'] = 'bread';
		// Deliberately no 'gi3' — this is the expected termination signal
		// saveCommon() relies on; accessing it must not warn.

		$controller = GroceryController::newInstance();
		$controller->saveCommon($user);

		$items = GroceryList::findByUser($user->id);
		$this->assertCount(3, $items);

		$byOrder = array();
		foreach ($items as $item) {
			$byOrder[$item->orderid] = $item->description;
		}
		ksort($byOrder);
		$this->assertEquals(array('eggs', 'milk', 'bread'), array_values($byOrder));
	}

	function testSaveCommonReplacesExistingItems() {
		$user = User::loadOne(array('username' => 'root'));

		$_REQUEST['gi0'] = 'first list item';
		$controller = GroceryController::newInstance();
		$controller->saveCommon($user);
		$this->assertCount(1, GroceryList::findByUser($user->id));

		unset($_REQUEST['gi0']);
		$_REQUEST['gi0'] = 'second list item a';
		$_REQUEST['gi1'] = 'second list item b';
		$controller->saveCommon($user);

		$items = GroceryList::findByUser($user->id);
		$this->assertCount(2, $items);
	}
}
