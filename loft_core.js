/**
 * @file
 * The main javascript file for loft_core
 *
 * @ingroup loft_core
 */
(function ($, Drupal) {
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
  Drupal.ajax.prototype.commands.loftCoreTrackJsConsole = function (ajax, response, status) {
    trackJs.console[response.data.severity](response.data.message);
  };

})(jQuery, Drupal);
