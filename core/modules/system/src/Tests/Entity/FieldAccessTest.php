<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Entity\FieldAccessTest.
 */

namespace Drupal\system\Tests\Entity;

use Drupal\Core\Access\AccessResult;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests Field level access hooks.
 *
 * @group Entity
 */
class FieldAccessTest extends KernelTestBase {

  /**
   * Modules to load code from.
   *
   * @var array
   */
  public static $modules = array('entity_test', 'field', 'system', 'text', 'filter', 'user', 'entity_reference');

  /**
   * Holds the currently active global user ID that initiated the test run.
   *
   * The user ID gets replaced during the test and needs to be kept here so that
   * it can be restored at the end of the test run.
   *
   * @var int
   */
  protected $activeUid;

  protected function setUp() {
    parent::setUp();
    // Install field configuration.
    $this->installConfig(array('field'));
    // The users table is needed for creating dummy user accounts.
    $this->installEntitySchema('user');
    // Register entity_test text field.
    module_load_install('entity_test');
    entity_test_install();
  }

  /**
   * Tests hook_entity_field_access() and hook_entity_field_access_alter().
   *
   * @see entity_test_entity_field_access()
   * @see entity_test_entity_field_access_alter()
   */
  function testFieldAccess() {
    $values = array(
      'name' => $this->randomMachineName(),
      'user_id' => 1,
      'field_test_text' => array(
        'value' => 'no access value',
        'format' => 'full_html',
      ),
    );
    $entity = entity_create('entity_test', $values);

    // Create a dummy user account for testing access with.
    $values = array('name' => 'test');
    $account = entity_create('user', $values);

    $this->assertFalse($entity->field_test_text->access('view', $account), 'Access to the field was denied.');
    $expected = AccessResult::forbidden()->cacheUntilEntityChanges($entity);
    $this->assertEqual($expected, $entity->field_test_text->access('view', $account, TRUE), 'Access to the field was denied.');

    $entity->field_test_text = 'access alter value';
    $this->assertFalse($entity->field_test_text->access('view', $account), 'Access to the field was denied.');
    $this->assertEqual($expected, $entity->field_test_text->access('view', $account, TRUE), 'Access to the field was denied.');

    $entity->field_test_text = 'standard value';
    $this->assertTrue($entity->field_test_text->access('view', $account), 'Access to the field was granted.');
    $this->assertEqual(AccessResult::allowed(), $entity->field_test_text->access('view', $account, TRUE), 'Access to the field was granted.');
  }
}
