<?php

namespace Drupal\Tests\loft_core\Integration;

use Drupal\Core\Template\Attribute;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/loft_core.forms.inc';

/**
 * @covers loft_core_min_weight
 * @covers loft_core_max_weight
 */
class LoftCoreFormTabindexTest extends TestCase {

  public function testLoftCoreTabIndexWithObject() {
    $tabindex = $ti_control = 100;
    $el = array('#attributes' => new Attribute());
    $control = array('tabindex' => 100);
    \loft_core_form_tabindex($el, $tabindex);
    $this->assertSame($control, $el['#attributes']->toArray());
    $this->assertSame($ti_control + 1, $tabindex);
  }

  public function testLoftCoreTabIndexWithArray() {
    $tabindex = $ti_control = 100;
    $el = array();
    $control = array(
      '#attributes' => array('tabindex' => 100),
    );
    \loft_core_form_tabindex($el, $tabindex);
    $this->assertSame($control, $el);
    $this->assertSame($ti_control + 1, $tabindex);
  }


}
