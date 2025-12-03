<?php

return [
    'grabber' => [
        '%.*%' => [
            'test_url' => 'http://www.neustadt-ticker.de/36480/aktuell/nachrichten/buergerbuero-neustadt-ab-heute-wieder-geoeffnet',
            'body' => ['//div[contains(@class,"article")]/div[@class="PostContent" and *[not(contains(@class, "navigation"))]]'],
            'strip' => [
                '//*[@id="wp_rp_first"]',
            ],
        ],
    ],
];
