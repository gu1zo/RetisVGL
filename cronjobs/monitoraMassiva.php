<?php

namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Massivas as EntityMassiva;
use \App\Model\Rest\APIInt6;
use \App\Controller\Massiva\Massiva;
use DateTime;
use DateTimeZone;

$limite = 10;

$results = EntityMassiva::getMassivas('id_massiva is not null', null, $limite, "*", 'id_massiva');
$qtd = EntityMassiva::getMassivas('id_massiva is not null', 'id ASC', $limite, 'COUNT(*) as qtd')->fetchObject()->qtd;
if ($qtd > 0) {
    while ($obMassiva = $results->fetchObject(EntityMassiva::class)) {
        if (APIInt6::verificaMassivaBydId((string) $obMassiva->id_massiva)) {
            echo $obMassiva->id_massiva;
            Massiva::documentaChatsByIdMassiva($obMassiva->id_massiva);
        }
    }
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo "Verificado Massiva - " . $data->format('d/m/Y H:i') . "\n";
} else {
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo "Nenhuma Massiva Ativa - " . $data->format('d/m/Y H:i') . "\n";
}