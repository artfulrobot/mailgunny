<?php
return [
  'mailgun_api_key' => [
    'group_name'  => 'domain',
    'name'        => 'mailgun_api_key',
    'title'       => ts('Mailgun API key'),
    'description' => ts('The Mailgun API key for the sending domain'),
    'type'        => 'String',
    'add'         => '5.8',
    'html_type'   => 'text',
    'default'     => '',
    'is_domain'   => 1,
    'is_contact'  => 0,
  ]
];
