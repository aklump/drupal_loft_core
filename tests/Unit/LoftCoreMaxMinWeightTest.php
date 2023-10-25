<?php

namespace Drupal\Tests\loft_core\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/loft_core.utilities.inc';

/**
 * @covers loft_core_min_weight
 * @covers loft_core_max_weight
 */
class LoftCoreMaxMinWeightTest extends TestCase {

  /**
   * Provides data for testMinWeight.
   */
  function DataForTestWeightProvider() {
    $tests = array();
    $tests[] = array(
      -10,
      9,
      array(
        array('#weight' => 0),
        array('#weight' => 9),
        array('#weight' => -5),
        array('#weight' => -10),
      ),
    );

    return $tests;
  }

  /**
   * @dataProvider DataForTestWeightProvider
   */
  public function testMaxWeight($min, $max, $subject) {
    $this->assertSame($max, \loft_core_max_weight($subject));
  }

  /**
   * @dataProvider DataForTestWeightProvider
   */
  public function testMinWeight($min, $max, $subject) {
    $this->assertSame($min, \loft_core_min_weight($subject));
  }

}
