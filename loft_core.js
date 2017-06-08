/**
 * @file
 * The main javascript file for loft_core
 *
 * @ingroup loft_core
 */
var trackJS = trackJS || null;

(function ($, Drupal, trackJS) {
  "use strict";

  /**
   * Receive an ajax command to fire off trackjs.console.
   *
   * @param ajax
   * @param response
   * @param status
   *
   * @see loft_core_ajax_command_trackjs_console().
   */
  if (Drupal.ajax) {
    Drupal.ajax.prototype.commands.loftCoreTrackJsConsole = function (ajax, response, status) {
      trackJS && trackJS.console[response.data.severity](response.data.message);
    };
  }

  /**
   * Displays a JavaScript error from an Ajax response when appropriate to do so.
   */
  Drupal.displayAjaxError = function (message) {
    // Skip displaying the message if the user deliberately aborted (for example,
    // by reloading the page or navigating to a different page) while the Ajax
    // request was still ongoing. See, for example, the discussion at
    // http://stackoverflow.com/questions/699941/handle-ajax-error-when-a-user-clicks-refresh.
    if (!Drupal.beforeUnloadCalled) {
      if (trackJS) {
        trackJS.console.error(message);
      }

      // Write the ajax error to the console.
      if (Drupal.settings.DRUPAL_ENV_ROLE !== 'prod') {
        console.log(message);
      }
    }
  };

})(jQuery, Drupal, trackJS);
