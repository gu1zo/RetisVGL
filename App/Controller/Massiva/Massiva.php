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
            case 'documented':
                return Alert::getSuccess('Chats documentados com sucesso!');
            case 'atualizado':
                return Alert::getSuccess('Chats atualizados com sucesso!');
            case 'deleted':
                return Alert::getSuccess('Afetado removido com sucesso!');
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
                'id' => $obMassiva->id,
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
        while ($obMassiva = $results->fetchObject(EntityMassiva::class)) {

            $obFila = new EntityFila;
            $obFila->nome = $obMassiva->nome;
            $obFila->codsercli = $obMassiva->codsercli;
            $obFila->protocolo_sz = $obMassiva->protocolo_sz;
            $obFila->numero = $obMassiva->numero;
            $obFila->cpf_cnpj = $obMassiva->cpf_cnpj;
            $obFila->cadastrar();

            $obMassiva->excluir();
        }
        $request->getRouter()->redirect('/massiva?status=documented');
        exit;
    }


    public static function documentaChatsByIdMassiva($id_massiva)
    {
        $results = EntityMassiva::getMassivasByIdMassiva($id_massiva);
        while ($obMassiva = $results->fetchObject(EntityMassiva::class)) {
            $obFila = new EntityFila;
            $obFila->nome = $obMassiva->nome;
            $obFila->codsercli = $obMassiva->codsercli;
            $obFila->protocolo_sz = $obMassiva->protocolo_sz;
            $obFila->numero = $obMassiva->numero;
            $obFila->cpf_cnpj = $obMassiva->cpf_cnpj;
            $obFila->cadastrar();

            $obMassiva->excluir();
        }
    }

    public static function atualizaChats($request)
    {
        $postVars = $request->getPostVars();
        $files = $_FILES;
        $mensagem = $postVars['mensagem'] ?? '';
        $massivas = $postVars['massivas'] ?? [];
        $todos = isset($postVars['todos']);

        // ===== Configurações de upload =====
        $uploadDir = '/var/www/html/resources/img/tmp/'; // ajuste se o seu docroot for diferente
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imagemCaminho = null;

        // Validar e mover upload se existir
        if (isset($files['imagem']) && $files['imagem']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $files['imagem']['tmp_name'];
            $origName = $files['imagem']['name'];
            $size = $files['imagem']['size'];
            $mime = mime_content_type($tmpName);

            // validações básicas
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5 MB, ajuste se quiser

            if (!in_array($mime, $allowedMimes)) {
                // trate erro conforme seu fluxo (ex: redirect com erro)
                $request->getRouter()->redirect('/massiva?status=erro_tipo_imagem');
                exit;
            }
            if ($size > $maxSize) {
                $request->getRouter()->redirect('/massiva?status=erro_tamanho_imagem');
                exit;
            }

            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $uniqueName = uniqid('img_', true) . '.' . strtolower($ext);
            $destino = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $uniqueName;

            if (!move_uploaded_file($tmpName, $destino)) {
                $request->getRouter()->redirect('/massiva?status=erro_upload');
                exit;
            }

            // opcional: ajustar permissões para leitura pública
            @chmod($destino, 0644);

            $imagemCaminho = $destino;
        }

        // ===== Preparar envio multi-cURL =====
        $multiHandle = curl_multi_init();
        $curlHandles = [];
        $token = APIFortics::getToken();

        foreach ($massivas as $k) {
            $results = EntityMassiva::getMassivasByIdMassiva($k);
            while ($obMassiva = $results->fetchObject(EntityMassiva::class)) {
                $deveEnviar = $todos || $obMassiva->avisado == 0;

                if ($deveEnviar) {
                    APIFortics::sendMessageAtt(
                        $obMassiva->numero,
                        $obMassiva->protocolo_sz,
                        $mensagem,
                        $multiHandle,
                        $curlHandles,
                        $token,
                        $imagemCaminho // pode ser null
                    );

                    $obMassiva->avisado = 1;
                    $obMassiva->atualizar();
                }
            }
        }

        // Executa todas as requisições
        APIFortics::executeBatchRequests($multiHandle, $curlHandles);

        if ($imagemCaminho && file_exists($imagemCaminho)) {
            @unlink($imagemCaminho);
        }

        $request->getRouter()->redirect('/massiva?status=atualizado');
        exit;
    }

    public static function finalizaChats($request)
    {
        $postVars = $request->getPostVars();
        $massivas = $postVars['massivas'];

        foreach ($massivas as $k) {
            self::documentaChatsByIdMassiva($k);
        }

        $request->getRouter()->redirect('/massiva?status=atualizado');
        exit;
    }

    public static function getDeleteAfetado($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];


        $obMassiva = EntityMassiva::getMassivasById($id);

        if (!$obMassiva instanceof EntityMassiva) {
            $request->getRouter()->redirect('/massiva');
            exit;
        }

        $content = View::render('massiva/delete', [
            'nome' => $obMassiva->nome,
        ]);

        //Retorna a página
        return parent::getPage('Excluir Afetado > RetisVGL', $content);
    }
    public static function setDeleteAfetado($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        $obMassiva = EntityMassiva::getMassivasById($id);

        if (!$obMassiva instanceof EntityMassiva) {
            $request->getRouter()->redirect('/massiva');
            exit;
        }
        $obMassiva->excluir();

        $request->getRouter()->redirect('/massiva?status=deleted');
        exit;
    }
}