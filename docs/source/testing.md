# Testing

Test mode is enabled by default when `DRUPAL_ENV` does not equal 'prod'.  This means that test classes will be appened to elements whenever the function `loft_core_test_class()` is used.

When not in test mode, test classes will not be added.

## Testing on Prod

In order to test against a production environment you need to enable test mode using an endpoint.  You must set up the endpoint with an access key in your settings.php like this:

    $conf['loft_core_testing_key'] = '{some obscure public key that will appear in your url}';

When you visit the url endpoint, include the testing key like so:

    /loft-core/testing/enable/{testing key}
    
This will enable the test mode for a short duration and cause test classes to appear on production, for your IP only.
