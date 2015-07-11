<?php

/**
 * @file
 * Contains \Drupal\ckeditor_test\Plugin\CKEditorPlugin\LlamaContextual.
 */

namespace Drupal\ckeditor_test\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginContextualInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines a "Llama" plugin, with a contextually enabled "llama" feature.
 *
 * @CKEditorPlugin(
 *   id = "llama_contextual",
 *   label = @Translation("Contextual Llama")
 * )
 */
class LlamaContextual extends Llama implements CKEditorPluginContextualInterface {

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginContextualInterface::isEnabled().
   */
  function isEnabled(Editor $editor) {
    // Automatically enable this plugin if the Underline button is enabled.
    $settings = $editor->getSettings();
    foreach ($settings['toolbar']['rows'] as $row) {
      foreach ($row as $group) {
        if (in_array('Strike', $group['items'])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getFile().
   */
  function getFile() {
    return drupal_get_path('module', 'ckeditor_test') . '/js/llama_contextual.js';
  }

}
