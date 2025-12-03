<?php

return [
    'filter' => [
        '%.*%' => [
            '%(<img.+)(\.png"/>)%' => '$1$2$1after$2',
        ],
    ],
];
