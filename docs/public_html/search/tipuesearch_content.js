var tipuesearch = {"pages":[{"title":"Loft Core","text":"  This module contains features that I wish were in core, and which I often use for all my projects.  Autoloading  Some features require the autoloading, if you use these features you have to enable them.  To activate the autoloading feature of this module add this to settings.php:  $conf['loft_core_autoload'] = true;   Contact   In the Loft Studios Aaron Klump - Developer PO Box 29294 Bellingham, WA 98228-1294 skype: intheloftstudios d.o: aklump http:\/\/www.InTheLoftStudios.com  ","tags":"","url":"README.html"},{"title":"Forms API","text":"  Hide elements  See loft_core_form_hide_elements().  Disable elements  It is nice to be able to keep an element visible, yet disable it.  Making this easy is the goal of loft_core_form_disable_elements().  Form help  This module defines a new element called 'form_help'. It can also take #weight and #attributes (not shown).      &lt;?php     $form['help'] = [         '#type'    =&gt; 'form_help',          \/\/ Notice an array, where each value is a separate paragraph and will be themed as such. It does not have to be an array, and passing a string is considered a single paragraph.         '#message' =&gt; array(             t(\"You are editing the template for all user collections.\"),             t(\"When a user account is created, the values of this node at the time the user account is created will be copied to the users's account as a Sample Collection.  Changes made to the node are not retroactive and only affect the user collections created from that point forward.\"),         ),     ];  ","tags":"","url":"forms.html"},{"title":"Redirects","text":"  To redirect by node type  This module provides a clean means of redirecting node in page view by bundle.  So you could redirect all nodes in a certain bundle to a different page, mark them as access denied or even not found.  It takes the approach of injecting the redirect in node_page_view, rather than invoking something during hook_init(), which should have a lesser footprint.  see HOOK_loft_core_redirect_node_BUNDLE_TYPE().  If you set his module up to redirect say, blog nodes, then when you create a new blog, using the node edit form, instead of the normal behavior which takes you to the node view page, you will be taken to the node edit page instead.  Otherwise you would have an issue with getting redirected to places you didn't intend, and if the redirect was to another site, then you'd really be confused as an admin. ","tags":"","url":"redirects.html"},{"title":"Search Results","text":" ","tags":"","url":"search--results.html"},{"title":"Two new constants","text":"  Files used  settings.env.php settings.dev.php settings.prod.php settings.local.php   Changes to settings.php   Add the following lines to settings.php:  require dirname(__FILE__) . '\/settings.env.php'; require dirname(__FILE__) . '\/settings.' . DRUPAL_ENV . '.php'; require dirname(__FILE__) . '\/settings.local.php';  Create a file settings.env.php with this:  &lt;?php \/**  * @var $settings_presets  * Define the environment: dev or prod  *  * prod:  * - will server minified js  * - will enable the prod settings presets file  *\/ define('DRUPAL_ENV', 'dev');  Create settings.dev.php and settings.prod.php and put in the environment settings specific to environment, e.g. cache settings. Move the database declaration into settings.local.php.  Also include the following lines:  \/\/ \/\/ \/\/ Define the server environment's role, one of: prod, staging, dev \/\/ \/\/ The role may be different than the environment, for example a staging server should have a production environment, but it is serving a staging role.  Production would have the same 'prod' for both DRUPAL_ENV_ROLE and DRUPAL_ENV. \/\/ define('DRUPAL_ENV_ROLE', 'dev');    Available in PHP as  DRUPAL_ENV DRUPAL_ENV_ROLE   Available in JS as  Drupal.settings.DRUPAL_ENV Drupal.settings.DRUPAL_ENV_ROLE  ","tags":"","url":"settings.html"},{"title":"Add TrackJS.com to your website","text":"   Sign up for an account at http:\/\/www.trackjs.com Obtain your token and add it to settings.php  $conf['loft_core_trackjs_token'] = '5302679c8b624e0395a6a6da5b1199d6';  Add the following snippet in html.tpl.php in your theme before $scripts output.  &lt;?= $loft_core_tracking ?&gt; &lt;?= $scripts; ?&gt;  This will only be present when DRUPAL_ENV_ROLE is prod.   Config and Metadata  See HOOK_loft_core_trackjs_alter() for more info. ","tags":"","url":"trackjs.html"}]};
