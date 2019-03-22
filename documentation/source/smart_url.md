# Smart Urls

* User enters: `http://www.mysite.com/some-cool-aliased-title`

* You want: `/node/123`

* Use `loft_core_smart_url`

Imagine an admin entering a link to the site.  As developer want the unaliased relative path, as the admin, they copy and past the absolute aliased path.  It's a pain to explain to theme that they have to convert it to the way you want it, so you give up.  But that's where `loft_core_smart_url()` comes in.  Hand it the url they entered, and it will give you the url you want.

Add this to a presave hook after collection an url in a node form.

Add this to a filter that can then convert the bad urls to good urls.

Rock.

## Configuration

This should work out of the box, but to fine tune it add something like the following to _settings.php_.

    $config['loft_core.settings']['smart_url_regex'] = '/mysite.(?:org|loft)$/i';

