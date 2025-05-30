<?php
use \App\Controller\Logs\Logs;
use \App\http\Response;

//ROTA HOME
$obRouter->get('/logs', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Logs::getLogs($request));
    }
]);

$obRouter->get('/logs/concluir', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Logs::concluirLog($request));
    }
]);