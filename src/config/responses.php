<?php

return [
    'allow' => [
        'code' => 403, // 200 = log && notify, but keep pages rendering

        'message' => null,

        'view' => null,

        'redirect_to' => null,

        'should_abort' => false, // return abort() instead of Response::make() - disabled by default
    ],

    'block' => [
        'code' => 403, // 200 = log && notify, but keep pages rendering

        'message' => null,

        'view' => null,

        'redirect_to' => null,

        'should_abort' => false, // return abort() instead of Response::make() - disabled by default
    ],
];
