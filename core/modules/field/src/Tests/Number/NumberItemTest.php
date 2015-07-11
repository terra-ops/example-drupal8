<?php

/**
 * @file
 * Contains \Drupal\field\Tests\Number\NumberItemTest.
 */

namespace Drupal\field\Tests\Number;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field\Tests\FieldUnitTestBase;

/**
 * Tests the new entity API for the number field type.
 *
 * @group field
 */
class NumberItemTest extends FieldUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array();

  protected function setUp() {
    parent::setUp();

    // Create number field storages and fields for validation.
    foreach (array('integer', 'float', 'decimal') as $type) {
      entity_create('field_storage_config', array(
        'entity_type' => 'entity_test',
        'field_name' => 'field_' . $type,
        'type' => $type,
      ))->save();
      entity_create('field_config', array(
        'entity_type' => 'entity_test',
        'field_name' => 'field_' . $type,
        'bundle' => 'entity_test',
      ))->save();
    }
  }

  /**
   * Tests using entity fields of the number field type.
   */
  public function testNumberItem() {
    // Verify entity creation.
    $entity = entity_create('entity_test');
    $integer = rand(0, 10);
    $entity->field_integer = $integer;
    $float = 3.14;
    $entity->field_float = $float;
    $decimal = '31.3';
    $entity->field_decimal = $decimal;
    $entity->name->value = $this->randomMachineName();
    $entity->save();

    // Verify entity has been created properly.
    $id = $entity->id();
    $entity = entity_load('entity_test', $id);
    $this->assertTrue($entity->field_integer instanceof FieldItemListInterface, 'Field implements interface.');
    $this->assertTrue($entity->field_integer[0] instanceof FieldItemInterface, 'Field item implements interface.');
    $this->assertEqual($entity->field_integer->value, $integer);
    $this->assertEqual($entity->field_integer[0]->value, $integer);
    $this->assertTrue($entity->field_float instanceof FieldItemListInterface, 'Field implements interface.');
    $this->assertTrue($entity->field_float[0] instanceof FieldItemInterface, 'Field item implements interface.');
    $this->assertEqual($entity->field_float->value, $float);
    $this->assertEqual($entity->field_float[0]->value, $float);
    $this->assertTrue($entity->field_decimal instanceof FieldItemListInterface, 'Field implements interface.');
    $this->assertTrue($entity->field_decimal[0] instanceof FieldItemInterface, 'Field item implements interface.');
    $this->assertEqual($entity->field_decimal->value, $decimal);
    $this->assertEqual($entity->field_decimal[0]->value, $decimal);

    // Verify changing the number value.
    $new_integer = rand(11, 20);
    $new_float = rand(1001, 2000) / 100;
    $new_decimal = '18.2';
    $entity->field_integer->value = $new_integer;
    $this->assertEqual($entity->field_integer->value, $new_integer);
    $entity->field_float->value = $new_float;
    $this->assertEqual($entity->field_float->value, $new_float);
    $entity->field_decimal->value = $new_decimal;
    $this->assertEqual($entity->field_decimal->value, $new_decimal);

    // Read changed entity and assert changed values.
    $entity->save();
    $entity = entity_load('entity_test', $id);
    $this->assertEqual($entity->field_integer->value, $new_integer);
    $this->assertEqual($entity->field_float->value, $new_float);
    $this->assertEqual($entity->field_decimal->value, $new_decimal);

    /// Test sample item generation.
    $entity = entity_create('entity_test');
    $entity->field_integer->generateSampleItems();
    $entity->field_float->generateSampleItems();
    $entity->field_decimal->generateSampleItems();
    $this->entityValidateAndSave($entity);
  }

}
