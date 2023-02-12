@extends('twill::layouts.form', ['contentFieldsetLabel' => 'Configuration'])

@php
    use A17\TwillFirewall\Support\Facades\TwillFirewall;
@endphp

@section('contentFields')
    @formField('radios', [
        'name' => 'strategy',
        'label' => 'Strategy',
        'default' => 'allow',
        'options' => [
            [
                'value' => 'allow',
                'label' => 'Allow'
            ],
            [
                'value' => 'block',
                'label' => 'Block'
            ]
        ]
    ])

    @component('twill::partials.form.utils._connected_fields', [
        'fieldName' => 'strategy',
        'fieldValues' => 'allow',
    ])
        @formField('input', [
            'type' => TwillFirewall::config('inputs.allow.type'),
            'rows' => TwillFirewall::config('inputs.allow.rows'),
            'name' => 'allow',
            'label' => 'Allow only these addresses',
            'required' => false,
            'disabled' => TwillFirewall::hasDotEnv(),
            'note' => 'One per line, IP or CIDR, IPv4 and IPv6',
        ])
    @endcomponent

    @component('twill::partials.form.utils._connected_fields', [
        'fieldName' => 'strategy',
        'fieldValues' => 'block',
    ])
        @formField('input', [
            'type' => TwillFirewall::config('inputs.block.type'),
            'rows' => TwillFirewall::config('inputs.allow.rows'),
            'name' => 'block',
            'label' => 'Block these addresses',
            'required' => false,
            'disabled' => TwillFirewall::hasDotEnv(),
            'note' => 'One per line, IP or CIDR, IPv4 and IPv6',
        ])

        @formField('checkbox', [
            'name' => 'block_attacks',

            'label' => 'Block attacks',

            'disabled' => TwillFirewall::hasDotEnv(),
        ])

        @component('twill::partials.form.utils._connected_fields', [
            'fieldName' => 'block_attacks',
            'fieldValues' => true,
        ])
            @formField('checkbox', [
                'name' => 'add_blocked_to_list',

                'label' => 'Automatically add blocked IP addresses to block list',

                'disabled' => TwillFirewall::hasDotEnv(),
            ])

            @formField('input', [
                'name' => 'max_requests_per_minute',
                'label' => 'Max requests per minute per IP address',
                'required' => false,
                'disabled' => TwillFirewall::hasDotEnv(),
            ])
        @endcomponent
    @endcomponent

    @formField('input', [
        'name' => 'redirect_to',
        'label' => 'Redirect to this URL instead of blocking',
        'required' => false,
        'disabled' => TwillFirewall::hasDotEnv(),
        'note' => 'Start with / for internal addresses',
    ])

    @component('twill::partials.form.utils._columns')
        @slot('left')
            @formField('checkbox', [
                'name' => 'allow_laravel_login',

                'label' => 'Logged in Laravel users can pass the firewall',

                'disabled' => TwillFirewall::hasDotEnv(),
            ])
        @endslot
        @slot('right')
            @formField('checkbox', [
                'name' => 'allow_twill_login',

                'label' => 'Logged in Twill users can pass the firewall',

                'disabled' => TwillFirewall::hasDotEnv(),
            ])
        @endslot
    @endcomponent
@stop
