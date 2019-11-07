<?php
return [
  'mailgun_api_key' => [
    'group_name'  => 'domain',
    'name'        => 'mailgun_api_key',
    'title'       => ts('Mailgun HTTP webhook signing key'),
    'description' => ts('The Mailgun signing key for the sending domain - get it from Mailgun\'s webhooks page for your domain on their website.'),
    'type'        => 'String',
    'add'         => '5.0',
    'html_type'   => 'text',
    'default'     => '',
    'is_domain'   => 1,
    'is_contact'  => 0,
  ]
];
