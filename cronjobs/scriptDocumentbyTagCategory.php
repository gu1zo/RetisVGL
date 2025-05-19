<?php
namespace App\Cronjobs;

use App\Model\Rest\APIFortics;

require __DIR__ . '/../includes/app.php';
function getAllPlatformIds($baseUrl, $authorizationToken)
{
    $page = 1;
    $allPlatformIds = [];

    do {
        // Monta a URL com a página atual
        $url = $baseUrl . '?page=' . $page;

        // Inicializa o cURL
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: $authorizationToken",
            "Content-Type: application/json"
        ]);

        // Executa a requisição
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "Erro na requisição: " . curl_error($ch);
            break;
        }

        curl_close($ch);

        // Decodifica o JSON
        $data = json_decode($response, true);

        // Verifica se a resposta está no formato esperado
        if (!isset($data['data'])) {
            echo "Resposta inesperada:\n$response\n";
            break;
        }

        // Extrai os platform_id
        foreach ($data['data'] as $item) {
            if (isset($item['platform_id'])) {
                $allPlatformIds[] = $item['platform_id'];
            }
        }

        // Verifica se há próxima página
        $hasNext = isset($data['hasNextPage']) && $data['hasNextPage'] === true;
        $page++;
    } while ($hasNext);

    return $allPlatformIds;
}

// 🔧 Configuração da URL da API e token
$baseUrl = 'https://ggnet.sz.chat/api/v4/attendances';
$authorizationToken = 'Bearer' . APIFortics::getToken(); // Substitua pelo seu token real

// 🚀 Executa e imprime os platform_id coletados
$platformIds = getAllPlatformIds($baseUrl, $authorizationToken);

echo "Platform IDs coletados:\n";
foreach ($platformIds as $id) {
    echo "$id\n";
}