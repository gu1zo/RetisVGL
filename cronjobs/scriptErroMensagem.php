<?php
namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Fila as EntityFila;
use \App\Model\Rest\APIFortics;
use DateTime;
use DateTimeZone;
use Dom\Entity;

function documentaChats()
{
    $results = EntityFila::getMassivas(null, 'id ASC');
    $token = APIFortics::getToken();
    $mensagem = "A instabilidade na região foi resolvida e o acesso à internet já foi normalizado. Caso sua conexão ainda não tenha sido restabelecida, sugerimos que reinicie seu roteador. Se o problema persistir, entre em contato com nosso suporte para que possamos auxiliar.\n\nAgradecemos sua paciência e compreensão.";

    $batchSize = 20;
    $batch = [];
    $total = 0;

    while ($obMassiva = $results->fetchObject(EntityFila::class)) {
        $batch[] = $obMassiva;
        $total++;

        if (count($batch) === $batchSize) {
            processBatch($batch, $token, $mensagem);
            $batch = [];
            sleep(2); // Pausa entre os batches
        }
    }

    // Processa o restante, se sobrar
    if (count($batch) > 0) {
        processBatch($batch, $token, $mensagem);
    }
}

function processBatch(array $batch, string $token, string $mensagem)
{
    $multiHandle = curl_multi_init();
    $curlHandles = [];

    foreach ($batch as $item) {
        APIFortics::sendMessageAtt($item->numero, $mensagem, $multiHandle, $curlHandles, $token, true);
    }

    APIFortics::executeBatchRequests($multiHandle, $curlHandles);
}

documentaChats();