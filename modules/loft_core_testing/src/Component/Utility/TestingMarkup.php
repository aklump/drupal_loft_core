<?php

namespace Drupal\loft_core_testing\Component\Utility;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Template\Attribute;

/**
 * Utility class to provide testing markup, classes and functions.
 */
class TestingMarkup {

  /**
   * The css prefix used in test classes.
   *
   * @var string
   */
  const CSS_PREFIX = 't-';

  protected static $seenUniqueIds = [];

  /**
   * Holds the value of the environment test to determine if we're in test mode.
   *
   * @var null|bool
   */
  private static $isTestingFlag;

  /**
   * Add a prefixed data attribute only for testing.
   *
   * For example if you want to embed an entity id on a form you should use
   * this.
   *
   * @param array $attributes
   *   The render element attributes array or Attribute object, e.g.,
   *   $attributes['#attributes'] or $vars['attributes'].
   * @param $key
   *   The key, 'data-t-' will be prefixed.
   * @param $value
   *   The value to set, if an array it will be jsoned.
   */
  public static function addDataAttribute(array &$attributes, $key, $value) {
    if (self::isTesting()) {
      $key = strtolower('data-' . self::CSS_PREFIX . $key);
      $value = is_scalar($value) ? $value : json_encode($value);
      if (is_array($attributes)) {
        $attributes[$key] = $value;
      }
      else {
        $attributes->setAttribute($key, $value);
      }
    }
  }

  /**
   * Determining if test mode is enabled.
   *
   * @return bool
   *   True if testing is underway.
   */
  public static function isTesting() {
    if (is_null(self::$isTestingFlag)) {
      self::$isTestingFlag = DRUPAL_ENV !== DRUPAL_ENV_PROD
        || ($expiry = \Drupal::state()->get('loft_core_test_mode_expiry'));

      // First time on page load that we get here, we may delete the expiry.
      if (!self::$isTestingFlag && !empty($expiry) && $expiry < time()) {
        \Drupal::state()->delete('loft_core_test_mode_expiry');
      }
    }

    return self::$isTestingFlag;
  }

  /**
   * Returns a prefixed test id only when test mode is enabled.
   *
   * Note this should be set on elements on the `class` attribute not the `id`
   * attribute.
   *
   * @param string $css_class_base
   *   The bass CSS class, will be prefixed to and returned.
   *
   * @return string
   *   The prefixed test class name.
   */
  public static function id($css_class_base) {
    return self::isTesting() ? self::CSS_PREFIX . strtolower($css_class_base) : '';
  }

  /**
   * Use this to generate unique ids like when looping rows or something.
   *
   * @param $css_class_base
   *
   * @return string
   */
  public static function uniqueId($css_class_base) {
    if (empty(static::$seenUniqueIds[$css_class_base])) {
      static::$seenUniqueIds[$css_class_base] = 1;
    }
    else {
      $css_class_base .= '--' . ++static::$seenUniqueIds[$css_class_base];
    }

    return static::id($css_class_base);
  }

  private static function getModifiedFormId(string $form_id) {
    return;
  }

  /**
   * Recursively add test classes to a form.
   *
   * @param array $element
   *   The form, form element or other renderable element.
   * @param array $context
   *   Used internally, by recursion.
   */
  public static function formAddClasses(array &$element, array $context = []) {
    if (isset($element['#type'])) {
      $class = [];
      $path = '#attributes';
      $element['#after_build'] = $element['#after_build'] ?? [];
      switch ($element['#type']) {
        case 'form':
          list($context['form_name']) = explode('--', $element['#id'] . '--');
          $context['form_name'] = preg_replace('/[-_]+form$/', '', $context['form_name']);
          $context['form_name'] = preg_replace('/^node[-_]+/', '', $context['form_name']);
          $class[] = $context['form_name'];
          $class[] = 'form';
          break;

        case 'link':
        case 'submit':
          $class = $context['class_base'] ?? [];
          switch ($context['parent']['#type'] ?? '') {
            case 'managed_file':
              $class[] = preg_replace('/[_-]+button?/', '', $context['parent_key']);
              break;
          }
          if (!empty($context['paragraph_operations'])) {
            $class[] = $context['paragraph_operations'];
            $class[] = $element['#bundle_machine_name'];
          }
          elseif (!empty($context['paragraphs_actions'])) {
            $class[] = $context['paragraphs_actions'];
            $class[] = preg_replace('/[-_]+button$/', '', $context['parent_key']);
          }
          elseif (empty($class)) {

            // Mutate some form names.
            $form_name = $context['form_name'] ?? '';
            if ($form_name) {
              $form_name = preg_replace('/\-form\-commerce\-product\-\d+/', '', $form_name);
              $class[] = $form_name;
            }
            $class[] = $context['parent_key'] ?? '';
          }
          break;

        case 'address':
          $element['#after_build'][] = [self::class, 'addressAfterBuild'];
          $context['class_base'] = self::defaultClassGenerator($context);
          // Commerce is not consistent across it's payment methods, so we are
          // going to try and make it so here.
          if (isset($context['form_name']) && strpos($context['form_name'], 'commerce-checkout-flow') === 0) {
            $first = array_shift($context['path']);
            while (reset($context['path']) !== 'billing_information') {
              array_shift($context['path']);
            }
            array_unshift($context['path'], $first);
          }
          $element['#loft_core_testing']['context'] = $context;
          break;

        case 'managed_file':
          $element['#after_build'][] = [self::class, 'fileAfterBuild'];
          $context['class_base'] = self::defaultClassGenerator($context);
          $element['#loft_core_testing']['context'] = $context;
          break;

        case 'commerce_price':
          $element['#after_build'][] = [
            self::class,
            'commercePriceAfterBuild',
          ];
          $element['#loft_core_testing']['context'] = $context;
          break;

        case 'details':
          $element['#after_build'][] = [
            self::class,
            'stripTestClassesAfterBuild',
          ];
          $class = self::defaultClassGenerator($context);
          break;

        case 'datetime':
          $element['#after_build'][] = [
            self::class,
            'stripTestClassesAfterBuild',
          ];
          $element['#after_build'][] = [self::class, 'datetimeAfterBuild'];
          $class = self::defaultClassGenerator($context);
          $element['#loft_core_testing']['context'] = $context;
          break;

        case 'details':
        case 'container':
          if (!empty($context['parent']['#paragraphs_widget']) && isset($element['#delta'])) {
            $class[] = $context['field_name'];
            $class[] = $element['#delta'] ? 'item' . $element['#delta'] : 'item';
          }
          else {
            $c = $context;
            array_unshift($c['path'], 'wrap');
            $class = self::defaultClassGenerator($c);
          }
          break;

        case 'checkbox':
        case 'checkboxes':
        case 'email':
        case 'entity_autocomplete':
        case 'file':
        case 'number':
        case 'password':
        case 'search':
        case 'select':
        case 'tel':
        case 'text_format':
        case 'textarea':
        case 'textfield':
        case 'url':
          $class = self::defaultClassGenerator($context);
          break;

        case 'password_confirm':
          $class = self::defaultClassGenerator($context);
          $element['#after_build'][] = [
            self::class,
            'passwordConfirmAfterBuild',
          ];
          break;

        case 'paragraphs_actions':
          $context['paragraphs_actions'] = implode('', array_filter(array_slice($context['path'], 0, 3), function ($value) {
            return $value !== 'widget';
          }));
          break;

        case 'paragraph_operations':
          $context['paragraph_operations'] = reset($context['path']) . '_add';
          $class[] = $context['paragraph_operations'];
          foreach ($element['#links'] as &$link) {
            self::formAddClasses($link, $context);
          }
          unset($context['paragraph_operations']);
          break;

        case 'item':
        case 'hidden':
        case 'vertical_tabs':
        case 'weight':
        case 'value':
        case 'language_select':
        case 'token':
          // Do nothing for these types.
          break;

        default:
          break;
      }
      if ($path && $class) {
        $s = data_api();
        $test_class = self::id(implode('__', $class));
        $attributes = $s->get($element, $path);
        if (is_null($attributes)) {

          switch ($element['#type']) {
            case 'submit':
              // @see bartik_form_alter.
              $attributes = new Attribute();
              $attributes->addClass($test_class);
              break;

            default:
              $attributes = ['class' => [$test_class]];
              break;
          }
        }
        elseif (is_array($attributes)) {
          $attributes['class'][] = $test_class;
        }
        $s->set($element, $path, $attributes);
      }
    }
    foreach (Element::children($element) as $key) {
      if (!is_array($element[$key])) {
        return;
      }
      $context['field_name'] = $element['#field_name'] ?? NULL;
      if (empty($context['field_name'])
        && strpos($key, 'field_') === 0) {
        $context['field_name'] = $key;
      }
      $context['parent'] = $element;
      $context['parent_key'] = $key;
      $context['path'][] = $key;
      self::formAddClasses($element[$key], $context);
      array_pop($context['path']);
    }
    unset($context['form']);
    unset($context['parent']);
    unset($context['parent_key']);
    unset($context['field_name']);
    unset($context['paragraphs_actions']);
  }

  /**
   * Default class generator suitable for most elements.
   *
   * @param array $context
   *   The current recursive context.
   *
   * @return array
   *   The classes array to use on the element.
   *
   * @see ::formAddClasses
   */
  private static function defaultClassGenerator(array $context) {

    $class = array_filter($context['path'], function ($item) {
      return is_numeric($item) || !(in_array($item, [

          // Remove certain strings from the path that does not help our class
          // be readable nor distinct.  These are the "main" keys for a field
          // with multiple values.  What could be considered the assumed main
          // value.
          'subform',
          'target_id',
          'uri',
          'value',
          'widget',
        ]));
    });

    // Squish numeric indexes into the element names, removing 0 as it's
    // assumed.  This creates a cleaner look.
    $key = 0;
    $temp = [];
    while ($item = array_shift($class)) {
      $temp[$key] = $item;
      if (is_numeric(reset($class))) {
        $number = array_shift($class);
        $number > 0 && $temp[$key] .= $number;
      }
      ++$key;
    }
    $class = $temp;

    // Add the form id to certain forms.
    switch ($context['form_name'] ?? '') {
      case 'user-login':
        array_unshift($class, $context['form_name']);
        break;
    }

    return $class;
  }

  /**
   * Handle bug with datetime render.
   *
   * This handles a problem with the way datetime renders the
   * #attributes.class, where it adds it both to the input and the wrapper.
   * This will remove test classes from the wrapper so that we can target the
   * input only, and directly.
   *
   * @param array $element
   *   The datetime element.
   *
   * @return array
   *   The altered element.
   */
  public static function stripTestClassesAfterBuild(array $element) {
    $element['#attributes']['class'] = array_filter($element['#attributes']['class'], function ($item) {
      return strpos($item, self::CSS_PREFIX) !== 0;
    });

    return $element;
  }

  public static function datetimeAfterBuild(array $element) {
    if (isset($element['time'])) {
      $element['time']['#attributes']['class'] = array_map(function ($item) {
        return strpos($item, self::CSS_PREFIX) === 0 ? $item . '__time' : $item;
      }, $element['time']['#attributes']['class']);
    }

    return $element;
  }

  public static function commercePriceAfterBuild(array $element) {
    $context = $element['#loft_core_testing']['context'];
    $class = self::defaultClassGenerator($context);
    $element['number']['#attributes']['class'][] = self::id(implode('__', $class));

    return $element;
  }

  public static function detailsAfterBuild(array $element) {
    $element['#attributes']['class'] = array_filter($element['#attributes']['class'], function ($item) {
      return strpos($item, self::CSS_PREFIX) !== 0;
    });

    return $element;
  }

  /**
   * Add test classes to each of the two password textfields.
   *
   * @param array $element
   *   The password element.
   *
   * @return array
   *   The modified password element.
   */
  public static function passwordConfirmAfterBuild(array $element) {
    loft_core_element_add_test_classes($element, [
      'pass1',
      'pass2',
    ]);

    return $element;
  }

  /**
   * Add test classes to each of the two password textfields.
   *
   * @param array $element
   *   The password element.
   *
   * @return array
   *   The modified password element.
   */
  public static function fileAfterBuild(array $element, FormStateInterface $form_state) {
    $context = $element['#loft_core_testing']['context'];
    foreach (Element::children($element) as $child) {
      $context['parent'] = $element;
      $context['parent_key'] = $child;
      self::formAddClasses($element[$child], $context);
    }

    return $element;
  }

  public static function addressAfterBuild(array $element, FormStateInterface $form_state) {
    $context = $element['#loft_core_testing']['context'];
    $path = $context['path'];
    array_pop($path);
    foreach (Element::children($element) as $child) {
      $context['parent'] = $element;
      $context['parent_key'] = $child;
      $context['path'] = $path;
      $context['path'][] = $child;

      // Remove the redundant "address".
      $last = array_pop($context['path']);
      $context['path'][] = preg_replace('/^address_/', '', $last);
      self::formAddClasses($element[$child], $context);
    }

    return $element;
  }

}
