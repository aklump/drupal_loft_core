<?php

/**
 * @file
 * PHPUnit tests for the BlockVisibilityTest class
 */

class BlockVisibilityTestTest extends \PHPUnit_Framework_TestCase {

  public function testMultiple() {
    $obj = new BlockVisibility(NULL, 'library');
    $obj->hide_if_alias('library', 'library/browse');
    $obj->show_if_alias_regex('/^library\/.+/');
    $this->assertFalse($obj->isVisible());

    $obj = new BlockVisibility(NULL, 'library/browse');
    $obj->hide_if_alias('library', 'library/browse');
    $obj->show_if_alias_regex('/^library\/.+/');
    $this->assertFalse($obj->isVisible());

    $obj = new BlockVisibility(NULL, 'library/people');
    $obj->hide_if_alias('library', 'library/browse');
    $obj->show_if_alias_regex('/^library\/.+/');
    $this->assertTrue($obj->isVisible());
  }

  public function testHideIfAliasRegex() {
    $obj = new BlockVisibility(NULL, 'library');
    $obj->hide_if_alias_regex('/library\/.+/');
    $this->assertTrue($obj->isVisible());

    $obj = new BlockVisibility(NULL, 'library/people');
    $obj->hide_if_alias_regex('/library\/.+/');
    $this->assertFalse($obj->isVisible());
  }

  public function testShowIfAliasRegex() {
    $obj = new BlockVisibility(NULL, 'library');
    $obj->show_if_alias_regex('/library\/.+/');
    $this->assertFalse($obj->isVisible());

    $obj = new BlockVisibility(NULL, 'library/people');
    $obj->show_if_alias_regex('/library\/.+/');
    $this->assertTrue($obj->isVisible());
  }

  public function testHideIfNid() {
    $obj = new BlockVisibility('node/234');
    $obj->hide_if_nid(123, 234);
    $this->assertFalse($obj->isVisible());

    $obj = new BlockVisibility('node/345');
    $obj->hide_if_nid(123, 234);
    $this->assertTrue($obj->isVisible());
  }

  public function testShowIfNid() {
    $obj = new BlockVisibility('node/234');
    $obj->show_if_nid(123, 234);
    $this->assertTrue($obj->isVisible());

    $obj = new BlockVisibility('node/345');
    $obj->show_if_nid(123, 234);
    $this->assertFalse($obj->isVisible());
  }

  public function testHideIfAlias() {
    $obj = new BlockVisibility(NULL, 'home');
    $obj->hide_if_alias('home');
    $this->assertFalse($obj->isVisible());

    $obj = new BlockVisibility(NULL, 'home');
    $obj->hide_if_alias('away');
    $this->assertTrue($obj->isVisible());
  }

  public function testShowIfAlias() {
    $obj = new BlockVisibility(NULL, 'home');
    $obj->show_if_alias('home');
    $this->assertTrue($obj->isVisible());

    $obj = new BlockVisibility(NULL, 'home');
    $obj->show_if_alias('away');
    $this->assertFalse($obj->isVisible());
  }

  public function testNoMatchDefaults() {
    $obj = new BlockVisibility();
    $this->assertFalse($obj->isVisible());

    $obj = new BlockVisibility();
    $obj->default = TRUE;
    $this->assertTrue($obj->isVisible());
  }

}

if (!function_exists('current_path')) {
  function current_path() {
    return '';
  }
}

if (!function_exists('drupal_get_path_alias')) {
  function drupal_get_path_alias() {
    return '';
  }
}
