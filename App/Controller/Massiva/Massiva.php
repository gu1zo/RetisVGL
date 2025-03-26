<?php

namespace App\Controller\Massiva;

use \App\Controller\Pages\Page;
use \App\Utils\View;
use \App\Utils\Alert;
use \App\Model\Entity\Cidades as EntityCidades;
use \App\Model\Entity\Massivas as EntityMassiva;
use \App\Model\Entity\Fila as EntityFila;
use \App\Model\Rest\APIElite;
use \App\Model\Rest\APIFortics;

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
            case 'documented':
                return Alert::getSuccess('Chats documentados com sucesso!');
                break;
            case 'atualizado':
                return Alert::getSuccess('Chats atualizados com sucesso!');
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
            $id_massiva = is_null($obMassiva->id_massiva) ? 0 : $obMassiva->id_massiva;
            $itens .= View::render('/massiva/item', [
                'nome' => $obMassiva->nome,
                'protocolo' => $obMassiva->protocolo_sz,
                'numero' => $obMassiva->numero,
                'id_massiva' => $id_massiva
            ]);
        }

        return $itens;
    }

    public static function documentaChats($request)
    {
        $results = EntityMassiva::getMassivas(null, 'id ASC');
        $multiHandle = curl_multi_init();
        $curlHandles = [];
        $token = APIFortics::getToken();
        $mensagem = "A instabilidade na região foi resolvida e o acesso à internet já foi normalizado. Caso sua conexão ainda não tenha sido restabelecida, sugerimos que reinicie seu roteador. Se o problema persistir, entre em contato com nosso suporte para que possamos auxiliar.\n\nAgradecemos sua paciência e compreensão.";
        while ($obMassiva = $results->fetchObject(EntityMassiva::class)) {
            APIFortics::sendMessageAtt($obMassiva->numero, $mensagem, $multiHandle, $curlHandles, $token);

            $obFila = new EntityFila;
            $obFila->nome = $obMassiva->nome;
            $obFila->codsercli = $obMassiva->codsercli;
            $obFila->protocolo_sz = $obMassiva->protocolo_sz;
            $obFila->numero = $obMassiva->numero;
            $obFila->cadastrar();

            $obMassiva->excluir();
        }
        APIFortics::executeBatchRequests($multiHandle, $curlHandles);
        $request->getRouter()->redirect('/massiva?status=documented');
        exit;
    }


    public static function documentaChatsByIdMassiva($id_massiva)
    {
        $results = EntityMassiva::getMassivasByIdMassiva($id_massiva);
        $multiHandle = curl_multi_init();
        $curlHandles = [];
        $token = APIFortics::getToken();
        $mensagem = "A instabilidade na região foi resolvida e o acesso à internet já foi normalizado. Caso sua conexão ainda não tenha sido restabelecida, sugerimos que reinicie seu roteador. Se o problema persistir, entre em contato com nosso suporte para que possamos auxiliar.\n\nAgradecemos sua paciência e compreensão.";
        while ($obMassiva = $results->fetchObject(EntityMassiva::class)) {
            APIFortics::sendMessageAtt($obMassiva->numero, $mensagem, $multiHandle, $curlHandles, $token);

            $obFila = new EntityFila;
            $obFila->nome = $obMassiva->nome;
            $obFila->codsercli = $obMassiva->codsercli;
            $obFila->protocolo_sz = $obMassiva->protocolo_sz;
            $obFila->numero = $obMassiva->numero;
            $obFila->cadastrar();

            $obMassiva->excluir();
        }
        APIFortics::executeBatchRequests($multiHandle, $curlHandles);
    }

    public static function atualizaChats($request)
    {
        $postVars = $request->getPostVars();
        $mensagem = $postVars['mensagem'];

        // Inicializa Multi cURL
        $multiHandle = curl_multi_init();
        $curlHandles = [];

        $results = EntityMassiva::getMassivas(null, 'id ASC');
        $token = APIFortics::getToken();

        while ($obMassiva = $results->fetchObject(EntityMassiva::class)) {
            APIFortics::sendMessageAtt($obMassiva->numero, $mensagem, $multiHandle, $curlHandles, $token);
        }

        // Executa todas as requisições ao mesmo tempo
        APIFortics::executeBatchRequests($multiHandle, $curlHandles);

        $request->getRouter()->redirect('/massiva?status=atualizado');
        exit;
    }


}