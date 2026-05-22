<?php
namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Fila as EntityFila;
use \App\Model\Rest\APIFortics;
use DateTime;
use DateTimeZone;

function documentaChats()
{
    $results = EntityFila::getMassivasMensagem(0);

    // Verifica se há algum resultado
    if (!$results || $results->rowCount() === 0) {
        $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        echo "Nenhum Lote - " . $data->format('d/m/Y H:i') . "\n";
        return false;
    }

    $mensagem = "A instabilidade na região foi resolvida e o acesso à internet já foi normalizado. Caso sua conexão ainda não tenha sido restabelecida, sugerimos que reinicie seu roteador. Se o problema persistir, entre em contato com nosso suporte para que possamos auxiliar.\n\nAgradecemos sua paciência e compreensão.";

    $batchSize = 20;
    $batch = [];
    $mensagensEnviadas = false;

    while ($obFila = $results->fetchObject(EntityFila::class)) {
        $batch[] = $obFila;

        if (count($batch) === $batchSize) {
            processBatch($batch, $mensagem);
            $batch = [];
            $mensagensEnviadas = true;
            sleep(2); // Pausa entre os batches
        }
    }
    if (count($batch) > 0) {
        processBatch($batch, $mensagem);
        $mensagensEnviadas = true;
    }
    return $mensagensEnviadas;
}

function processBatch(array $batch, string $mensagem)
{
    $multiHandle = curl_multi_init();
    $curlHandles = [];
    $token = APIFortics::getToken();
    foreach ($batch as $item) {
        APIFortics::sendMessageAtt($item->numero, $item->protocolo_sz, $mensagem, $multiHandle, $curlHandles, $token, null, true);

        // Marca como enviado após agendar o envio
        $item->enviado = 1;
        $item->atualizar(); // Crie esse método no EntityFila se não existir
    }

    APIFortics::executeBatchRequests($multiHandle, $curlHandles);
}


// Executa o envio e só exibe log se houver envio real
if (documentaChats()) {
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo "Validado Mensagem - " . $data->format('d/m/Y H:i') . "\n";
}