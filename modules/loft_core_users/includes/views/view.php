<?php
$view = new view();
$view->name = 'loft_core_users_admin';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'loft_core_users';
$view->human_name = 'loft_core_users_admin';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['title'] = 'Loft Core Users';
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['access']['perm'] = 'loft_core_users:administer';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['pager']['options']['items_per_page'] = '50';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['pager']['options']['id'] = '0';
$handler->display->display_options['pager']['options']['quantity'] = '9';
$handler->display->display_options['style_plugin'] = 'table';
$handler->display->display_options['style_options']['default_row_class'] = FALSE;
$handler->display->display_options['style_options']['columns'] = array(
    'id' => 'id',
    'status' => 'status',
    'uid' => 'uid',
    'name' => 'name',
    'domain' => 'domain',
    'ip' => 'ip',
);
$handler->display->display_options['style_options']['default'] = 'id';
$handler->display->display_options['style_options']['info'] = array(
    'id' => array(
        'sortable' => 1,
        'default_sort_order' => 'desc',
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
    ),
    'status' => array(
        'sortable' => 1,
        'default_sort_order' => 'asc',
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
    ),
    'uid' => array(
        'sortable' => 1,
        'default_sort_order' => 'asc',
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
    ),
    'name' => array(
        'sortable' => 1,
        'default_sort_order' => 'asc',
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
    ),
    'domain' => array(
        'sortable' => 1,
        'default_sort_order' => 'asc',
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
    ),
    'ip' => array(
        'sortable' => 1,
        'default_sort_order' => 'asc',
        'align' => '',
        'separator' => '',
        'empty_column' => 0,
    ),
);
/* Relationship: Loft users: Title shown when adding the relationship */
$handler->display->display_options['relationships']['uid']['id'] = 'uid';
$handler->display->display_options['relationships']['uid']['table'] = 'loft_core_users';
$handler->display->display_options['relationships']['uid']['field'] = 'uid';
$handler->display->display_options['relationships']['uid']['label'] = 'Default label for the relationship';
$handler->display->display_options['relationships']['uid']['required'] = TRUE;
/* Field: Loft users: Record Id */
$handler->display->display_options['fields']['id']['id'] = 'id';
$handler->display->display_options['fields']['id']['table'] = 'loft_core_users';
$handler->display->display_options['fields']['id']['field'] = 'id';
/* Field: Loft users: Status */
$handler->display->display_options['fields']['status']['id'] = 'status';
$handler->display->display_options['fields']['status']['table'] = 'loft_core_users';
$handler->display->display_options['fields']['status']['field'] = 'status';
/* Field: User: Uid */
$handler->display->display_options['fields']['uid']['id'] = 'uid';
$handler->display->display_options['fields']['uid']['table'] = 'users';
$handler->display->display_options['fields']['uid']['field'] = 'uid';
$handler->display->display_options['fields']['uid']['relationship'] = 'uid';
/* Field: User: Name */
$handler->display->display_options['fields']['name']['id'] = 'name';
$handler->display->display_options['fields']['name']['table'] = 'users';
$handler->display->display_options['fields']['name']['field'] = 'name';
$handler->display->display_options['fields']['name']['relationship'] = 'uid';
/* Field: Loft users: Domain */
$handler->display->display_options['fields']['domain']['id'] = 'domain';
$handler->display->display_options['fields']['domain']['table'] = 'loft_core_users';
$handler->display->display_options['fields']['domain']['field'] = 'domain';
/* Field: Loft users: IP */
$handler->display->display_options['fields']['ip']['id'] = 'ip';
$handler->display->display_options['fields']['ip']['table'] = 'loft_core_users';
$handler->display->display_options['fields']['ip']['field'] = 'ip';
/* Filter criterion: Loft users: Status */
$handler->display->display_options['filters']['status']['id'] = 'status';
$handler->display->display_options['filters']['status']['table'] = 'loft_core_users';
$handler->display->display_options['filters']['status']['field'] = 'status';
$handler->display->display_options['filters']['status']['exposed'] = TRUE;
$handler->display->display_options['filters']['status']['expose']['operator_id'] = 'status_op';
$handler->display->display_options['filters']['status']['expose']['label'] = 'Status';
$handler->display->display_options['filters']['status']['expose']['operator'] = 'status_op';
$handler->display->display_options['filters']['status']['expose']['identifier'] = 'status';
$handler->display->display_options['filters']['status']['expose']['remember_roles'] = array(
    2 => '2',
    1 => 0,
    3 => 0,
    5 => 0,
    6 => 0,
    7 => 0,
    8 => 0,
);

/* Display: Page (with Page Title) */
$handler = $view->new_display('page_with_page_title', 'Page (with Page Title)', 'page_with_page_title_1');
$handler->display->display_options['path'] = 'admin/people/loft-core-users';
$handler->display->display_options['menu']['type'] = 'tab';
$handler->display->display_options['menu']['title'] = 'Loft Core Users';
$handler->display->display_options['menu']['weight'] = '0';
$handler->display->display_options['menu']['context'] = 0;
$handler->display->display_options['menu']['context_only_inline'] = 0;
$translatables['loft_core_users_admin'] = array(
    t('Master'),
    t('Loft Core Users'),
    t('more'),
    t('Apply'),
    t('Reset'),
    t('Sort by'),
    t('Asc'),
    t('Desc'),
    t('Items per page'),
    t('- All -'),
    t('Offset'),
    t('« first'),
    t('‹ previous'),
    t('next ›'),
    t('last »'),
    t('Default label for the relationship'),
    t('Record Id'),
    t('.'),
    t(','),
    t('Status'),
    t('Uid'),
    t('Name'),
    t('Domain'),
    t('IP'),
    t('Page (with Page Title)'),
);
