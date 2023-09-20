<?php

return [
    'GET' => [
        '' => [\Nskdmitry\Cutlink\Controller::class, 'index'],
        '/{short}' => [\Nskdmitry\Cutlink\Controller::class, 'relocate'],
    ],
    'POST' => [
        '' => [\Nskdmitry\Cutlink\Controller::class, 'post'],
    ]
];