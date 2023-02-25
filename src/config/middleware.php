<?php

return [
    'automatic' => true, // Do it yourself to optimize the middleware stack for speed

    'method' => 'append', // 'prepend' (faster if you don't need session login) or 'append'

    'groups' => ['web'],

    'class' => \A17\TwillFirewall\Http\Middleware::class,
];
