<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\ContentEntityStorageBase.
 */

namespace Drupal\Core\Entity;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ContentEntityStorageBase extends EntityStorageBase implements DynamicallyFieldableEntityStorageInterface {

  /**
   * The entity bundle key.
   *
   * @var string|bool
   */
  protected $bundleKey = FALSE;

  /**
   * Constructs a ContentEntityStorageBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   */
  public function __construct(EntityTypeInterface $entity_type) {
    parent::__construct($entity_type);

    $this->bundleKey = $this->entityType->getKey('bundle');
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hasData() {
    return (bool) $this->getQuery()
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  protected function doCreate(array $values) {
    // We have to determine the bundle first.
    $bundle = FALSE;
    if ($this->bundleKey) {
      if (!isset($values[$this->bundleKey])) {
        throw new EntityStorageException(SafeMarkup::format('Missing bundle for entity type @type', array('@type' => $this->entityTypeId)));
      }
      $bundle = $values[$this->bundleKey];
    }
    $entity = new $this->entityClass(array(), $this->entityTypeId, $bundle);

    foreach ($entity as $name => $field) {
      if (isset($values[$name])) {
        $entity->$name = $values[$name];
      }
      elseif (!array_key_exists($name, $values)) {
        $entity->get($name)->applyDefaultValue();
      }
      unset($values[$name]);
    }

    // Set any passed values for non-defined fields also.
    foreach ($values as $name => $value) {
      $entity->$name = $value;
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldStorageDefinitionCreate(FieldStorageDefinitionInterface $storage_definition) { }

  /**
   * {@inheritdoc}
   */
  public function onFieldStorageDefinitionUpdate(FieldStorageDefinitionInterface $storage_definition, FieldStorageDefinitionInterface $original) { }

  /**
   * {@inheritdoc}
   */
  public function onFieldStorageDefinitionDelete(FieldStorageDefinitionInterface $storage_definition) { }

  /**
   * {@inheritdoc}
   */
  public function onFieldDefinitionCreate(FieldDefinitionInterface $field_definition) { }

  /**
   * {@inheritdoc}
   */
  public function onFieldDefinitionUpdate(FieldDefinitionInterface $field_definition, FieldDefinitionInterface $original) { }

  /**
   * {@inheritdoc}
   */
  public function onFieldDefinitionDelete(FieldDefinitionInterface $field_definition) { }

  /**
   * {@inheritdoc}
   */
  public function purgeFieldData(FieldDefinitionInterface $field_definition, $batch_size) {
    $items_by_entity = $this->readFieldItemsToPurge($field_definition, $batch_size);

    foreach ($items_by_entity as $items) {
      $items->delete();
      $this->purgeFieldItems($items->getEntity(), $field_definition);
    }
    return count($items_by_entity);
  }

  /**
   * Reads values to be purged for a single field.
   *
   * This method is called during field data purge, on fields for which
   * onFieldDefinitionDelete() has previously run.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param $batch_size
   *   The maximum number of field data records to purge before returning.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface[]
   *   An array of field item lists, keyed by entity revision id.
   */
  abstract protected function readFieldItemsToPurge(FieldDefinitionInterface $field_definition, $batch_size);

  /**
   * Removes field items from storage per entity during purge.
   *
   * @param ContentEntityInterface $entity
   *   The entity revision, whose values are being purged.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field whose values are bing purged.
   */
  abstract protected function purgeFieldItems(ContentEntityInterface $entity, FieldDefinitionInterface $field_definition);

  /**
   * {@inheritdoc}
   */
  public function finalizePurge(FieldStorageDefinitionInterface $storage_definition) { }

  /**
   * Checks translation statuses and invoke the related hooks if needed.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity being saved.
   */
  protected function invokeTranslationHooks(ContentEntityInterface $entity) {
    $translations = $entity->getTranslationLanguages(FALSE);
    $original_translations = $entity->original->getTranslationLanguages(FALSE);
    $all_translations = array_keys($translations + $original_translations);

    // Notify modules of translation insertion/deletion.
    foreach ($all_translations as $langcode) {
      if (isset($translations[$langcode]) && !isset($original_translations[$langcode])) {
        $this->invokeHook('translation_insert', $entity->getTranslation($langcode));
      }
      elseif (!isset($translations[$langcode]) && isset($original_translations[$langcode])) {
        $this->invokeHook('translation_delete', $entity->getTranslation($langcode));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function invokeHook($hook, EntityInterface $entity) {
    if ($hook == 'presave') {
      $this->invokeFieldMethod('preSave', $entity);
    }
    parent::invokeHook($hook, $entity);
  }

  /**
   * Invokes a method on the Field objects within an entity.
   *
   * @param string $method
   *   The method name.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity object.
   */
  protected function invokeFieldMethod($method, ContentEntityInterface $entity) {
    foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
      $translation = $entity->getTranslation($langcode);
      foreach ($translation->getFields() as $field) {
        $field->$method();
      }
    }
  }

  /**
   * Checks whether the field values changed compared to the original entity.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   Field definition of field to compare for changes.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to check for field changes.
   * @param \Drupal\Core\Entity\ContentEntityInterface $original
   *   Original entity to compare against.
   *
   * @return bool
   *   True if the field value changed from the original entity.
   */
  protected function hasFieldValueChanged(FieldDefinitionInterface $field_definition, ContentEntityInterface $entity, ContentEntityInterface $original) {
    $field_name = $field_definition->getName();
    $langcodes = array_keys($entity->getTranslationLanguages());
    if ($langcodes !== array_keys($original->getTranslationLanguages())) {
      // If the list of langcodes has changed, we need to save.
      return TRUE;
    }
    foreach ($langcodes as $langcode) {
      $items = $entity->getTranslation($langcode)->get($field_name)->filterEmptyItems();
      $original_items = $original->getTranslation($langcode)->get($field_name)->filterEmptyItems();
      // If the field items are not equal, we need to save.
      if (!$items->equals($original_items)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Populates the affected flag for all the revision translations.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity object being saved.
   */
  protected function populateAffectedRevisionTranslations(ContentEntityInterface $entity) {
    if ($this->entityType->isTranslatable() && $this->entityType->isRevisionable()) {
      $languages = $entity->getTranslationLanguages();
      foreach ($languages as $langcode => $language) {
        $translation = $entity->getTranslation($langcode);
        // Avoid populating the value if it was already manually set.
        $affected = $translation->isRevisionTranslationAffected();
        if (!isset($affected) && $translation->hasTranslationChanges()) {
          $translation->setRevisionTranslationAffected(TRUE);
        }
      }
    }
  }

}
