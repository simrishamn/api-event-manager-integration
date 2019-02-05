<?php 



    'key' => 'group_5c599a27e446a',
    'title' => __('Event form', 'event-integration'),
    'fields' => array(
        0 => array(
            'key' => 'field_5c599a30583d3',
            'label' => __('User groups', 'event-integration'),
            'name' => 'user_groups',
            'type' => 'text',
            'instructions' => __('Comma separated list of user group IDs.', 'event-integration'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'mod-event-submit',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
