<?php
return [
    'grabber' => [
        '%.*%' => [
            'test_url' => 'http://www.pcinpact.com/news/85954-air-france-ne-vous-demande-plus-deteindre-vos-appareils-electroniques.htm?utm_source=PCi_RSS_Feed&utm_medium=news&utm_campaign=pcinpact',
            'body' => [
                '//div[contains(@id, "actu_content")]',
            ],
            'strip' => [
            ],
        ]
    ]
];
