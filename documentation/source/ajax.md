## Ajax

## Testing and Waiting for Ajax Responses

In automated testing you may need to wait for an ajax request to complete.  Here are some strategy.

### Use `data-data-time`

The idea is to monitor a data attribute timestamp that gets updated by the ajax response, here is the markup model:

    <div class="story t-story" data-data-time="1550610871.67">...</div>

In the page markup for the initial render you must call `loft_core_add_data_refresh`:

    $attributes
      ->addClass('story')
      ->addClass(loft_core_test_class('story'));
    gop3_core_include('ajax');
    loft_core_add_data_refresh($attributes);
    
In your commands response you add this command:
    
    public function getCommands__favorites__post(&$commands, $markup) {
      $commands[] = loft_core_ajax_command_update_data_refresh('.story');
    }

Then, in the test method you do something like this:

    $this->loadPageByUrl('/node/11206');
    $el = $this->getDomElements([
      '.t-story',
      '.t-favorite-add--11206',
    ]);
    $el['.t-favorite-add--11206']->click();
    $this->waitForDataRefresh('.t-story');


