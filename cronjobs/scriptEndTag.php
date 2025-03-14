<?php
namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Fila as EntityFila;
use \App\Model\Entity\Massivas as EntityMassiva;
use \App\Model\Rest\APIFortics;
use DateTime;
use DateTimeZone;

$data = APIFortics::getAllMessages();

// Inicializar o array para armazenar os dados filtrados
$filteredData = [];
$mensagem = "Olá! a internet já foi restabelecida! Caso precise de algo mais, estamos à disposição. Obrigado pela paciência!";
$token = APIFortics::getToken();
$multiHandle = curl_multi_init();
$curlHandles = [];
// Iterar sobre os dados retornados pela API
foreach ($data as $item) {
    if (
        isset($item["campaign_id"], $item["tagCategory"]) &&
        $item["campaign_id"] === "67d3295c0af8a0cd7606a19f" && // Verificar o campaign_id
        $item["tagCategory"] === "65eb4af28d3d3520f70f1dfc" // Verificar o tagCategory
    ) {

        $obMassiva = EntityMassiva::getMassivaByNumber($item["platform_id"]);

        if ($obMassiva instanceof EntityMassiva) {
            APIFortics::sendMessageAtt($item["platform_id"], $mensagem, $multiHandle, $curlHandles, $token);
            $obFila = new EntityFila;
            $obFila->nome = $obMassiva->nome;
            $obFila->codsercli = $obMassiva->codsercli;
            $obFila->cpf_cnpj = $obMassiva->cpf_cnpj;
            $obFila->protocolo_sz = $obMassiva->protocolo_sz;
            $obFila->numero = $obMassiva->numero;
            $obFila->cadastrar();

            $obMassiva->excluir();
        }
    }
}
APIFortics::executeBatchRequests($multiHandle, $curlHandles);
$data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
echo "Adicionado Chats para fila - " . $data->format('d/m/Y H:i') . "\n";