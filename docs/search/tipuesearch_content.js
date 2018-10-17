var tipuesearch = {"pages":[{"title":"0.9.12","text":"   BREAKING CHANGE: The structure of theme_form_help has changed, it is now wrapped with a   &lt;  blockquote>, and uses different class names. ","tags":"","url":"CHANGELOG.html"},{"title":"Loft Core","text":"  This module contains features that I wish were in core, and which I often use for all my projects.  Documentation can be found at docs\/index.html or by using the Advanced Help module.  Contact   In the Loft Studios Aaron Klump - Developer PO Box 29294 Bellingham, WA 98228-1294 skype: intheloftstudios d.o: aklump http:\/\/www.InTheLoftStudios.com  ","tags":"","url":"README.html"},{"title":"Database","text":"  loft_core_users  This table augments the users table.  By design, when a user is deleted, their associated record in this table remains; this is because thier information may be used to fight SPAM.  loft_core_users.status  This column shows if a user has been blocked as a robotrap. ","tags":"","url":"database.html"},{"title":"Working With Entities Cheat Sheet","text":"  Whether using data_api() or EntityTrait you want to always cast a variable at the top of your script, this will speed up your code.  The benchmarks show that the get() and f() methods have no performance difference; however instantiating the service is 4x as expensive as data_api().  $n = data_api('node'); $n-&gt;get(... $n-&gt;get(... $n-&gt;get(... $n-&gt;get(...   or  $extract = \\Drupal::service('itls.extract')-&gt;setEntity('node', $node); $extract-&gt;f(... $extract-&gt;f(... $extract-&gt;f(... $extract-&gt;f(...   Pulling Raw Data  \/\/ Define the default and the field name. $url = $extract-&gt;f('#', 'field_url');   If you don't have access to the extract service, then use this more verbose method:  \/\/ Define the entity, path to value, and default. $url = $n-&gt;get($node, 'field_url.0.value', '#');   Pulling out Markup Safe Data  $summary = $extract-&gt;safe('', 'field_summary');   Pulling out field items array  $items = $extract-&gt;items('field_references');   If you don't have access to the extract service, then use this:  $items = $n-&gt;get($node, 'field_references.0', []);   Technical Details  Markup Safe  When given an entity field item the safe value will be the first of:  $extract-&gt;safe('', 'field_thing');    $entity-&gt;field_thing['und'][0]['safe_value'] check_markup($entity-&gt;field_thing['und'][0]['value'], $entity-&gt;field_thing['und'][0]['format']) Core::getSafeMarkupHandler()  ","tags":"","url":"entities.html"},{"title":"Archiving Entities","text":"  Here is an example of how to use loft_core_update__archive_entities in a hook_update_n implementation to archive entites.  \/**  * Create archive of the product entities.  *  * @throws \\DrupalUpdateException  *\/ function MODULE_update_N() {   $sql = \"SELECT   created,   product_id,   p.revision_id,   sku,   title,   type,   field_format_value AS format,   ROUND(commerce_price_amount \/ 100, 2) AS price,   field_xml_metadata_xml AS metadata,   filename AS image_filename,   uri AS image_uri,   field_description_references_nid AS related_nid,   field_product_description_value AS description,   field_product_overview_value AS overview,   field_product_contents_value AS contents from commerce_product p   LEFT JOIN field_data_field_product_images pi ON (pi.entity_id = product_id)   LEFT JOIN field_data_commerce_price cp ON (cp.entity_id = product_id)   LEFT JOIN field_data_field_format ff ON (ff.entity_id = product_id)   LEFT JOIN field_data_field_product_description pd ON (pd.entity_id = product_id)   LEFT JOIN field_data_field_product_contents pc ON (pc.entity_id = product_id)   LEFT JOIN field_data_field_product_overview po ON (po.entity_id = product_id)   LEFT JOIN field_data_field_xml_metadata xml ON (xml.entity_id = product_id)   LEFT JOIN field_data_field_description_references fdr ON (fdr.entity_id = product_id)   LEFT JOIN file_managed f ON (f.fid = pi.field_product_images_fid) WHERE 1;\";   module_load_include('install', 'loft_core', 'loft_core');    return loft_core_update__archive_entities(     'Create archive of the product entities.',     $sql,     'commerce_products',     [       ['image_filename', 'image_uri'],     ],     function ($key, &amp;$value) {       if ($key === 'metadata') {         \/\/ Convert XML To JSON.         $value = simplexml_load_string($value);         $value = $value ? json_encode($value) : NULL;       }        return TRUE;     }   ); }  ","tags":"","url":"entity_archive.html"},{"title":"Forms API","text":"  Hide elements  See loft_core_form_hide_elements().  Disable elements  It is nice to be able to keep an element visible, yet disable it.  Making this easy is the goal of loft_core_form_disable_elements().  Form help  This module defines a new element called 'form_help'. It can also take #weight and #attributes (not shown).      &lt;?php     $form['help'] = [         '#type'    =&gt; 'form_help',          \/\/ Notice an array, where each value is a separate paragraph and will be themed as such. It does not have to be an array, and passing a string is considered a single paragraph.         '#message' =&gt; array(             t(\"You are editing the template for all user collections.\"),             t(\"When a user account is created, the values of this node at the time the user account is created will be copied to the users's account as a Sample Collection.  Changes made to the node are not retroactive and only affect the user collections created from that point forward.\"),         ),     ];   Tabindex  loft_core_form_tabindex()  ","tags":"","url":"forms.html"},{"title":"Parsing HTML\/DOM in PHP","text":"  This module provides the Php Simple Html DOM Parser   https:\/\/packagist.org\/packages\/sunra\/php-simple-html-dom-parser   Here is example code that appends a class and replaces a child node's text with other.  use Sunra\\PhpSimple\\HtmlDomParser;  ...   \/\/ Replace the description with the error and add error class. $dom = HtmlDomParser::str_get_html($vars['element']['#children']);  \/\/ Here we will append a class to already existing classes. $item = $dom-&gt;find('.form-item')[0]; $class = $item-&gt;class . ' form-item--error'; $item-&gt;class = $class;  \/\/ Look for the description and append if not found if (!$dom-&gt;find('.form-item__description')) {     $inner = $item-&gt;innertext;     $inner .= '&lt;span class=\"form-item__description\"&gt;' . $error . '&lt;\/span&gt;';     $item-&gt;innertext = $inner; }  \/\/ Otherwise replaces it's innerHtml with the error else {     $dom-&gt;find('.form-item__description')[0]-&gt;innertext = $error; } $output = (string) $dom;  ","tags":"","url":"html_parsing.html"},{"title":"Images","text":"  Cache Buster  loft_core_image_src_itok_cache_buster ","tags":"","url":"images.html"},{"title":"Loft Core: Users","text":"  Summary  This module adds some extra user-related features to Drupal core.  A big purpose of this module is to track anonymous users that appear \"spammy\" in order to block them from using your site.  It leverages the popular Honeypot module to flag a potential spammy user.  It uses the User restrictions module to then block the users, or generates a snippet of Apache code that can be pasted in the .htaccess file to block by IP address.  Expanding upon core's ability to block or unblock a user in a binary fashion, this module brings additional context to a blocked user, so you have an idea why the user was blocked.  Contexts include: user was robo-trapped, user is honeybear, user was marked spammy manually, user was manually converted from honeybear to spammy.  This module has a setting to record the IPs for authenticated users when they register, which augments the information stored about a user by Drupal core.  The module integrates with views making available the user IP and status, plus a few other fields.  It ships with a default view you may enable to see the augmented user info, or fields that you can add to other views as desired.  It also provides a setting that affects the login form, which will save the username of a user when they login, to a cookie in their browser.  When they visit the login form later, their username in the form is filled in from the cookie. Read more here.  How Are Users Flagged\/Blocked?  The Honepot Module Finds Them Suspicious  When the honeypot module traps a user, it passes that information on to Loft Core Users where they become a \"honeybear\".  This does not affect them yet, merely flags them as suspicious.  After review at \/admin\/config\/people\/loft-core-users, you can manually convert honeybears to spammy users with the following SQL against your database.  UPDATE loft_core_users SET status = 34 where status = 3;   Once converted the will be blocked from using the site.  The User Has Visited an URL They Shouldn't Have  If you see paths that are visited by obvious robots, you can register such paths in hook_menu or hook_menu_alter to use the page callback loft_core_users_robotrap_page_callback().  This sets up a trap that will immediately mark a user with the status LOFT_CORE_USERS_STATUS_ROBOT; they become robo-trapped.  Study the code for usage.  Programatically in Code  Simply mark a user as spammy using loft_core_users_mark_user_spammy.  This is the turn-key function to use.  Or for more control, set one of the following specific statuses on a user with loft_core_users_set_user_status.   LOFT_CORE_USERS_STATUS_ROBOT LOFT_CORE_USERS_STATUS_HONEYBEAR LOFT_CORE_USERS_STATUS_HONEYBEAR_MADE_SPAMMY LOFT_CORE_USERS_STATUS_SPAMMY   How are Anonymous User Tracked?  Authenticated users are considered unique by their Drupal user id.  Anonymous users, however are considered unique, by this module, by looking at their email address domain + their IP address.  As an example, if the website received three requests from anonymous users filling out forms with the following information, this module will see them all as a single user.  This is because all email addresses in the list share the same domain and the IP is the same from all three.       mail   IP       a@spammy.com   172.1.1.1     b@spammy.com   172.1.1.1     c@spammy.com   172.1.1.1     But in this next set, three database records will be created because the IP is different for two and non-existent for one, despite having the same base domain.       mail   IP       a@spammy.com   172.1.1.1     b@spammy.com   172.1.1.2     c@spammy.com   null     Lastly, take note that if the IP is the same but the domain differs, then three records will be tracked:       mail   IP       trouble@spammy.com   172.1.1.1     trouble@morespam.com   172.1.1.1     trouble@totalspam.com   172.1.1.1     What Exactly Does Blocked Mean?  If you have enabled the User Restrictions module, it will leverage the domain data collected by this module to block any user who is filling out the login form, the user regsitration form, or the user profile form, and whose email domain appears in the loft_core_users table as spammy. (Remember honeybears are not blocked yet).  If you have enabled the setting to automatically ban by IP, then new users will be locked out of your site at the Drupal level when their IP is associated with more than N spammy domains, where N is another module setting.  You may review and remove the ips banned in this way at \/admin\/config\/people\/ip-blocking.  Apache Level Blocking  You may routinely visit \/admin\/config\/people\/loft-core-users and export the apache snippet found there and paste it into your web root's .htaccess file.  This will block IPs at the apache level and reduce the load on your server and Drupal.  The admin form always contains all IP address in the database so you can just replace the entire code in .htaccess with the new snippet.  On that same form, you are asked how many spammy domains must share a single IP before they are included in the Apache list.  The logic is that if you see more than, say three domains, which have been marked as spammy coming from a single IP, you are probably safe to ban them from your site at the Apache level.  If you were to set this too low, you run this risk blocking valid users from ever accessing your site, or receiving assistence if wrongly blocked.  When a user is blocked by Drupal, they will see a link to the contact page where they can make a request to be unblocked.  But when they are blocked by apache, they have no way to contact you via website, as your site will never load for them.  They will see something like this:    Suggested Modules to Use  Honeypot  Honeypot rejections will be tracked as \"honey bears\" and can later be converted to \"spammy\" users.  This only works if the $_POST array contains the key mail.  An example is user_register_form, where this works nicely.  Honeybears can be found in the table loft_core_users with a status of LOFT_CORE_USERS_STATUS_HONEYBEAR.  Unless they are converted to spammy users LOFT_CORE_USERS_STATUS_HONEYBEAR_MADE_SPAMMY, they are not blocked from using your site.  User Restrictions  This module is not a dependecy, but without it blocking a user doesn't have any real effect.  Blocks users with an email address containing any domains that have been found as \"spammy\".  The list of domains are found in loft_core_users.  Contact Module  When a user is banned by drupal we will link them to the contact page, where they can ask to be unbanned.  Todos   [] batch process to mark all users that match domains or ips in our loft_users_core. [] vbo integration.  ","tags":"","url":"loft_core_users.html"},{"title":"Redirects","text":"     Consider using the Rabbit Hole module instead.   In order to use this feature you must add the following to settings.php  $conf['loft_core_node_redirects'] = true;   To redirect by node type  This module provides a clean means of redirecting node in page view by bundle.  So you could redirect all nodes in a certain bundle to a different page, mark them as access denied or even not found.  It takes the approach of injecting the redirect in node_page_view, rather than invoking something during hook_init(), which should have a lesser footprint.  HOOK_loft_core_redirect_node_BUNDLE_TYPE_view HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete HOOK_loft_core_node_BUNDLE_TYPE_page   If you set his module up to redirect say, blog nodes, then when you create a new blog, using the node edit form, instead of the normal behavior which takes you to the node view page, you will be taken to the node edit page instead.  Otherwise you would have an issue with getting redirected to places you didn't intend, and if the redirect was to another site, then you'd really be confused as an admin. ","tags":"","url":"redirects.html"},{"title":"Remember Usernames in Login Form","text":"     If you want a persistent login you should install the Persistent Login module.  This means that the user will have the option of checking a box that keeps them logged in via a cookie across sessions, that is when they close their browser.  But if they don't check the box they are logged out.      This is different from core Drupal, which keeps them logged in across sessions always; with no way to opt out.  So the persistent login module adds security for users who are sharing a computer.      Be sure to follow install instructions as settings.php needs to be modified.   This module has a means to remember the username so that it appears in the login form next time they try to log in.  Which is different from the above, but compilments it well.  In fact this module integrates with the Persistent Login module to leverage it's checkbox.  To enable this feature add the following to settings.php:  $conf['loft_core_users_remember_usernames'] = true;   If you enable this feature and you are not going to use Persistent Login, you can leverage a different switch by setting this variable in a hook_form_alter:  $form['#loft_core_users_remember_key'] = 'some_form_value_that_is_toggled';   By default, all usernames will be remembered if you enalbe this feature.  And to control the number of days the username is stored in the cookie you can add this to settings.php.  $conf['loft_core_users_remember_user_for_days'] = 30;  ","tags":"","url":"remember_users.html"},{"title":"Search Results","text":" ","tags":"","url":"search--results.html"},{"title":"settings.php","text":"  Files used  settings.env.php settings.dev.php settings.prod.php settings.local.php   Changes to settings.php   Add the following lines to settings.php at the very end:  define('DRUPAL_ENV_PROD', 'prod'); define('DRUPAL_ENV_STAGING', 'staging'); define('DRUPAL_ENV_DEV', 'dev'); require dirname(__FILE__) . '\/settings.env.php'; require dirname(__FILE__) . '\/settings.' . DRUPAL_ENV . '.php'; require dirname(__FILE__) . '\/settings.local.php';  Create a file settings.env.php with this:  &lt;?php \/**  * @var $settings_presets  * Define the environment: DRUPAL_ENV_DEV or DRUPAL_ENV_PROD  *  * prod:  * - will server minified js  * - will enable the prod settings presets file  *\/ define('DRUPAL_ENV', DRUPAL_ENV_DEV);  Create settings.dev.php and settings.prod.php and put in the environment settings specific to environment, e.g. cache settings. Move the database declaration into settings.local.php.  Also include the following lines:  \/\/ \/\/ \/\/ Define the server environment's role, one of: DRUPAL_ENV_PROD, DRUPAL_ENV_STAGING, DRUPAL_ENV_DEV \/\/ \/\/ The role may be different than the environment, for example a staging server should have a production environment, but it is serving a staging role.  Production would have the same DRUPAL_ENV_PROD for both DRUPAL_ENV_ROLE and DRUPAL_ENV. \/\/ define('DRUPAL_ENV_ROLE', DRUPAL_ENV_DEV);  If you're using loft_deploy module you must add a $conf var right below the DRUPAL_ENV_ROLE definition.  define('DRUPAL_ENV_ROLE', DRUPAL_ENV_DEV); $conf['loft_deploy_site_role'] = DRUPAL_ENV_ROLE;    Available in PHP as  DRUPAL_ENV DRUPAL_ENV_ROLE  DRUPAL_ENV_PROD DRUPAL_ENV_STAGING DRUPAL_ENV_DEV   Available in JS as  Drupal.settings.DrupalEnv Drupal.settings.DrupalEnvRole  Drupal.settings.DrupalEnvProd Drupal.settings.DrupalEnvStaging Drupal.settings.DrupalEnvDev  ","tags":"","url":"settings.html"},{"title":"Smart Urls","text":"  Imagine an admin entering a link to the site.  As developer want the unaliased relative path, as the admin, they copy and past the absolute aliased path.  It's a pain to explain to theme that they have to convert it to the way you want it, so you give up.  But that's where loft_core_smart_url() comes in.  Hand it the url they entered, and it will give you the url you want.  Add this to a presave hook after collection an url in a node form.  Add this to a filter that can then convert the bad urls to good urls.  Rock. ","tags":"","url":"smart_url.html"},{"title":"Stream wrapper \"static-content:\/\/\"","text":"  In some cases you may want to provide static content to your website, which is source controlled; not publicly accessible; is programatically appended to content; not editable by the CMS.  For all of these reasons the private:\/\/ and public:\/\/ stream wrappers will not suffice.  And example use case is your sites \"Privacy Policy\", with HTML that is too detailed to simply drop into a node body field.  Also in this case you may want to protect it from being altered except by your developers.  So then, let's use the stream wrapper static-content:\/\/privacy-policy.html.  By default this points to the following directory relative to web root: ..\/private\/default\/content.  But this can be changed doing something like so in settings.php:  $conf['files_static_content_path'] = '..\/private\/cms'   Permission &amp; Access  The stream wrapper creates an URL like \/system\/content\/privacy-policy.html, however you must grant user the permission Access static content for this to be allowed.  If they have access they can see the raw contents of your file.  How to Use  Here's an example of how you might use this in a render array  &lt;?php ['#markup' =&gt; file_get_contents('static-content:\/\/privacy-policy.html')];   Replace a node's body with static content  It's possible you want to use static content for a node body.  &lt;?php function my_module_node_load($nodes, $types) {   foreach ($nodes as $node) {     if ($node-&gt;nid === 123) {       $node-&gt;body['und'][0]['body'] = file_get_contents('static-content:\/\/privacy-policy.html');        \/\/ Use a format that doesn't mess with your static content here.       $node-&gt;body['und'][0]['format'] = 'raw_html';     }   } }   You will also want to update your admin UI to lock out editing; something like the following...  &lt;?php  function my_module_form_page_node_form_alter(&amp;$form, &amp;$form_state, $form_id) {   $nid = $form['#node']-&gt;nid;   if ($nid == 123) {     \/\/ We hide the forms for these two nodes because they use the     \/\/ static-content:\/\/ wrapper to obtain their contents from HTML file.     loft_core_form_disable_elements($form, ['body']);   } }  ","tags":"","url":"static_content.html"},{"title":"Testing","text":"  Test mode is enabled by default when DRUPAL_ENV does not equal DRUPAL_ENV_PROD.  This means that test classes will be appened to elements whenever the function loft_core_test_class() is used.  When not in test mode, test classes will not be added.  Testing on Prod  In order to test against a production environment you need to enable test mode using an endpoint.  You must set up the endpoint with an access key in your settings.php like this:  $conf['loft_core_testing_key'] = '{some obscure public key that will appear in your url}';   When you visit the url endpoint, include the testing key like so:  \/loft-core\/testing\/enable\/{testing key}   This will enable the test mode for a short duration and cause test classes to appear on production, for your IP only. ","tags":"","url":"testing.html"},{"title":"Add TrackJS.com to your website","text":"   Sign up for an account at http:\/\/www.trackjs.com Obtain your token and add it to settings.php  $conf['loft_core_trackjs_token'] = '5302679c8b624e0395a6a6da5b1199d6';  Add the following snippet in html.tpl.php in your theme before $scripts output.  &lt;?= $loft_core_tracking ?&gt; &lt;?= $scripts; ?&gt;  This will only be present when DRUPAL_ENV_ROLE is prod.   Config and Metadata  See HOOK_loft_core_trackjs_alter() for more info. ","tags":"","url":"trackjs.html"}]};
