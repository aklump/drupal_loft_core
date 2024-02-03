<?php

namespace Drupal\Tests\loft_core\Unit;

use Drupal\Tests\loft_core\TestWithFilesTrait;
use PHPUnit\Framework\TestCase;
use function loft_core_cl;

require_once __DIR__ . '/../../includes/loft_core.utilities.inc';

/**
 * @covers loft_core_bem
 */
class LoftCoreBemTest extends TestCase {

  use TestWithFilesTrait;

  public function dataFortestLoftCoreBemReturnsExpectedValuesProvider() {
    $tests = [];
    $tests[] = ['apple'];
    $tests[] = ['banana-split'];

    return $tests;
  }

  /**
   * @dataProvider dataFortestLoftCoreBemReturnsExpectedValuesProvider
   */
  public function testLoftCoreBemReturnsExpectedValues(string $subject) {
    /** @phpstan-ignore-next-line */
    list($bem, $bem_modifier) = loft_core_bem('foo');
    $this->assertSame('foo', $bem());
    $this->assertSame('foo__' . $subject, $bem($subject));
    $this->assertSame('foo', $bem_modifier());
    $this->assertSame('foo--' . $subject, $bem_modifier($subject));
  }

  public function dataForBemModifierThrowsProvider() {
    $tests = [];
    $tests[] = ['lorem--ipsum'];
    $tests[] = ['lorem__ipsum'];
    $tests[] = ['lorem_ipsum'];

    return $tests;
  }

  /**
   * @dataProvider dataForBemModifierThrowsProvider
   */
  public function testLoftCoreBemThrowsWithDoubleHyphenArgumentToModifier(string $subject) {
    /** @phpstan-ignore-next-line */
    list(, $bem_modifier) = loft_core_bem('foo');
    $this->expectException(\InvalidArgumentException::class);
    $bem_modifier($subject);
  }

  public function dataForBemThrowsProvider() {
    $tests = [];
    $tests[] = ['lorem--ipsum'];
    $tests[] = ['lorem__ipsum'];

    return $tests;
  }

  /**
   * @dataProvider dataForBemThrowsProvider
   */
  public function testLoftCoreBemThrowsWithDoubleHyphenArgumentToElement(string $subject) {
    /** @phpstan-ignore-next-line */
    list($bem) = loft_core_bem('foo');
    $this->expectException(\InvalidArgumentException::class);
    $bem($subject);
  }

  /**
   * Provides data for testLoftCoreCl.
   */
  function DataForTestLoftCoreClProvider() {
    $tests = array();
    $tests[] = array(
      'do',
      're',
      'do__re',
      'do--re',
    );
    $tests[] = array(
      'a_list_apart',
      'chapter-one-thing',
      'a-list-apart__chapter-one-thing',
      'a-list-apart--chapter-one-thing',
    );

    return $tests;
  }

  /**
   * @dataProvider DataForTestLoftCoreClProvider
   */
  public function testLoftCoreCl($base, $subject, $control_component, $control_version) {
    /** @phpstan-ignore-next-line */
    $cl = loft_core_cl($base);
    $this->assertSame($control_component, $cl($subject));
    $this->assertSame($control_component, $cl($subject, TRUE));
    $this->assertSame($control_version, $cl($subject, FALSE));
  }

}
