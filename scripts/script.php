<?php

namespace App\Scripts;
require __DIR__ . '/../includes/app.php';

use App\Model\Entity\Evento;

$csvFile = 'eventos.csv';

if (!file_exists($csvFile) || !is_readable($csvFile)) {
    die("Arquivo CSV não encontrado ou não é legível.");
}

if (($handle = fopen($csvFile, 'r')) !== false) {
    // Função para remover BOM do início da string
    function removeBom($str)
    {
        if (substr($str, 0, 3) === "\xEF\xBB\xBF") {
            $str = substr($str, 3);
        }
        return $str;
    }

    $header = fgetcsv($handle, 1000, ';');

    // Remove BOM do primeiro elemento do cabeçalho, se existir
    if (isset($header[0])) {
        $header[0] = removeBom($header[0]);
    }

    $header = array_map(function ($h) {
        return strtolower(trim($h, " \t\n\r\0\x0B\""));
    }, $header);

    print_r($header);

    $colProtocolo = array_search('protocolo', $header);
    $colDataInicio = array_search('datainicio', $header);
    $colDataFim = array_search('datafim', $header);

    if ($colProtocolo === false || $colDataInicio === false || $colDataFim === false) {
        die("Colunas protocolo, datainicio ou datafim não encontradas no CSV.");
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