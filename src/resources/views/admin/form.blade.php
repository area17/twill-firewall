@extends('twill::layouts.form')

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
            'label' => 'Allow only this address list',
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
            'label' => 'Block all this address list',
            'required' => false,
            'disabled' => TwillFirewall::hasDotEnv(),
            'note' => 'One per line, IP or CIDR, IPv4 and IPv6',
        ])
    @endcomponent

    @formField('input', [
        'name' => 'redirect_to',
        'label' => 'Redirect to this URL instead of blocking',
        'required' => false,
        'disabled' => TwillFirewall::hasDotEnv(),
        'note' => 'Start with / for internal addresses',
    ])

    @formField('checkbox', [
        'name' => 'allow_laravel_login',

        'label' => 'Logged in Laravel users can pass the firewall',

        'disabled' => TwillFirewall::hasDotEnv(),
    ])

    @formField('checkbox', [
        'name' => 'allow_twill_login',

        'label' => 'Logged in Twill users can pass the firewall',

        'disabled' => TwillFirewall::hasDotEnv(),
    ])
@stop
