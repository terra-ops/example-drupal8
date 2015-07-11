<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateCommentVariableEntityFormDisplaySubjectTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\d6\MigrateDrupal6TestBase;

/**
 * Upgrade comment subject variable to core.entity_form_display.comment.*.default.yml
 *
 * @group migrate_drupal
 */
class MigrateCommentVariableEntityFormDisplaySubjectTest extends MigrateDrupal6TestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('comment', 'node');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    foreach (['comment', 'comment_no_subject'] as $comment_type) {
      entity_create('comment_type', array(
        'id' => $comment_type,
        'target_entity_type_id' => 'node',
      ))
        ->save();
    }
    // Add some id mappings for the dependant migrations.
    $id_mappings = array(
      'd6_comment_type' => array(
        array(array('comment'), array('comment_no_subject')),
      ),
    );
    $this->prepareMigrations($id_mappings);
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_comment_entity_form_display_subject');
    $dumps = array(
      $this->getDumpDirectory() . '/Variable.php',
      $this->getDumpDirectory() . '/NodeType.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests comment subject variable migrated into an entity display.
   */
  public function testCommentEntityFormDisplay() {
    $component = entity_get_form_display('comment', 'comment', 'default')
      ->getComponent('subject');
    $this->assertIdentical('string_textfield', $component['type']);
    $this->assertIdentical(10, $component['weight']);
    $component = entity_get_form_display('comment', 'comment_no_subject', 'default')
      ->getComponent('subject');
    $this->assertNull($component);
  }

}
