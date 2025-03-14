<?php

namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Fila as EntityFila;
use \App\Model\Rest\APIElite;
use \App\Model\Rest\APIFortics;
use DateTime;
use DateTimeZone;

$data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

$limite = 50;

$results = EntityFila::getMassivas(null, 'id ASC', $limite);
$qtd = EntityFila::getMassivas(null, 'id ASC', $limite, 'COUNT(*) as qtd')->fetchObject()->qtd;

if ($qtd > 0) {
    while ($obMassiva = $results->fetchObject(EntityFila::class)) {
        $codoco = APIElite::abreAtendimento($obMassiva->codsercli, $obMassiva->protocolo_sz, $obMassiva->nome, $obMassiva->numero);
        APIElite::fechaAtendimento($codoco);
        APIFortics::sendMessage($obMassiva->numero);
        APIFortics::closeChat($obMassiva->protocolo_sz);

        $obMassiva->excluir();
    }
    // Definir o fuso horário de Brasília
    echo "Lote documentado - " . $data->format('d/m/Y H:i') . "\n";
} else {
    echo "Nenhum Lote Ativo - " . $data->format('d/m/Y H:i') . "\n";
}