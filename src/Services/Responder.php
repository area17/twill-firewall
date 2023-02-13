<?php

namespace A17\TwillFirewall\Services;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class Responder
{
    public function respond(array $data = []): mixed
    {
        if ($data['code'] === 200) {
            return null;
        }

        if ($data['should_abort']) {
            abort($data['code'], $data['message']);
        }

        if ($page = $data['redirect_to']) {
            return Redirect::to($page);
        }

        if ($view = $data['view']) {
            return Response::view($view, $data);
        }

        return Response::make($data['message'], $data['code']);
    }
}
