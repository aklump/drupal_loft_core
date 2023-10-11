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
      $('[data-loft-core-clipboard]', context)
        .once('loft-core-clipboard')
        .click(function(e) {
          const $el = $(this);
          const permalink = $el.data('loft-core-clipboard');
          const confirm = $el.data('loft-core-clipboard-confirm') || null;

          let result = copy(permalink, {
            debug: true,
            message: 'Press #{key} to copy',
          });

          if (result && confirm) {
            const label = $el.html();
            const duration =
              $el.data('loft-core-clipboard-confirm-duration') || 3000;
            $el.html(confirm);
            setTimeout(function() {
              $el.html(label);
            }, duration);
          }

          return e.preventDefault();
        });
    },
  };
})(jQuery, Drupal);
