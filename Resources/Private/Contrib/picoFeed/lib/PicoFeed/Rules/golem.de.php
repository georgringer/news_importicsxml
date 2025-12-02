<?php

return [
    'grabber' => [
        '%.*%' => [
            'test_url' => 'http://www.golem.de/news/breko-telekom-verzoegert-gezielt-den-vectoring-ausbau-1311-102974.html',
            'body' => [
                '//header[@class="cluster-header"]',
                '//div[@class="formatted"]',
            ],
        ],
    ],
];
