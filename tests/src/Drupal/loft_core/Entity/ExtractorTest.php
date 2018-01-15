<?php

use AKlump\LoftLib\Testing\PhpUnitTestCase;
use Drupal\loft_core\Entity\Extractor;

/**
 * @coversDefaultClass Drupal\loft_core\Entity\Extractor
 * @group              ${test_group}
 */
//class ExtractorTest extends PHPUnit_Framework_TestCase {
class ExtractorTest extends PhpUnitTestCase {

  public function testExample() {
    $this->assertTrue(FALSE);
  }

  public function setUp() {
    $this->objArgs = [];
    $this->createObj();
  }

  protected function createObj() {
    $this->obj = new Dates();
  }
}
