<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\source\d6\ProfileField.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
use Drupal\migrate\Row;

/**
 * Drupal 6 profile fields source from database.
 *
 * @MigrateSource(
 *   id = "d6_profile_field",
 *   source_provider = "profile"
 * )
 */
class ProfileField extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('profile_fields', 'pf')
      ->fields('pf', array(
        'fid',
        'title',
        'name',
        'explanation',
        'category',
        'page',
        'type',
        'weight',
        'required',
        'register',
        'visibility',
        'autocomplete',
        'options',
      ));

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if ($row->getSourceProperty('type') == 'selection') {
      // Get the current options.
      $current_options = preg_split("/[\r\n]+/", $row->getSourceProperty('options'));
      // Select the list values from the profile_values table to ensure we get
      // them all since they can get out of sync with profile_fields.
      $options = $this->getDatabase()->query('SELECT DISTINCT value FROM {profile_values} WHERE fid = :fid', array(':fid' => $row->getSourceProperty('fid')))->fetchCol();
      $options = array_merge($current_options, $options);
      // array_combine() takes care of any duplicates options.
      $row->setSourceProperty('options', array_combine($options, $options));
    }

    if ($row->getSourceProperty('type') == 'checkbox') {
      // D6 profile checkboxes values are always 0 or 1 (with no labels), so we
      // need to create two label-less options that will get 0 and 1 for their
      // keys.
      $row->setSourceProperty('options', array(NULL, NULL));
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'fid' => $this->t('Primary Key: Unique profile field ID.'),
      'title' => $this->t('Title of the field shown to the end user.'),
      'name' => $this->t('Internal name of the field used in the form HTML and URLs.'),
      'explanation' => $this->t('Explanation of the field to end users.'),
      'category' => $this->t('Profile category that the field will be grouped under.'),
      'page' => $this->t("Title of page used for browsing by the field's value"),
      'type' => $this->t('Type of form field.'),
      'weight' => $this->t('Weight of field in relation to other profile fields.'),
      'required' => $this->t('Whether the user is required to enter a value. (0 = no, 1 = yes)'),
      'register' => $this->t('Whether the field is visible in the user registration form. (1 = yes, 0 = no)'),
      'visibility' => $this->t('The level of visibility for the field. (0 = hidden, 1 = private, 2 = public on profile but not member list pages, 3 = public on profile and list pages)'),
      'autocomplete' => $this->t('Whether form auto-completion is enabled. (0 = disabled, 1 = enabled)'),
      'options' => $this->t('List of options to be used in a list selection field.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['fid']['type'] = 'integer';
    return $ids;
  }

}
