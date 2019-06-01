<?php

namespace Drupal\Test\loft_core;

use Drupal\loft_core\Loft;
use Drupal\Tests\UnitTestCase;

class LoftTest extends UnitTestCase {

  public function testOverrideValuesByKey() {
    $form = [
      '#id' => 123,
      'field_test' => [
        '#access' => TRUE,
        '#required' => TRUE,
        'widget' => [
          0 => [
            'value' => [
              '#required' => TRUE,
            ],
          ],
        ],
      ],
    ];

    Loft::overrideValuesByKey($form, '#required', FALSE);
    $this->assertFalse($form['field_test']['#required']);
    $this->assertFalse($form['field_test']['widget'][0]['value']['#required']);
    $this->assertSame('8291d20f2afa74919e6858c51dc713ce', $form['#loft_core']['#processed'][0]);
  }

}