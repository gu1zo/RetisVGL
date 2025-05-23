<?php
namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Comentarios as EntityComentarios;
use \App\Model\Entity\User as EntityUser;

function getComentarios($id)
{
    //$queryParams = $request->getQueryParams();

    //$id = $queryParams['id'];
    $comentarios = [];
    $num = 1;

    $results = EntityComentarios::getComentariosByEventoId($id);

    while ($obComentarios = $results->fetchObject(EntityComentarios::class)) {
        $obUser = EntityUser::getUserById($obComentarios->id_usuario_criador);
        $comentarios[] = [
            'id' => $obComentarios->id,
            'num' => $num,
            'data' => $obComentarios->data,
            'autor' => $obUser->nome
        ];
        $num++;
    }
    return json_encode($comentarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

getComentarios('15978');