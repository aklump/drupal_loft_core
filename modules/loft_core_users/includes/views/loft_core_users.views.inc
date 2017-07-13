<?php

/**
 * Describes data tables (or the equivalent) to Views.
 *
 * This hook should be placed in MODULENAME.views.inc and it will be
 * auto-loaded. MODULENAME.views.inc must be in the directory specified by the
 * 'path' key returned by MODULENAME_views_api(), or the same directory as the
 * .module file, if 'path' is unspecified.
 *
 * @return
 *   An associative array describing the data structure. Primary key is the
 *   name used internally by Views for the table(s) – usually the actual table
 *   name. The values for the key entries are described in detail below.
 */
function loft_core_users_views_data() {
    // This example describes how to write hook_views_data() for the following
    // table:
    //
    // CREATE TABLE loft_core_users (
    //   uid INT(11) NOT NULL         COMMENT 'Primary key; refers to {users}.uid.',
    //   plain_text_field VARCHAR(32) COMMENT 'Just a plain text field.',
    //   numeric_field INT(11)        COMMENT 'Just a numeric field.',
    //   boolean_field INT(1)         COMMENT 'Just an on/off field.',
    //   timestamp_field INT(8)       COMMENT 'Just a timestamp field.',
    //   PRIMARY KEY(uid)
    // );

    // First, the entry $data['loft_core_users']['table'] describes properties of
    // the actual table – not its content.

    // The 'group' index will be used as a prefix in the UI for any of this
    // table's fields, sort criteria, etc. so it's easy to tell where they came
    // from.
    $data['loft_core_users']['table']['group'] = t('Loft Core Users');

    // Define this as a base table – a table that can be described in itself by
    // views (and not just being brought in as a relationship). In reality this
    // is not very useful for this table, as it isn't really a distinct object of
    // its own, but it makes a good example.
    $data['loft_core_users']['table']['base'] = array(
        'field' => 'uid', // This is the identifier field for the view.
        'title' => t('Loft Core: Users'),
        'help' => t('Example table contains example content and can be related to users.'),
        'weight' => -10,
    );

    // This table references the {users} table. The declaration below creates an
    // 'implicit' relationship to the users table, so that when 'users' is the base
    // table, the fields are automatically available.
    $data['loft_core_users']['table']['join'] = array(
        // Index this array by the table name to which this table refers.
        // 'left_field' is the primary key in the referenced table.
        // 'field' is the foreign key in this table.
        'users' => array(
            'left_field' => 'uid',
            'field' => 'uid',
        ),
    );

    // Next, describe each of the individual fields in this table to Views. This
    // is done by describing $data['loft_core_users']['FIELD_NAME']. This part of
    // the array may then have further entries:
    //   - title: The label for the table field, as presented in Views.
    //   - help: The description text for the table field.
    //   - relationship: A description of any relationship handler for the table
    //     field.
    //   - field: A description of any field handler for the table field.
    //   - sort: A description of any sort handler for the table field.
    //   - filter: A description of any filter handler for the table field.
    //   - argument: A description of any argument handler for the table field.
    //   - area: A description of any handler for adding content to header,
    //     footer or as no result behaviour.
    //
    // The handler descriptions are described with examples below.

    // Node ID table field.
    $data['loft_core_users']['uid'] = array(
        'title' => t('UID'),
        'help' => t('User ID or 0'),
        // Define a relationship to the {users} table, so loft_core_users views can
        // add a relationship to users. If you want to define a relationship the
        // other direction, use hook_views_data_alter(), or use the 'implicit' join
        // method described above.
        'relationship' => array(
            'base' => 'users', // The name of the table to join with.
            'base field' => 'uid', // The name of the field on the joined table.
            // 'field' => 'uid' -- see hook_views_data_alter(); not needed here.
            'handler' => 'views_handler_relationship',
            'label' => t('User'),
            'title' => t('Title shown when adding the relationship'),
            'help' => t('Linked to the users table.'),
        ),
    );

    // Example plain text field.
    $data['loft_core_users']['ip'] = array(
        'title' => t('IP'),
        'help' => t('Last IP address associated with this account.'),
        'field' => array(
            'handler' => 'views_handler_field',
            'click sortable' => TRUE, // This is use by the table display plugin.
        ),
        'sort' => array(
            'handler' => 'views_handler_sort',
        ),
        'filter' => array(
            'handler' => 'views_handler_filter_string',
        ),
        'argument' => array(
            'handler' => 'views_handler_argument_string',
        ),
    );


    // Example plain text field.
    $data['loft_core_users']['domain'] = array(
        'title' => t('Domain'),
        'help' => t('Last associated email domain.'),
        'field' => array(
            'handler' => 'views_handler_field',
            'click sortable' => TRUE, // This is use by the table display plugin.
        ),
        'sort' => array(
            'handler' => 'views_handler_sort',
        ),
        'filter' => array(
            'handler' => 'views_handler_filter_string',
        ),
        'argument' => array(
            'handler' => 'views_handler_argument_string',
        ),
    );

    // Example numeric text field.
    $data['loft_core_users']['status'] = array(
        'title' => t('Status'),
        'help' => t('Augmented status value'),
        'field' => array(
            'handler' => 'views_handler_field_numeric',
            'click sortable' => TRUE,
        ),
        'filter' => array(
            'handler' => 'views_handler_filter_numeric',
        ),
        'sort' => array(
            'handler' => 'views_handler_sort',
        ),
    );

    return $data;
}
