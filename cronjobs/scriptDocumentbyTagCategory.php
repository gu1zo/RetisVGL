<?php
namespace App\Cronjobs;

use App\Model\Rest\APIFortics;

require __DIR__ . '/../includes/app.php';

function getAllPlatformIds($baseUrl, $authorizationToken)
{
    $page = 1;
    $allPlatformIds = [];

    do {
        $url = $baseUrl . '&page=' . $page;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: $authorizationToken",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "Erro na requisição: " . curl_error($ch);
            break;
        }

        curl_close($ch);
        $data = json_decode($response, true);

        if (!isset($data['data'])) {
            echo "Resposta inesperada:\n$response\n";
            break;
        }

        foreach ($data['data'] as $item) {
            if (isset($item['platform_id'])) {
                $allPlatformIds[] = $item['platform_id'];
            }
        }

        $hasNext = $data['last_page'] != $page;
        $page++;
    } while ($hasNext);

    return $allPlatformIds;
}

function processBatch(array $batch, string $token, string $mensagem)
{
    $multiHandle = curl_multi_init();
    $curlHandles = [];

    foreach ($batch as $item) {
        APIFortics::sendMessageAtt($item, $mensagem, $multiHandle, $curlHandles, $token, true);
    }

    APIFortics::executeBatchRequests($multiHandle, $curlHandles);
}

// Configuração
$baseUrl = 'https://ggnet.sz.chat/api/v4/attendances?campaign_id=67d3295c0af8a0cd7606a19f';
$authorizationToken = 'Bearer ' . APIFortics::getToken();

$platformIds = getAllPlatformIds($baseUrl, $authorizationToken);

$token = APIFortics::getToken();
$mensagem = "A instabilidade na região foi resolvida e o acesso à internet já foi normalizado. Caso sua conexão ainda não tenha sido restabelecida, sugerimos que reinicie seu roteador. Se o problema persistir, entre em contato com nosso suporte para que possamos auxiliar.\n\nAgradecemos sua paciência e compreensão.";

$batchSize = 20;
$batch = [];

foreach ($platformIds as $id) {
    $batch[] = $id;

    if (count($batch) === $batchSize) {
        processBatch($batch, $token, $mensagem);
        $batch = [];
        sleep(2); // opcional: evitar sobrecarga
    }
}

// Processa o restante, se houver
if (count($batch) > 0) {
    processBatch($batch, $token, $mensagem);
}