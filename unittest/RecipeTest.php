<?php
require_once(dirname(__FILE__) . '/BaseDBTest.php');
require_once(dirname(__FILE__) . '/../install/db_installer.php');
require_once(dirname(__FILE__) . '/../install/mysql_db_installer.php');
require_once(dirname(__FILE__) . '/../classes/base_record.php');
require_once(dirname(__FILE__) . '/../classes/recipe.php');

class RecipeTest extends BaseDBTest {

	function testLoad() {
		$r = $this->createFooRecipe();
		$r->save();
		$this->assertTrue($r->id > 0);
		$r = Recipe::load($r->id);
		$this->assertNotNull($r);
		$this->assertEquals("foo", $r->title);
		$this->assertEquals($this->getInitialUser(), $r->submittedByName);
	}

	function createFooRecipe() {
		$r = new Recipe();
		$r->title = "foo";
		$r->categoryId = 1;
		$r->submittedById = 1;
		$r->note = "foo";
		$this->assertEquals(-1, $r->id);
		return $r;
	}

	function testSave() {
		$r = $this->createFooRecipe();
		$r->note = "foo note";
		$this->assertEquals(-1, $r->id);
		$r->save();
		$firstId = $r->id;
		$r = Recipe::load($firstId);
		$this->assertNotNull($r);
		$this->assertEquals("foo", $r->title);
		$r->save();
		$this->assertEquals($firstId, $r->id);
		$this->assertNotNull($r);
		$this->assertEquals("foo", $r->title);
		$this->assertEquals("foo note", $r->note);
		$this->assertEquals($this->getInitialUser(), $r->submittedByName);
		$r->note = "foo note 2";
		$r->save();
		$r = Recipe::load($r->id);
		$this->assertEquals("foo note 2", $r->note);

		$r2 = $this->createFooRecipe();
		$r2->title = "bar";
		$r2->note = "bar note";
		$r2->save();
		$this->assertTrue($r2->id > $firstId);
		$r2 = Recipe::load($r2->id);
		$this->assertEquals("bar note", $r2->note);
	}
}
