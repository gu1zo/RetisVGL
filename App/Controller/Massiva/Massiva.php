<?php

namespace App\Controller\Massiva;

use \App\Controller\Pages\Page;
use \App\Utils\View;
use \App\Utils\Alert;
use \App\Model\Entity\Cidades as EntityCidades;
use \App\Model\Entity\Massivas as EntityMassiva;

class Massiva extends Page
{
    public static function getCidades($request)
    {
        $content = View::render('/cidades/table', [
            'status' => self::getStatus($request),
            'itens' => self::getCidadesItem()
        ]);

        return parent::getPage('Cidades Massiva > RetisVGL', $content);
    }

    public static function setCidades($request)
    {
        $postVars = $request->getPostVars();
        $cidades = $postVars['massiva'] ?? [];
        $results = EntityCidades::getCidades();

        while ($obCidades = $results->fetchObject(EntityCidades::class)) {
            $obCidades->massiva = 0;
            if (in_array($obCidades->id, $cidades)) {
                $obCidades->massiva = 1;
            }
            $obCidades->atualizar();
        }
        $request->getRouter()->redirect('/cidades?status=updated');
        exit;
    }

    private static function getCidadesItem()
    {
        $item = '';
        $results = EntityCidades::getCidades();

        while ($obCidade = $results->fetchObject(EntityCidades::class)) {
            $massiva = $obCidade->massiva == 1 ? 'checked' : '';
            $item .= View::render('/cidades/item', [
                'nome' => $obCidade->nome,
                'id' => $obCidade->id,
                'massiva' => $massiva
            ]);
        }
        return $item;
    }

    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'updated':
                return Alert::getSuccess('Cidades atualizadas com sucesso!');
                break;
        }
        return '';
    }

    public static function getAfetados($request)
    {
        $content = View::render('/massiva/table', [
            'status' => self::getStatus($request),
            'itens' => self::getAfetadosItens()
        ]);
        return parent::getPage("Afetados Massiva > RetisVGL", $content);
    }

    private static function getAfetadosItens()
    {
        $itens = '';

        $results = EntityMassiva::getMassivas(null, 'id ASC');
        while ($obMassiva = $results->fetchObject(EntityMassiva::class)) {
            $itens .= View::render('/massiva/item', [
                'nome' => $obMassiva->nome,
                'cpf_cnpj' => $obMassiva->cpf_cnpj,
                'protocolo' => $obMassiva->protocolo_sz,
                'numero' => $obMassiva->numero
            ]);
        }

        return $itens;
    }
}