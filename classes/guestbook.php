<?php

/*
 * rbook Recipe Management System
 * Copyright (C) 2006 Andrew Violette andrew@andrewviolette.net
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Represents the guestbook.
 *
 * @author Maschine
 * @package rbook
 * @since 2.1.2
 * @version $Id: comment.php,v 1.4 2007/03/16 01:24:26 aviolette Exp $
 */


require_once(dirname(__FILE__) . '/base_record.php');

class GuestbookFactory extends BaseRecordFactory {
  function createInstance(): mixed {
    return new Guestbook();
  }

  function getTable(): string {
    return "guestbook";
  }
}

class Guestbook extends BaseRecord {
  public $id;
  public $name;
  public $comment;
  public $postdate;

  function __construct() {
    parent::__construct("guestbook");
  }
        
  function init(&$row) {
    $this->id = $row['id'];
    $this->name = $row['name'];
    $this->comment = $row['comment'];
    $this->postdate = $row['postdate'];
  }

  function dbCreateNew() {
  	if(isset($this->postdate) && $this->postdate != null)
		$pdate = $this->postdate;
	else
		$pdate = date("YmdHis", time());
		
    $db = $this->getDb();
    $this->runQuery($db, "insert into guestbook (name, comment, postdate) values (?, ?, ?)",
	  array($this->name, $this->comment, $pdate));
    $this->id = $db->lastInsertId();
    $db->commit();
    $db->disconnect();
  }
  function remove($db = null) {
    $cascade = isset($db);
    if(!isset($db)) {
      $db = BaseRecord::getDb();
    }
    $this->runQuery($db, "delete from guestbook where id = ?", array($this->id));
    if(!$cascade) {
      $db->commit();
      $db->disconnect();
    }
  }

  function loadOne($qualifiers) {
    $guestbook = Guestbook::loadMultiple($qualifiers, 1);
    if(count($guestbook)) {
      return $guestbook[0];
    }
    return null;
  }

  function deleteMultiple($qualifiers = null) {
    return BaseRecord::deleteMultipleOfClass($qualifiers, "guestbook");
  }

  function loadMultiple($qualifiers = null, $limit = null, $db = null) {
    return BaseRecord::loadMultipleBasic(new GuestbookFactory(), $qualifiers, $limit, $db, "postdate DESC");
  }


}
?>