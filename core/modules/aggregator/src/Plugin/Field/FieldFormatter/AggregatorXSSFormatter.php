<?php

/**
 * @file
 * Contains \Drupal\aggregator\Plugin\Field\FieldFormatter\AggregatorXSSFormatter.
 */

namespace Drupal\aggregator\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'aggregator_xss' formatter.
 *
 * @FieldFormatter(
 *   id = "aggregator_xss",
 *   label = @Translation("Aggregator XSS"),
 *   description = @Translation("Filter output for aggregator items"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *   }
 * )
 */
class AggregatorXSSFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'markup',
        '#markup' => aggregator_filter_xss($item->value),
      ];
    }
    return $elements;
  }
}
