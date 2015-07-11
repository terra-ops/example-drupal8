<?php

/**
 * @file
 * Contains \Drupal\Core\Validation\DrupalTranslator.
 */

namespace Drupal\Core\Validation;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Translates strings using Drupal's translation system.
 *
 * This class is used by the Symfony validator to translate violation messages.
 */
class DrupalTranslator implements TranslatorInterface {

  /**
   * The locale used for translating.
   *
   * @var string
   */
  protected $locale;

  /**
   * Implements \Symfony\Component\Translation\TranslatorInterface::trans().
   */
  public function trans($id, array $parameters = array(), $domain = NULL, $locale = NULL) {

    return t($id, $this->processParameters($parameters), $this->getOptions($domain, $locale));
  }

  /**
   * Implements \Symfony\Component\Translation\TranslatorInterface::transChoice().
   */
  public function transChoice($id, $number, array $parameters = array(), $domain = NULL, $locale = NULL) {
    // Violation messages can separated singular and plural versions by "|".
    $ids = explode('|', $id);

    if (!isset($ids[1])) {
      throw new \InvalidArgumentException(sprintf('The message "%s" cannot be pluralized, because it is missing a plural (e.g. "There is one apple|There are @count apples").', $id));
    }
    return \Drupal::translation()->formatPlural($number, $ids[0], $ids[1], $this->processParameters($parameters), $this->getOptions($domain, $locale));
  }

  /**
   * Implements \Symfony\Component\Translation\TranslatorInterface::setLocale().
   */
  public function setLocale($locale) {
    $this->locale = $locale;
  }

  /**
   * Implements \Symfony\Component\Translation\TranslatorInterface::getLocale().
   */
  public function getLocale() {
    return $this->locale ? $this->locale : \Drupal::languageManager()->getCurrentLanguage()->getId();
  }

  /**
   * Processes the parameters array for use with t().
   */
  protected function processParameters(array $parameters) {
    $return = array();
    foreach ($parameters as $key => $value) {
      if (is_object($value)) {
        // t() does not work will objects being passed as replacement strings.
      }
      // Check for symfony replacement patterns in the form "{{ name }}".
      elseif (strpos($key, '{{ ') === 0 && strrpos($key, ' }}') == strlen($key) - 3) {
        // Transform it into a Drupal pattern using the format %name.
        $key = '%' . substr($key, 3, strlen($key) - 6);
        $return[$key] = $value;
      }
      else {
        $return[$key] = $value;
      }
    }
    return $return;
  }

  /**
   * Returns options suitable for use with t().
   */
  protected function getOptions($domain = NULL, $locale = NULL) {
    // We do not support domains, so we ignore this parameter.
    // If locale is left NULL, t() will default to the interface language.
    $locale = isset($locale) ? $locale : $this->locale;
    return array('langcode' => $locale);
  }
}
