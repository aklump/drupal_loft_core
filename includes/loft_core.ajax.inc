<?php

/**
 * @file
 * Add in AJAX helpers.
 */

use Drupal\Core\Template\Attribute;

/**
 * Send an ajax command to fire off trackjs.console.
 *
 * @param $severity One of 'log',
 *                  'debug',
 *                  'info',
 *                  'warn',
 *                  'error'
 * @param $message
 *
 * @return array
 *
 * @see  Drupal.ajax.prototype.commands.loftCoreTrackJsConsole().
 * @link http://docs.trackjs.com/tracker/top-level-api#trackjsconsole
 *
 * @throws InvalidArgumentException if $severity is not valid.
 */
function loft_core_ajax_command_trackjs_console($severity,
                                                $message
) {
  $valid = ['log', 'debug', 'info', 'warn', 'error'];
  if (!in_array($severity, $valid)
  ) {
    throw new \InvalidArgumentException("$severity must be one of: " . implode(',',
        $valid));
  }

  return [
    'command' => 'loftCoreTrackJsConsole',
    'data' => [
      'severity' => $severity,
      'message' => $message,
    ],
  ];
}


function loft_core_ajax_command_bbq_push_state($hash_string) {
  return [
    'command' => 'loftCoreAjaxBbqPushState',
    'data' => [
      'hash' => '#' . ltrim($hash_string, '#'),
    ],
  ];
}

/**
 * @param string $selector jQuery selector of the element to replace
 * @param string $content
 * @param int $duration Milliseconds for the fade to last.
 * @param int $delay Milliseconds before fade out commences.
 * @param string $css_prefix A means to custom or remove the css class prefix
 *                           on the fade classes.
 *
 * @return array
 */
function loft_core_ajax_command_html_and_fade($selector, $content, $duration = 800, $delay = 2200, $css_prefix = 'lc-') {
  return [
    'command' => 'loftCoreAjaxHtmlAndFade',
    'data' => [
      'selector' => $selector,
      'content' => $content,
      'duration' => $duration,
      'delay' => $delay,
      'cssPrefix' => $css_prefix,
    ],
  ];
}

/**
 * Add the data-data-time value to an Attributes instance.
 *
 * @param \Drupal\loft_core\Attribute $attributes
 *   The attributes instance.
 */
function loft_core_add_data_refresh(Attribute $attributes) {
  static $microtime = NULL;
  if (is_null($microtime)) {
    $microtime = microtime(TRUE);
  }
  $attributes->setAttribute('data-data-time', $microtime);
}

/**
 * Update the data-data-time attribute of an element.
 *
 * @param string $selector
 *   The CSS selector of the element with data-data-time.
 *
 * @return array
 */
function loft_core_ajax_command_update_data_refresh($selector) {
  static $microtime = NULL;
  if (is_null($microtime)) {
    $microtime = microtime(TRUE);
  }

  return [
    'command' => 'update_data_refresh',
    'selector' => $selector,
    'value' => $microtime,

  ];
}
