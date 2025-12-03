<?php

return [
    'grabber' => [
        '%.*%' => [
            'test_url' => 'http://www.npr.org/blogs/thesalt/2013/09/17/223345977/auto-brewery-syndrome-apparently-you-can-make-beer-in-your-gut',
            'body' => [
                '//div[@id="storytext"]',
            ],
            'strip' => [
                '//*[@class="bucket img"]',
                '//*[@class="creditwrap"]',
                '//*[@class="captionwrap"]',
                '//*[contains(@class, "enlargebtn")]',
            ],
        ],
    ],
];
