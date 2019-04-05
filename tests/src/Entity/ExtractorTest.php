<?php

namespace Drupal\loft_core;

use AKlump\LoftLib\Testing\PhpUnitTestCase;
use Drupal\d8now\Drupal7Mock;
use Drupal\loft_core\Entity\HasEntityTrait;
use Drupal\d8now\Mock;
use Drupal\data_api\Data as Data;
use Drupal\data_api\DataMock;
use Drupal\data_api\DataTrait;
use Drupal\loft_core\Entity\Extractor;
use Drupal\loft_core\Entity\EntityDataAccessorTrait;

/**
 * @coversDefaultClass Drupal\loft_core\Entity\Extractor
 * @group              ${test_group}
 */
class ExtractorTest extends PhpUnitTestCase {

  public function testItemsReturnsUnlocalizedValueWhenLocalizedNotPresentEvenWithLanguage() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $entity->language = 'en';
    $this->assertSame([
      0 => ['value' => 'lorem'],
      1 => ['value' => 'ipsum'],
    ], $this->obj->items('field_summary'));
  }

  public function testItemsReturnsLocalizedValueWhenPresent() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $entity->language = 'en';
    $entity->field_summary['en'][0]['value'] = 'alpha';
    $entity->field_summary['en'][1]['value'] = 'bravo';
    $this->assertSame([
      0 => ['value' => 'alpha'],
      1 => ['value' => 'bravo'],
    ], $this->obj->items('field_summary'));
  }

  public function testFReturnsDefaultWhenNoDelta() {
    $this->prepareTestableEntity();
    $entity = (object) [
      'type' => 'page',
      'field_summary' => [],
    ];
    $this->obj->setEntity('node', $entity);
    $this->assertSame('eggnog_latte', $this->obj->f('eggnog_latte', 'field_summary'));
  }

  public function testFReturnsTypeWhichIsNotAField() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->assertSame('page', $this->obj->f('default', 'type'));
  }

  public function testSafeReturnsTheValueForAllSignatures() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->assertSame('lorem', $this->obj->safe('default', 'field_summary'));
    $this->assertSame('lorem', $this->obj->safe('default', 'field_summary', 'value'));
    $this->assertSame('lorem', $this->obj->safe('default', 'field_summary', 0, 'value'));
    $this->assertSame('lorem', $this->obj->safe('default', 'field_summary', 'value', 0));
    $this->assertSame('ipsum', $this->obj->safe('default', 'field_summary', 1, 'value'));
    $this->assertSame('ipsum', $this->obj->safe('default', 'field_summary', 'value', 1));
  }

  public function testFReturnsTheValueForAllSignatures() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->assertSame('lorem', $this->obj->f('default', 'field_summary'));
    $this->assertSame('lorem', $this->obj->f('default', 'field_summary', 'value'));
    $this->assertSame('lorem', $this->obj->f('default', 'field_summary', 0, 'value'));
    $this->assertSame('lorem', $this->obj->f('default', 'field_summary', 'value', 0));
    $this->assertSame('ipsum', $this->obj->f('default', 'field_summary', 1, 'value'));
    $this->assertSame('ipsum', $this->obj->f('default', 'field_summary', 'value', 1));
  }

  public function testSafeFiltersHtmlTags() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->assertSame('WHO I AM', $this->obj->safe('lorem', 'field_html', 1, 'value'));
    $this->assertSame('&lt;h1&gt;For I Am&lt;/h1&gt;', $this->obj->safe('lorem', 'field_html'));
  }

  public function testSafeOnBogusFieldReturnsDefault() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->assertSame('lorem', $this->obj->safe('lorem', 'field_bogus'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testFThrowsWhenTypeWhichIsNotAFieldAndDeltaIsProvided() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->obj->f('default', 'type', 0);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testFThrowsWhenTypeWhichIsNotAFieldAndColumnIsProvided() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->obj->f('default', 'type', 'value');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testMoreThanFourArgsToFThrows() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->obj->f(NULL, 'field_summary', 1, 2, 3);
  }

  public function testItemsReturnsDefaultForBogusField() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->assertSame(['do', 're'], $this->obj->items('field_bogus', [
      'do',
      're',
    ]));
  }

  public function testItemsReturnsArrayForBogusField() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->assertSame([], $this->obj->items('field_bogus'));
  }

  public function testItemsReturnsArrayOfItems() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->assertSame([
      0 => ['value' => 'lorem'],
      1 => ['value' => 'ipsum'],
    ], $this->obj->items('field_summary'));
  }

  public function testFReturnsTheItemArrayAtZeroForAllSignatures() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->assertSame(['value' => 'lorem'], $this->obj->f([], 'field_summary', 0));
  }

  public function testFReturnsDefaultWhenBogusField() {
    global $entity;
    $entity = $this->prepareTestableEntity();
    $this->assertSame('default', $this->obj->f('default', 'field_bogus'));
  }

  public function setUp() {
    Mock::unmockAll();
    $this->objArgs = [new Drupal7(), new DataMock()];
    $this->createObj();
  }

  protected function prepareTestableEntity() {
    $entity = (object) [
      'type' => 'page',
      'field_summary' => [
        'und' => [
          0 => ['value' => 'lorem'],
          1 => ['value' => 'ipsum'],
        ],
      ],
      'field_html' => [
        'und' => [
          0 => ['value' => '<h1>For I Am</h1>', 'format' => 'plain_text'],
          1 => ['value' => 'who I am', 'format' => 'upper'],
        ],
      ],
    ];
    $this->obj->setEntity('node', $entity);

    Mock::field_info_field(function ($field_name) {
      if (in_array($field_name, ['field_html', 'field_summary'])) {
        return ['columns' => ['value']];
      }

      return NULL;
    });

    return $entity;
  }

  protected function createObj() {
    list($d7, $data) = $this->objArgs;
    $this->obj = new TestableExtractor($d7, $data);
  }
}

class TestableExtractor {

  use EntityDataAccessorTrait;
  use HasEntityTrait;
  use DataTrait;

  public function __construct(Drupal7 $d7, Data $data) {
    $this->d7 = $d7;
    $this->core = new TestableCore($data);
    $this->setDataApiData($data);
    $this->e = $this->getDataApiData('node');
  }
}

class TestableCore extends CoreBase {

  function getSafeMarkupHandler() {
    return 'plain_text';
  }
}

class Drupal7 extends Drupal7Mock {

  public function check_markup($text, $format_id = NULL, $langcode = '', $cache = FALSE) {
    switch ($format_id) {
      case 'plain_text':
        return $this->check_plain($text);
    }

    return strtoupper($text);
  }
}
