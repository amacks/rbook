<?php
require_once(dirname(__FILE__) . '/BaseDBTest.php');
require_once(dirname(__FILE__) . '/../install/db_installer.php');
require_once(dirname(__FILE__) . '/../install/mysql_db_installer.php');
require_once(dirname(__FILE__) . '/../classes/base_record.php');
require_once(dirname(__FILE__) . '/../classes/category.php');

class CategoryTest extends BaseDBTest {

	function testCreate() {
		$cat = new Category();
		$cat->name = 'Foo';
		$this->assertEquals(-1, $cat->id);
		$cat->save();
		$this->assertTrue($cat->id > 0);

		$cat2 = new Category();
		$cat2->name = 'bar';
		$cat2->save();
		$this->assertTrue($cat2->id > $cat->id);
	}

	function testLoadMultiple() {
		$categories = Category::loadMultiple();
		$this->assertEquals(10, count($categories));

		$this->assertEquals('Barbecue', $categories[0]->name);
		$this->assertEquals(1, $categories[0]->id);

		$this->assertEquals('Desserts', $categories[1]->name);
		$this->assertEquals(2, $categories[1]->id);

		$categories = Category::loadMultiple(array('id' => 1));
		$this->assertEquals(1, count($categories));

		$this->assertEquals('Barbecue', $categories[0]->name);
		$this->assertEquals(1, $categories[0]->id);
	}
}

