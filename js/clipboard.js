/**
 * @file
 *
 * @see https://www.npmjs.com/package/copy-to-clipboard
 */
// import copy from 'copy-to-clipboard'

(function($, Drupal) {
  'use strict'

  Drupal.behaviors.loftCoreClipboard = {
    attach: function(context) {
      $('[data-loft-core-clipboard]', context)
        .once('loft-core-clipboard')
        .click(function(e) {
          var $el = $(this)
          var permalink = $el.data('loft-core-clipboard')

          // Flash the NID for a moment.
          if ($el.data('loft-core-clipboard-reveal')) {
            var duration = $el.data('loft-core-clipboard-reveal-duration') || 3000
            var label = $el.html()
            $el.html(permalink)
            setTimeout(function() {
              $el.html(label)
            }, duration)
          }

          // copy(permalink, {
          //   debug: true,
          //   message: 'Press #{key} to copy',
          // })
          return e.preventDefault()
        })
    },
  }
})(jQuery, Drupal)
