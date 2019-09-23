/**
 * @file
 * The main javascript file for loft_core
 *
 * @ingroup loft_core
 */
var trackJS = trackJS || null;

(function($, Drupal, trackJS) {
  'use strict';

  Drupal.loft = {};

  if (Drupal.ajax) {
    /**
     * Receive an ajax command to fire off trackjs.console.
     *
     * @param ajax
     * @param response
     * @param status
     *
     * @see loft_core_ajax_command_trackjs_console().
     * @link http://docs.trackjs.com/tracker/top-level-api#trackjsconsole
     */
    Drupal.ajax.prototype.commands.loftCoreTrackJsConsole = function(
      ajax,
      response,
      status
    ) {
      trackJS && trackJS.console[response.data.severity](response.data.message);
    };

    /**
     * Ajax command for pushing a jquery bbq state.
     * @param ajax
     * @param response
     *   - hash string
     * @param status
     */
    Drupal.ajax.prototype.commands.loftCoreAjaxBbqPushState = function(
      ajax,
      response,
      status
    ) {
      $.bbq && $.bbq.pushState(response.data.hash);
    };

    /**
     * Update the data-data-time value for an element.
     *
     * @param ajax
     * @param response
     *   - selector
     *   - value
     */
    Drupal.ajax.prototype.commands.update_data_refresh = function(
      ajax,
      response
    ) {
      $(response.selector).attr('data-data-time', response.value);
    };

    /**
     * Ajax command for messages that fade out.
     *
     * @param ajax
     * @param response
     *   - selector
     *   - content
     *   - timeout int How long should it fade out.
     *   - delay int How long before fade out.
     * @param status
     */
    Drupal.ajax.prototype.commands.loftCoreAjaxHtmlAndFade = function(
      ajax,
      response,
      status
    ) {
      var $el = $(response.data.selector),
        prev = $el.data('loftCoreAjaxHtmlAndFade') || {},
        pre = response.data.cssPrefix || '';

      // Remove any delay timeouts currently underway.
      if (prev.timeout) {
        clearTimeout(prev.timeout);
      }

      $el
        .html(response.data.content)

        // Stops any fading animations currently underway
        .stop(false, true)
        .show()
        .addClass(pre + 'is-not-faded')
        .removeClass(pre + 'is-faded');

      if (response.data.duration) {
        var timeout = setTimeout(function() {
          $el.fadeOut(response.data.duration, function() {
            $(response.data.selector)
              .removeClass(pre + 'is-not-faded')
              .addClass(pre + 'is-faded')
              .show();
          });
        }, response.data.delay);
        $el.data('loftCoreAjaxHtmlAndFade', {
          timeout: timeout,
        });
      }
    };
  }

  /**
   * Displays a JavaScript error from an Ajax response when appropriate to do
   * so.
   */
  Drupal.displayAjaxError = function(message) {
    // Skip displaying the message if the user deliberately aborted (for
    // example, by reloading the page or navigating to a different page) while
    // the Ajax request was still ongoing. See, for example, the discussion at
    // http://stackoverflow.com/questions/699941/handle-ajax-error-when-a-user-clicks-refresh.
    if (!Drupal.beforeUnloadCalled) {
      if (trackJS) {
        trackJS.console.error(message);
      }

      // Write the ajax error to the console.
      if (drupalSettings.env.env !== drupalSettings.env.prod) {
        console.log(message);
      }
    }
  };

  if (Drupal.theme) {
    /**
     * Client-side theming using server-side templates.
     *
     * @param string theme
     * @param object vars
     *
     * The DOM must contain a template element provided by the server,
     *   containing twig-style dynamic vars.  See code example below.  Notice
     *   the id must begin with 'js-tpl--' followed by the theme name as it
     *   will be called by.  It must have the class 'js-tpl' and the variable
     *   {{ message }} will be replaced by var.message.
     *
     * @code
     *   <div id="js-tpl--message" class="js-tpl">{{ message }}</div>
     * @endcode
     *
     * Markup should be added to the page_bottom in hook_preprocess_html() like
     *   so.
     * @code
     *   $vars['page']['page_bottom']['js_tpl__form_item_description'] = [
     *     #prefix' => '<span id="js-tpl--form_item__description" class="js-tpl
     *   form-item__description {{ className
     *   }}">',
     *     #suffix' => '</span>',
     *     #markup' => '{{ message }}',
     *   ];
     * @endcode
     *
     * The JS in your theme needs to define a theme method like this, this is
     *   how you register your theme.
     * @code
     *   Drupal.theme.prototype.formItemDescription = function (vars) {
     *     vars = $.extend({
     *       className: '',
     *       message: ''
     *     }, vars);
     *     return Drupal.loft.theme('form_item__description', vars);
     *   };
     * @endcode
     *
     * Finally, where you want the theme to output an instance do this:
     * @code
     *   var message = Drupal.theme('formItemDescription', {
     *     message: 'Tell me something neat.'
     *   }));
     * @endcode
     */
    Drupal.loft.theme = function(theme, vars) {
      var $tpl = $('#js-tpl--' + theme);
      if (!$tpl.length) {
        trackJS && trackJS.console.error('Missing template #js-tpl--" + theme');
        return '';
      }
      var regex,
        html = $tpl
          .clone()
          .removeAttr('id')
          .removeClass('js-tpl')
          .wrap($('<div/>'))
          .parent()
          .html();

      for (var i in vars) {
        regex = new RegExp('{{ ' + i + ' }}', 'g');
        html = html.replace(regex, vars[i]);
        regex = new RegExp('%7B%7B%20' + i + '%20%7D%7D', 'g');
        html = html.replace(regex, vars[i]);
      }

      return html;
    };

    /**
     * Persistent client-side storage API
     */
    Drupal.loft.storage = {
      /**
       * Checks if localStorage is available.
       * @returns {boolean}
       */
      isAvailable: function() {
        try {
          var storage = window.localStorage,
            x = '__storage_test__';
          storage.setItem(x, x);
          storage.removeItem(x);
          return true;
        } catch (e) {
          return false;
        }
      },

      /**
       * This will prefix the localStorage key.
       *
       * This should be considered a public field and may be overwritten by
       * other modules as necessary.
       *
       * @var string
       */
      key: 'Drupal',

      /**
       * Save a key to persistent storage.
       *
       * @param string namespace
       *   Namespaces will break the local storage into it's own item.
       * @param string path
       *   The path in the storage object.
       * @param mixed value
       */
      save: function(namespace, path, value) {
        if (!this.isAvailable()) {
          throw 'LocalStorage is not available.';
        }
        var key = this.key + '.' + namespace,
          data = JSON.parse(localStorage.getItem(key)) || {};
        data[path] = value;
        localStorage.setItem(key, JSON.stringify(data));
      },

      /**
       * Load a key from persistent storage.
       * @param string namespace
       *   Namespaces will break the local storage into it's own item.
       * @param string path
       *   The path in the storage object.
       * @param mixed defaultValue
       *   The value to return when it doesn't already exist.
       * @returns {*|null}
       */
      load: function(namespace, path, defaultValue) {
        var key = this.key + '.' + namespace,
          data = JSON.parse(localStorage.getItem(key)) || {},
          defaultValue = defaultValue || null;
        return data[path] || defaultValue;
      },

      /**
       * Delete a key from persistent storage.
       *
       * @param string namespace
       *   Namespaces will break the local storage into it's own item.
       * @param string path
       *   The path in the storage object.
       */
      delete: function(namespace, path) {
        var key = this.key + '.' + namespace,
          data = JSON.parse(localStorage.getItem(key)) || {};
        delete data[path];
        var saving = JSON.stringify(data);
        if (saving === '{}') {
          localStorage.removeItem(key);
        } else {
          localStorage.setItem(key, saving);
        }
      },
    };
  }
})(jQuery, Drupal, trackJS);
