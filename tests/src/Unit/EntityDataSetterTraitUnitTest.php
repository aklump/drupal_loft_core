<?php

namespace Drupal\Tests\loft_core\Unit;

use AKlump\DrupalTest\Drupal8\EntityMockingTrait;
use AKlump\DrupalTest\Drupal8\UnitTestCase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinitionInterface;
use Drupal\Core\Url;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\loft_core\Entity\EntityDataSetterTrait;
use Drupal\loft_core\Entity\HasEntityInterface;
use Drupal\loft_core\Entity\HasEntityTrait;
use Drupal\node\NodeInterface;

/**
 * @group loft_core
 */
class EntityDataSetterTraitUnitTest extends UnitTestCase {

  use EntityMockingTrait;

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return [
      'classToBeTested' => Setter::class,
      'classArgumentsMap' => [],
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

  public function testSetsCorrectFormatForDateTimeEndValue() {
    $this->populateEntity('node', 'page');
    $this->populateDateField(date_create('2/22/74'), 'field_date', 'datetime');

    $return = $this->obj
      ->setEntity($this->entity)
      ->setDate('2/22/74', 'field_date', 'end_value');
    $this->assertSame($this->obj, $return);
  }

  public function testSetsCorrectFormatForDateEndValue() {
    $this->populateEntity('node', 'page');
    $this->populateDateField(date_create('2/22/74'), 'field_date', 'date');

    $return = $this->obj
      ->setEntity($this->entity)
      ->setDate('2/22/74', 'field_date', 'end_value');
    $this->assertSame($this->obj, $return);
  }

  public function testSetsCorrectFormatForDateTimeType() {
    $this->populateEntity('node', 'page');
    $this->populateDateField(date_create('7/31/74'), 'field_date', 'datetime');

    $return = $this->obj
      ->setEntity($this->entity)
      ->setDate('7/31/74', 'field_date');
    $this->assertSame($this->obj, $return);
  }

  public function testSetsCorrectFormatForDateType() {
    $this->populateEntity('node', 'page');

    $definition = \Mockery::mock();
    $definition->shouldReceive('getSetting')
      ->with('datetime_type')
      ->andReturns('date');

    $field = \Mockery::mock();
    $field->shouldReceive('getFieldDefinition')
      ->andReturns($definition);

    $this->entity->shouldReceive('get')->with('field_date')->andReturns($field);
    $this->entity->shouldReceive('set')->with('field_date', [
      'value' => '1974-07-31',
    ])->once();

    $return = $this->obj
      ->setEntity($this->entity)
      ->setDate('7/31/74', 'field_date');
    $this->assertSame($this->obj, $return);
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Cannot understand string date value
   */
  public function testBadDateValueStringThrows() {
    $this->populateEntity('node', 'page');
    $this->obj
      ->setEntity($this->entity)
      ->setDate('cannot understand value', 'field_date');
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testObjectWithoutFormatMethodThrows() {
    $this->populateEntity('node', 'page');
    $this->obj
      ->setEntity($this->entity)
      ->setDate(new \stdClass(), 'field_date');
  }

  /**
   * @expectedException \Drupal\loft_core\Entity\MissingRequiredEntityException
   */
  public function testSetDateThrowsWithoutEntity() {
    $this->obj->setDate('4/4/84', 'field_date');
  }

  /**
   * Mocks a datefield on an entity for set testing.
   *
   * @param \DateTime $control_date
   *   The date value that will be set.
   * @param string $field_name
   *   The date field name.
   * @param string $datetime_type
   *   The field type: date or datetime.
   */
  private function populateDateField(\DateTime $control_date, $field_name, $datetime_type) {
    $definition = \Mockery::mock();
    $definition->shouldReceive('getSetting')
      ->with('datetime_type')
      ->andReturns($datetime_type);

    $storage_format = $datetime_type === DateTimeItem::DATETIME_TYPE_DATE ? DateTimeItemInterface::DATE_STORAGE_FORMAT : DateTimeItemInterface::DATETIME_STORAGE_FORMAT;

    $field = \Mockery::mock();
    $field->shouldReceive('getFieldDefinition')
      ->andReturns($definition);

    $this->entity->shouldReceive('get')->with($field_name)->andReturns($field);
    $this->entity->shouldReceive('set')->with($field_name, [
      'value' => $control_date->format($storage_format),
    ])->once();
  }

}

class Setter implements HasEntityInterface {

  const ENTITY_TYPE_ID = 'node';

  use HasEntityTrait;
  use EntityDataSetterTrait;
}
