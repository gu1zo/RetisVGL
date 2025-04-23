<?php

namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Fila as EntityFila;
use \App\Model\Rest\APIElite;
use \App\Model\Rest\APIFortics;
use DateTime;
use DateTimeZone;

$limite = 50;

$results = EntityFila::getMassivas(null, 'id ASC', $limite);
$qtd = EntityFila::getMassivas(null, 'id ASC', $limite, 'COUNT(*) as qtd')->fetchObject()->qtd;
if ($qtd > 0) {
    while ($obFila = $results->fetchObject(EntityFila::class)) {
        $cpf_cnpj_limpo = preg_replace('/[\.\-\/]/', '', $obFila->cpf_cnpj);
        $codocop = "EXMM1DP8T2";
        $codocop = strlen($cpf_cnpj_limpo) === 11 ? "EXMM1DP8T2" : "EXMM1F5GL0";

        $codoco = APIElite::abreAtendimento($obFila->codsercli, $obFila->protocolo_sz, $obFila->nome, $obFila->numero, $codocop);
        APIElite::fechaAtendimento($codoco, $codocop);
        APIFortics::closeChat($obFila->protocolo_sz);

        $obFila->excluir();
    }
    // Definir o fuso horário de Brasília
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo "Lote documentado - " . $data->format('d/m/Y H:i') . "\n";
} else {
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo "Nenhum Lote Ativo - " . $data->format('d/m/Y H:i') . "\n";
}