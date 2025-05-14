<?php

namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\FilaEmail as EntityFilaEmails;
use \App\Controller\Email\Email;
use DateTime;
use DateTimeZone;

$limite = 200;
$data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
$results = EntityFilaEmails::getEmails('status != "enviado"', 'id ASC', $limite);
$qtd = EntityFilaEmails::getEmails('status != "enviado"', 'id ASC', $limite, 'COUNT(*) as qtd')->fetchObject()->qtd;
if ($qtd > 0) {
    while ($obFilaEmails = $results->fetchObject(EntityFilaEmails::class)) {
        $vars = json_decode($obFilaEmails->vars, true);
        $cliente = ['e_mail' => $obFilaEmails->cliente_email, 'nome' => $obFilaEmails->cliente_nome];
        Email::send($obFilaEmails->tipo, $vars, $cliente);

        $obFilaEmails->status = 'enviado';
        $obFilaEmails->enviado_em = $data->format('Y-m-d H:i');
        $obFilaEmails->atualizar();
    }
    // Definir o fuso horário de Brasília
    echo "Lote Enviado - " . $data->format('d/m/Y H:i') . "\n";
} else {
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo "Nenhum Lote Ativo - " . $data->format('d/m/Y H:i') . "\n";
}