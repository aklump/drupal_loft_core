/**
 * @file
 *
 * @see https://www.npmjs.com/package/copy-to-clipboard
 */
import copy from 'copy-to-clipboard';

(function($, Drupal) {
  'use strict';
  Drupal.behaviors.loftCoreClipboard = {
    attach: function(context) {
      $('.js-loft-core-clipboard', context)
        .once('loft-core-clipboard')
        .click(function(e) {
          var permalink = $(this).data('drupal-link-system-path');

          copy(permalink, {
            debug: true,
            message: 'Press #{key} to copy',
          });
          return e.preventDefault();
        });
    },
  };
})(jQuery, Drupal);
