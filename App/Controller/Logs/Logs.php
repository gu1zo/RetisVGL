<?php
namespace App\Controller\Logs;

use \App\Controller\Pages\Page;
use \App\Utils\View;
use \App\Utils\Alert;
use \App\Model\Entity\LogsCallback as EntityLogs;

class Logs extends Page
{
    public static function getLogs($request)
    {
        $content = View::render('/logs/table', [
            'status' => self::getStatus($request),
            'itens' => self::getLogsItens()
        ]);

        return parent::getPage('Logs Telegram > RetisVGL', $content);
    }

    private static function getLogsItens()
    {
        $item = '';
        $results = EntityLogs::getLogsByStatus('novo');

        while ($obLogs = $results->fetchObject(EntityLogs::class)) {
            $item .= View::render('/logs/item', [
                'id' => $obLogs->id,
                'protocolo' => $obLogs->protocolo,
                'protocolo_sz' => $obLogs->protocolo_sz,
                'erro' => $obLogs->error,
            ]);
        }
        return $item;
    }

    public static function concluirLog($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obLogs = EntityLogs::getLogsById($id);
        if (!$obLogs instanceof EntityLogs) {
            $request->getRouter()->redirect('/logs?status=undefined');
            exit;
        }

        $obLogs->status = 'concluido';
        $obLogs->atualizar();
        $request->getRouter()->redirect('/logs?status=updated');
        exit;

    }

    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'updated':
                return Alert::getSuccess('Erro concluído com sucesso!');
            case 'undefined':
                return Alert::getError('Log não encontrado!');
        }
        return '';
    }
}