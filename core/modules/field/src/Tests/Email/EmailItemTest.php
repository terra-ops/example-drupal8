<?php

/**
 * @file
 * Contains \Drupal\field\Tests\Email\EmailItemTest.
 */

namespace Drupal\field\Tests\Email;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\field\Tests\FieldUnitTestBase;

/**
 * Tests the new entity API for the email field type.
 *
 * @group field
 */
class EmailItemTest extends FieldUnitTestBase {

  protected function setUp() {
    parent::setUp();

    // Create an email field storage and field for validation.
    entity_create('field_storage_config', array(
      'field_name' => 'field_email',
      'entity_type' => 'entity_test',
      'type' => 'email',
    ))->save();
    entity_create('field_config', array(
      'entity_type' => 'entity_test',
      'field_name' => 'field_email',
      'bundle' => 'entity_test',
    ))->save();

    // Create a form display for the default form mode.
    entity_get_form_display('entity_test', 'entity_test', 'default')
      ->setComponent('field_email', array(
        'type' => 'email_default',
      ))
      ->save();
  }

  /**
   * Tests using entity fields of the email field type.
   */
  public function testEmailItem() {
    // Verify entity creation.
    $entity = entity_create('entity_test');
    $value = 'test@example.com';
    $entity->field_email = $value;
    $entity->name->value = $this->randomMachineName();
    $entity->save();

    // Verify entity has been created properly.
    $id = $entity->id();
    $entity = entity_load('entity_test', $id);
    $this->assertTrue($entity->field_email instanceof FieldItemListInterface, 'Field implements interface.');
    $this->assertTrue($entity->field_email[0] instanceof FieldItemInterface, 'Field item implements interface.');
    $this->assertEqual($entity->field_email->value, $value);
    $this->assertEqual($entity->field_email[0]->value, $value);

    // Verify changing the email value.
    $new_value = $this->randomMachineName();
    $entity->field_email->value = $new_value;
    $this->assertEqual($entity->field_email->value, $new_value);

    // Read changed entity and assert changed values.
    $entity->save();
    $entity = entity_load('entity_test', $id);
    $this->assertEqual($entity->field_email->value, $new_value);

    // Test sample item generation.
    $entity = entity_create('entity_test');
    $entity->field_email->generateSampleItems();
    $this->entityValidateAndSave($entity);
  }

}
