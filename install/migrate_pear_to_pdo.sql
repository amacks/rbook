-- ============================================================
-- Migration: PEAR DB sequences → MySQL AUTO_INCREMENT
-- Run this script ONCE against an existing rbook database
-- before upgrading the PHP code to the PDO-based version.
--
-- What this does:
--   1. Adds AUTO_INCREMENT to the id columns of all tables that
--      previously relied on PEAR DB *_seq sequence tables.
--   2. Sets each table's AUTO_INCREMENT counter to the value
--      stored in the corresponding _seq table (so IDs continue
--      from where they left off).
--   3. Drops the now-unused *_seq tables.
--
-- Safe to run on a fresh install too — AUTO_INCREMENT simply
-- starts at 1 when the tables are empty.
-- ============================================================

-- Preserve sequence counters before altering tables.
-- We read the current auto_increment value from each _seq table
-- and apply it after enabling AUTO_INCREMENT on the main table.

-- categories
ALTER TABLE `categories`
  MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
SET @cat_ai = (SELECT COALESCE(MAX(id), 1) FROM `categories_seq`);
SET @sql = CONCAT('ALTER TABLE `categories` AUTO_INCREMENT = ', @cat_ai);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- comments
ALTER TABLE `comments`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
SET @comments_ai = (SELECT COALESCE(MAX(id), 1) FROM `comments_seq`);
SET @sql = CONCAT('ALTER TABLE `comments` AUTO_INCREMENT = ', @comments_ai);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- groceryitems
ALTER TABLE `groceryitems`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
SET @gi_ai = (SELECT COALESCE(MAX(id), 1) FROM `groceryitems_seq`);
SET @sql = CONCAT('ALTER TABLE `groceryitems` AUTO_INCREMENT = ', @gi_ai);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- guestbook (added in schema v2.1.2)
ALTER TABLE `guestbook`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
-- guestbook_seq may not exist on all installations; ignore errors.
SET @gb_ai = (SELECT COALESCE(MAX(id), 1) FROM `guestbook`);
SET @sql = CONCAT('ALTER TABLE `guestbook` AUTO_INCREMENT = ', @gb_ai + 1);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- images
ALTER TABLE `images`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
SET @img_ai = (SELECT COALESCE(MAX(id), 1) FROM `images_seq`);
SET @sql = CONCAT('ALTER TABLE `images` AUTO_INCREMENT = ', @img_ai);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ingredients
ALTER TABLE `ingredients`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
SET @ing_ai = (SELECT COALESCE(MAX(id), 1) FROM `ingredients_seq`);
SET @sql = CONCAT('ALTER TABLE `ingredients` AUTO_INCREMENT = ', @ing_ai);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ingredientsets
ALTER TABLE `ingredientsets`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
SET @is_ai = (SELECT COALESCE(MAX(id), 1) FROM `ingredientsets_seq`);
SET @sql = CONCAT('ALTER TABLE `ingredientsets` AUTO_INCREMENT = ', @is_ai);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- recipes
ALTER TABLE `recipes`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
SET @rec_ai = (SELECT COALESCE(MAX(id), 1) FROM `recipes_seq`);
SET @sql = CONCAT('ALTER TABLE `recipes` AUTO_INCREMENT = ', @rec_ai);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- steps
ALTER TABLE `steps`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
SET @steps_ai = (SELECT COALESCE(MAX(id), 1) FROM `steps_seq`);
SET @sql = CONCAT('ALTER TABLE `steps` AUTO_INCREMENT = ', @steps_ai);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- users
ALTER TABLE `users`
  MODIFY `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
SET @usr_ai = (SELECT COALESCE(MAX(id), 1) FROM `users_seq`);
SET @sql = CONCAT('ALTER TABLE `users` AUTO_INCREMENT = ', @usr_ai);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- Drop the PEAR sequence tables — no longer needed.
-- ============================================================
DROP TABLE IF EXISTS `categories_seq`;
DROP TABLE IF EXISTS `comments_seq`;
DROP TABLE IF EXISTS `groceryitems_seq`;
DROP TABLE IF EXISTS `guestbook_seq`;
DROP TABLE IF EXISTS `images_seq`;
DROP TABLE IF EXISTS `ingredients_seq`;
DROP TABLE IF EXISTS `ingredientsets_seq`;
DROP TABLE IF EXISTS `recipes_seq`;
DROP TABLE IF EXISTS `steps_seq`;
DROP TABLE IF EXISTS `users_seq`;
