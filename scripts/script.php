<?php

namespace App\Scripts;
require __DIR__ . '/../includes/app.php';

use App\Model\Entity\Evento;

$csvFile = 'eventos.csv';

if (!file_exists($csvFile) || !is_readable($csvFile)) {
    die("Arquivo CSV não encontrado ou não é legível.");
}

if (($handle = fopen($csvFile, 'r')) !== false) {

    $header = fgetcsv($handle, 1000, ';');
    $header = array_map(function ($h) {
        return strtolower(trim($h));
    }, $header);

    $colProtocolo = array_search('protocolo', $header);
    $colDataInicio = array_search('datainicio', $header);
    $colDataFim = array_search('datafim', $header);

    if ($colProtocolo === false || $colDataInicio === false || $colDataFim === false) {
        die("Colunas protocolo, dataInicio ou dataFim não encontradas no CSV.");
    }

    while (($row = fgetcsv($handle, 1000, ';')) !== false) {

        $protocolo = trim($row[$colProtocolo]);
        $dataInicio = trim($row[$colDataInicio]);
        $dataFim = trim($row[$colDataFim]);

        $evento = Evento::getEventoByProtocol($protocolo);

        if ($evento) {
            if (!empty($dataInicio)) {
                $evento->dataInicio = $dataInicio;
            }
            if (!empty($dataFim)) {
                $evento->dataFim = $dataFim;
            }
            $evento->atualizar();
            echo "Evento com protocolo {$protocolo} atualizado com sucesso.\n";
        } else {
            echo "Evento com protocolo {$protocolo} não encontrado. Ignorado.\n";
        }
    }

    fclose($handle);
} else {
    die("Não foi possível abrir o arquivo CSV.");
}