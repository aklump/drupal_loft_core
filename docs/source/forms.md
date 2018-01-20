# Forms API

## Hide elements

See `loft_core_form_hide_elements()`.

## Disable elements

It is nice to be able to keep an element visible, yet disable it.  Making this easy is the goal of `loft_core_form_disable_elements()`.

## Form help

This module defines a new element called 'form_help'. It can also take `#weight` and `#attributes` (not shown).

        <?php
        $form['help'] = [
            '#type'    => 'form_help',

            // Notice an array, where each value is a separate paragraph and will be themed as such. It does not have to be an array, and passing a string is considered a single paragraph.
            '#message' => array(
                t("You are editing the template for all user collections."),
                t("When a user account is created, the values of this node at the time the user account is created will be copied to the users's account as a Sample Collection.  Changes made to the node are not retroactive and only affect the user collections created from that point forward."),
            ),
        ];

## Tabindex


    loft_core_form_tabindex()
