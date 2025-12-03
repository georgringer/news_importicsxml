<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Import of ICS & XML to EXT:news',
    'description' => 'Versatile news import from ICS + XML (local files or remote URLs) including images and category mapping',
    'category' => 'backend',
    'author' => 'Georg Ringer',
    'author_email' => 'mail@ringer.it',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '7.0.0',
    'constraints'
        => [
            'depends' => [
                'core' => '13.4.0-14.99.99',
                'news' => '12.0.0-14.99.99',
            ],
            'conflicts' => [],
            'suggests' => [],
        ],
];
