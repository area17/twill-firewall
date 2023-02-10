@extends('twill::layouts.form')

@php
    use A17\TwillFirewall\Support\Facades\TwillFirewall;
@endphp

@section('contentFields')
    @formField('input', [
    'type' => TwillFirewall::config('inputs.username.type'),
    'name' => 'username',
    'label' => 'Username',
    'required' => true,
    'disabled' => TwillFirewall::hasDotEnv(),
    ])

    @formField('input', [
    'type' => TwillFirewall::config('inputs.password.type'),
    'name' => 'password',
    'label' => 'Password',
    'required' => true,
    'disabled' => TwillFirewall::hasDotEnv(),
    'password' => true,
    ])

    @formField('checkbox', [
    'name' => 'allow_laravel_login',

    'label' => 'Allow Laravel users to login',

    'disabled' => TwillFirewall::hasDotEnv(),
    ])

    @formField('checkbox', [
    'name' => 'allow_twill_login',

    'label' => "Allow Twill users to login",

    'disabled' => TwillFirewall::hasDotEnv(),
    ])
@stop

@section('fieldsets')
    <a17-fieldset title="Block editor" id="block-editor" :open="true">
        @formField('block_editor', [
            'blocks' => [
                'notes',
            ]
        ])
    </a17-fieldset>

    <a17-fieldset title="Temporary access" id="temporary-access" :open="false">
        @formField('checkbox', [
            'name' => 'temporary_access_enabled',
            'label' => 'Enabled',
        ])

        @formField('repeater', ['type' => 'temporary-access'])
    </a17-fieldset>
@stop
