<?php

return [
    'grabber' => [
        '%.*%' => [
            'test_url' => 'http://www.numerama.com/magazine/26857-bientot-des-robots-dans-les-cuisines-de-mcdo.html',
            'body' => [
                '//div[@class="col_left"]//div[@class="content"]',
            ],
            'strip' => [
                '//div[@class="news_social"]',
                '//div[@id="newssuiv"]',
            ],
        ],
    ],
];
