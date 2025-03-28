<?php

use \App\Controller\Massiva\Massiva;
use \App\http\Response;

//ROTA HOME
$obRouter->get('/cidades', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Massiva::getCidades($request));
    }
]);

$obRouter->post('/cidades', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Massiva::setCidades($request));
    }
]);

$obRouter->get('/massiva', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Massiva::getAfetados($request));
    }
]);

$obRouter->get('/massiva/documentar', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Massiva::documentaChats($request));
    }
]);

$obRouter->post('/massiva/atualizar', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Massiva::atualizaChats($request));
    }
]);

$obRouter->get('/massiva/delete', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Massiva::getDeleteAfetado($request));
    }
]);

$obRouter->post('/massiva/delete', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Massiva::setDeleteAfetado($request));
    }
]);