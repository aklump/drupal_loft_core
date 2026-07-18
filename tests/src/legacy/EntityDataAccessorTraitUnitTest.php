<?php

namespace Drupal\Tests\loft_core\Unit;

use AKlump\DrupalTest\Drupal8\EntityMockingTrait;
use AKlump\DrupalTest\Drupal8\UnitTestCase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinitionInterface;
use Drupal\Core\Url;
use Drupal\field\FieldConfigInterface;
use Drupal\loft_core\Entity\EntityDataAccessorTrait;
use Drupal\loft_core\Entity\HasEntityInterface;
use Drupal\loft_core\Entity\HasEntityTrait;
use Drupal\node\NodeInterface;

/**
 * @group loft_core
 */
class EntityDataAccessorTraitUnitTest extends UnitTestCase {

  use EntityMockingTrait;

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return [
      'classToBeTested' => Data::class,
      'classArgumentsMap' => [
        'entityFieldManager' => EntityFieldManagerInterface::class,
        'entityTypeManager' => EntityTypeManagerInterface::class,
      ],
      'mockObjectsMap' => [
        'entity' => EntityInterface::class,
        'url' => Url::class,
        'fieldConfig' => FieldConfigInterface::class,
        'fieldDefinition' => FieldItemDataDefinitionInterface::class,
        'entityStorage' => EntityStorageInterface::class,
        'node' => NodeInterface::class,
      ],
    ];
  }

  /**
   * @dataProvider dataForTestDateReturnsDrupalDateTimeFromVariousValuesProvider
   */
  public function testDateReturnsDrupalDateTimeFromVariousValuesWhenUsingEndValueArgument($control_utc, $control_local, $field_value) {
    $this->populateEntity('node', 'story');
    $this->populateEntityFieldValues('field_date', [$field_value], 'end_value');

    $date = $this->obj->setEntity($this->entity)->date('', 'field_date', 'end_value');
    $this->assertInstanceOf(DrupalDateTime::class, $date);
    $this->assertSame($control_utc, $date->format('r'));

    $date = $this->obj->setEntity($this->entity)
      ->date('', 'field_date', 'end_value', 'America/Los_Angeles');
    $this->assertInstanceOf(DrupalDateTime::class, $date);
    $this->assertSame($control_local, $date->format('r'));
  }

  /**
   * @dataProvider dataForTestDateReturnsDrupalDateTimeFromVariousValuesProvider
   */
  public function testDateReturnsDrupalDateTimeFromVariousValuesWhenUsingItemIndexArgument($control_utc, $control_local, $field_value) {
    $this->populateEntity('node', 'story');
    $this->populateEntityFieldValues('field_date', [NULL, $field_value]);

    $date = $this->obj->setEntity($this->entity)->date('', 'field_date', 1);
    $this->assertInstanceOf(DrupalDateTime::class, $date);
    $this->assertSame($control_utc, $date->format('r'));

    $date = $this->obj->setEntity($this->entity)
      ->date('', 'field_date', 1, 'America/Los_Angeles');
    $this->assertInstanceOf(DrupalDateTime::class, $date);
    $this->assertSame($control_local, $date->format('r'));
  }

  /**
   * @dataProvider dataForTestDateReturnsDrupalDateTimeFromVariousValuesProvider
   */
  public function testDateReturnsDrupalDateTimeFromVariousValues($control_utc, $control_local, $field_value) {
    $this->populateEntity('node', 'story');
    $this->populateEntityFieldValues('field_date', [$field_value]);

    $date = $this->obj->setEntity($this->entity)->date('', 'field_date');
    $this->assertInstanceOf(DrupalDateTime::class, $date);
    $this->assertSame($control_utc, $date->format('r'));

    $date = $this->obj->setEntity($this->entity)
      ->date('', 'field_date', 'America/Los_Angeles');
    $this->assertInstanceOf(DrupalDateTime::class, $date);
    $this->assertSame($control_local, $date->format('r'));
  }

  public function testAnEmptyArrayIsReturnedWhenFieldDoesNotExist() {
    $this->populateEntity('node', 'person');
    $this->obj->setEntity($this->entity);
    $this->entity->allows('get')
      ->with('field_bad_field')
      ->andThrow(\InvalidArgumentException::class, 'Field is unknown.');
    $this->assertSame([], $this->obj->entities('field_bad_field'));
  }

  public function testEntitiesWorkAsExpected() {
    $this->populateEntity('node', 'person');
    $this->populateEntityFieldValues('field_related', [4628], 'target_id');
    $this->obj->setEntity($this->entity);

    $node = \Mockery::Mock(NodeInterface::class);
    $node->shouldReceive('id')->andReturn(4628);
    $this->entityStorage->shouldReceive('loadMultiple')
      ->with([4628])
      ->andReturn([
        4628 => $node,
      ]);
    $this->args->entityTypeManager->shouldReceive('getStorage')
      ->with('paragraphs_item')
      ->andReturn($this->entityStorage);
    $this->fieldDefinition->shouldReceive('getSetting')
      ->with('target_type')
      ->andReturn('paragraphs_item');
    $this->fieldConfig->shouldReceive('getItemDefinition')
      ->andReturn($this->fieldDefinition);
    $this->args->entityFieldManager->shouldReceive('getFieldDefinitions')
      ->with('node', 'person')
      ->andReturn([
        'field_related' => $this->fieldConfig,
      ]);

    $entities = $this->obj->entities('field_related');
    $this->assertSame(4628, key($entities));
    $first = reset($entities);
    $this->assertSame(4628, $first->id());

    $this->assertSame(4628, $this->obj->entity('field_related')->id());
  }

  public function testDateDefaultNowReturnsDateObjectForNonField() {
    $this->populateEntity('node', 'story');
    $this->assertInstanceOf(DrupalDateTime::class, $this->obj->setEntity($this->entity)
      ->date('now', 'field_date'));
  }

  public function testDateDefaultNullReturnsNullForNonField() {
    $this->populateEntity('node', 'story');
    $this->assertNull($this->obj->setEntity($this->entity)
      ->date(NULL, 'field_date'));
  }

  public function testDateDefaultFalseReturnsFalseForNonField() {
    $this->populateEntity('node', 'story');
    $this->assertSame(FALSE, $this->obj->setEntity($this->entity)
      ->date(FALSE, 'field_date'));
  }

  public function testDateDefaultEmptyStringReturnsEmptyStringForNonField() {
    $this->populateEntity('node', 'story');
    $this->assertSame('', $this->obj->setEntity($this->entity)
      ->date('', 'field_date'));
  }

  /**
   * Provides data for testDateReturnsDrupalDateTimeFromVariousValues.
   */
  public function dataForTestDateReturnsDrupalDateTimeFromVariousValuesProvider() {
    $tests = array();

    // Drupal\Core\Field\Plugin\Field\FieldType\TimestampItem.
    $tests[] = array(
      'Tue, 23 Apr 2019 00:23:00 +0000',
      'Mon, 22 Apr 2019 17:23:00 -0700',
      1555978980,
    );

    // Drupal\Core\Field\Plugin\Field\FieldType\TimestampItem.
    $tests[] = array(
      'Tue, 23 Apr 2019 00:23:00 +0000',
      'Mon, 22 Apr 2019 17:23:00 -0700',
      '1555978980',
    );

    // Drupal\datetime\Plugin\Field\FieldType\DateTimeItem.
    $tests[] = array(
      'Mon, 22 Apr 2019 00:00:00 +0000',
      'Sun, 21 Apr 2019 17:00:00 -0700',
      '2019-04-22',
    );

    // Drupal\datetime\Plugin\Field\FieldType\DateTimeItem.
    $tests[] = array(
      'Mon, 22 Apr 2019 22:32:50 +0000',
      'Mon, 22 Apr 2019 15:32:50 -0700',
      '2019-04-22T22:32:50',
    );

    return $tests;
  }

  public function testDefaultReturnsWhenFieldIsEmpty() {
    $this->populateEntity('node', 'page');
    $this->populateEntityFieldValues('field_list', []);

    $this->assertSame('milk', $this->obj->setEntity($this->entity)
      ->f('milk', 'field_list'));
    $this->assertSame('milk', $this->obj->setEntity($this->entity)
      ->f('milk', 'field_list', 'value'));
    $this->assertSame('milk', $this->obj->setEntity($this->entity)
      ->f('milk', 'field_list', 'value', 0));
    $this->assertSame('milk', $this->obj->setEntity($this->entity)
      ->f('milk', 'field_list', 0, 'value'));
    $this->assertSame('milk', $this->obj->setEntity($this->entity)
      ->f('milk', 'field_list', 'value', 1));
    $this->assertSame('milk', $this->obj->setEntity($this->entity)
      ->f('milk', 'field_list', 1, 'value'));
  }

  /**
   * @expectedException \OutOfBoundsException
   */
  public function testFieldWithoutValueColumnThrowsWhenOmittingValue() {
    $this->populateEntity('node', 'page');
    $this->populateEntityFieldValues('field_related', [22], 'target_id');
    $this->obj->setEntity($this->entity);
    $this->assertSame('def', $this->obj->f('def', 'field_related'));
  }

  public function testPathReturnsLocalUnaliasedPath() {
    $this->populateEntity('node', 'page');
    $this->obj->setEntity($this->entity);
    $path = '/node/' . $this->entity->id();

    $this->url
      ->shouldReceive('toString')
      ->once()
      ->andReturn($path);

    $this->entity->shouldReceive('toUrl')
      ->with('canonical', ['path_processing' => FALSE])
      ->andReturn($this->url);

    $this->assertSame($path, $this->obj->path());
  }

  public function testItemsReturnDefaultWhenNoField() {
    $this->populateEntity('node', 'page');
    $this->obj->setEntity($this->entity);

    $items = $this->obj->items('field_body', ['a', 'b']);
    $this->assertSame(['a', 'b'], $items);
  }

  public function testItemsReturnsTheCorrectArrayByField() {

    // Prepare the entity.
    $this->populateEntity('node', 'page');
    $this->populateEntityFieldValues('field_body', ['lorem ipsum']);
    $this->populateEntityFieldValues('field_teaser', ['dolar sit', 'amet']);
    $this->obj->setEntity($this->entity);

    // Test 'items'.
    $items = $this->obj->items('field_body');
    $this->assertSame('lorem ipsum', $items[0]['value']);

    $items = $this->obj->items('field_teaser');
    $this->assertSame('dolar sit', $items[0]['value']);
    $this->assertSame('amet', $items[1]['value']);
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   */
  public function testItemsThrowsIfEntityNotSet() {
    $this->obj->items('field_bogus');
  }

  public function testFReturnsDefaultWhenFieldNotExists() {
    $this->populateEntity('node', 'page');
    $result = $this->obj->setEntity($this->entity)
      ->f('fallback_value', 'field_data');
    $this->assertSame('fallback_value', $result);
  }

  public function testFReturnsDefaultWhenFieldIsEmpty() {
    $this->populateEntity('node', 'page');
    $this->populateEntityFieldValues('field_data', []);
    $result = $this->obj->setEntity($this->entity)
      ->f('fallback_value', 'field_data');
    $this->assertSame('fallback_value', $result);
  }

  /**
   * @expectedException \BadMethodCallException
   */
  public function testFThrowsIfNonFieldColumnIsPassed() {
    $this->populateEntity('node', 'page');
    $this->entity->nonFieldData = 'bravo';
    $this->obj->setEntity($this->entity);

    $this->assertSame('bravo', $this->obj->f('alpha', 'nonFieldData', 'value'));
  }

  /**
   * @expectedException \BadMethodCallException
   */
  public function testFThrowsIfNonFieldDeltaIsPassed() {
    $this->populateEntity('node', 'page');
    $this->entity->nonFieldData = 'bravo';
    $this->obj->setEntity($this->entity);

    $this->assertSame('bravo', $this->obj->f('alpha', 'nonFieldData', 0));
  }

  /**
   * @expectedException \BadMethodCallException
   */
  public function testFThrowsIfNonFieldDeltaAndColumnArePassed() {
    $this->populateEntity('node', 'page');
    $this->entity->nonFieldData = 'bravo';
    $this->obj->setEntity($this->entity);

    $this->assertSame('bravo', $this->obj->f('alpha', 'nonFieldData', 'target_id', 1));
  }

  /**
   * @expectedException \BadMethodCallException
   */
  public function testFThrowsIfTooManyArgumentsOnAField() {
    $this->populateEntity('node', 'page');
    $this->populateEntityFieldValues('field_data', [100]);
    $this->obj->setEntity($this->entity);
    $this->obj->f('alpha', 'field_data', 'value', 0, 1);
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   */
  public function testFThrowsIfEntityNotSet() {
    $this->obj->f('alpha', 'field_bogus');
  }

  public function testFVariantsWithTargetId() {
    $this->populateEntity('node', 'page');
    $this->populateEntityFieldValues('field_related', [
      22,
      33,
    ], 'target_id');
    $this->obj->setEntity($this->entity);

    $this->assertSame(22, $this->obj->f('', 'field_related', 'target_id'));
    $this->assertSame(22, $this->obj->f('', 'field_related', 'target_id', 0));
    $this->assertSame(22, $this->obj->f('', 'field_related', 0, 'target_id'));

    $this->assertSame(33, $this->obj->f('', 'field_related', 'target_id', 1));
    $this->assertSame(33, $this->obj->f('', 'field_related', 1, 'target_id'));

    $this->assertSame(['target_id' => 22], $this->obj->f('', 'field_related', 0));
    $this->assertSame(['target_id' => 33], $this->obj->f('', 'field_related', 1));
  }

  public function testFVariantsWithValue() {
    $this->populateEntity('node', 'page');
    $this->populateEntityFieldValues('field_pull_quote', [
      'Five things are...',
      'Then we will...',
    ]);
    $this->obj->setEntity($this->entity);
    $this->assertSame('Five things are...', $this->obj->f('', 'field_pull_quote'));
    $this->assertSame('Five things are...', $this->obj->f('', 'field_pull_quote', 'value'));
    $this->assertSame('Five things are...', $this->obj->f('', 'field_pull_quote', 'value', 0));
    $this->assertSame('Five things are...', $this->obj->f('', 'field_pull_quote', 0, 'value'));

    $this->assertSame('Then we will...', $this->obj->f('', 'field_pull_quote', 'value', 1));
    $this->assertSame('Then we will...', $this->obj->f('', 'field_pull_quote', 1, 'value'));

    $this->assertSame(['value' => 'Five things are...'], $this->obj->f('', 'field_pull_quote', 0));
    $this->assertSame(['value' => 'Then we will...'], $this->obj->f('', 'field_pull_quote', 1));
  }

  public function testFReturnsValueWhenSetButValueIsANonField() {
    $this->populateEntity('node', 'page');
    $this->entity->nonField = 'tree';
    $this->obj->setEntity($this->entity);

    $this->assertSame('tree', $this->obj->f('', 'nonField'));
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   */
  public function testPathThrowsIfEntityNotSet() {
    $this->obj->path('alpha', 'field_bogus');
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   */
  public function testDateThrowsIfEntityNotSet() {
    $this->obj->date('alpha', 'field_bogus');
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   */
  public function testEntitiesThrowsIfEntityNotSet() {
    $this->obj->entities('alpha', 'field_bogus');
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   */
  public function testEntityThrowsIfEntityNotSet() {
    $this->obj->entity('alpha', 'field_bogus');
  }
}

class Data implements HasEntityInterface {

  const ENTITY_TYPE_ID = 'node';

  protected $entityFieldManager, $entityTypeManager;

  public function __construct(EntityFieldManagerInterface $entity_field_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  use HasEntityTrait;
  use EntityDataAccessorTrait;
}
