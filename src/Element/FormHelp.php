<?php

namespace Drupal\loft_core\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides an element to forms to provide in-form help messages.
 *
 * Usage example:
 *
 * @code
 *   $form['user_help_text'] = [
 *     '#type' => 'form_help',
 *     '#message' => [
 *       t('Lorem ipsum dolor sit amet.'),
 *       \Drupal\Core\Render\Markup::create('Ut enim ad minim veniam.'),
 *     ],
 *   ];
 *   $form['more_help_text'] = [
 *     '#type' => 'form_help',
 *     '#attributes' => ['class' => ['alpha', 'bravo']],
 *     '#message' => t('Lorem ipsum dolor sit amet veniam.'),
 *   ];
 * @endcode
 *
 * @FormElement("form_help")
 */
class FormHelp extends FormElement {

  public function getInfo() {
    return [
      '#weight' => -99,
      '#message' => [],
      '#theme_wrappers' => [
        'form_help',
        'form_element',
      ],
    ];

    //    $class = get_class($this);
    //
    //    return [
    //      '#input' => TRUE,
    //      '#size' => 60,
    //      '#maxlength' => 128,
    //      '#autocomplete_route_name' => FALSE,
    //      '#process' => [
    //        [$class, 'processAutocomplete'],
    //        [$class, 'processAjaxForm'],
    //        [$class, 'processPattern'],
    //        [$class, 'processGroup'],
    //      ],
    //      '#pre_render' => [
    //        [$class, 'preRenderTextfield'],
    //        [$class, 'preRenderGroup'],
    //      ],
    //      '#theme' => 'input__textfield',
    //      '#theme_wrappers' => ['form_element'],
    //    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    return NULL;
  }

}
