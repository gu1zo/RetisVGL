<?php

namespace App\Scripts;
require __DIR__ . '/../includes/app.php';
// ajuste o autoload conforme seu projeto

use App\Model\Entity\Evento;

// Caminho do arquivo CSV
$csvFile = 'eventos.csv';

// Abre o arquivo CSV
if (!file_exists($csvFile) || !is_readable($csvFile)) {
    die("Arquivo CSV não encontrado ou não é legível.");
}

if (($handle = fopen($csvFile, 'r')) !== false) {

    // Lê o cabeçalho para saber os índices das colunas
    $header = fgetcsv($handle, 1000, ',');

    // Mapeia os índices das colunas que queremos
    $colProtocolo = array_search('protocolo', $header);
    $colDataInicio = array_search('dataInicio', $header);
    $colDataFim = array_search('dataFim', $header);

    if ($colProtocolo === false || $colDataInicio === false || $colDataFim === false) {
        die("Colunas protocolo, dataInicio ou dataFim não encontradas no CSV.");
    }

    while (($row = fgetcsv($handle, 1000, ',')) !== false) {

        $protocolo = trim($row[$colProtocolo]);
        $dataInicio = trim($row[$colDataInicio]);
        $dataFim = trim($row[$colDataFim]);

        // Busca evento pelo protocolo
        $evento = Evento::getEventoByProtocol($protocolo);

        if ($evento) {
            // Atualiza as datas no objeto
            $evento->dataInicio = $dataInicio;
            $evento->dataFim = $dataFim;

            // Chama o método atualizar()
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