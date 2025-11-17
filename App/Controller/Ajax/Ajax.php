<?php
namespace App\Controller\Ajax;

use App\Model\Entity\PontoAcesso as EntityPontoAcesso;
use App\Model\Entity\Massivas as EntityMassivas;
use App\Model\Entity\Comentarios as EntityComentarios;
use App\Model\Entity\Evento as EntityEvento;
use App\Model\Entity\Alteracoes as EntityAlteracoes;
use App\Model\Entity\User as EntityUser;
use App\Utils\StringManipulation;
use App\Session\Login\Login;
use App\Controller\Evento\Evento;
use App\Controller\Api\EvolutionAPI;
use WilliamCosta\DatabaseManager\Pagination;
use DateTime;


class Ajax
{
    /**
     * Método responsável por retornar os dados para o select2 de pontos de Acesso
     * @param  $request
     * @return 
     */
    public static function getPontosAcesso($request)
    {
        $results = [];

        // Obter os parâmetros da query
        $queryParams = $request->getQueryParams();

        $paginaAtual = $queryParams['page'] ?? 1;
        $search = $queryParams['search'] ?? '';

        // Montar o filtro de busca
        $where = null;
        if (!empty($search)) {
            $where = 'nome LIKE "%' . addslashes($search) . '%"';
        }

        // Obter o total de registros com o filtro
        $quantidadetotal = EntityPontoAcesso::getPontosAcesso($where, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        // Configuração da paginação
        $obPagination = new Pagination($quantidadetotal, $paginaAtual, 100);

        // Buscar os registros filtrados
        $res = EntityPontoAcesso::getPontosAcesso($where, 'nome ASC', $obPagination->getLimit());

        // Construir a lista de resultados
        while ($obPontoAcesso = $res->fetchObject(EntityPontoAcesso::class)) {
            $results[] = [
                'id' => $obPontoAcesso->codigo,
                'text' => $obPontoAcesso->nome
            ];
        }

        // Verificar se há mais páginas
        $hasMore = $paginaAtual * 15 < $quantidadetotal;
        // Estrutura JSON com resultados e paginação
        $response = [
            'results' => $results,
            'pagination' => [
                'more' => $hasMore
            ]
        ];


        // Retornar JSON
        return json_encode($response);
    }

    public static function getEvents($request)
    {
        // Obter os parâmetros da query
        $queryParams = $request->getQueryParams();

        $status = $queryParams['status'] ?? null;
        $start = isset($queryParams['start']) ? (int) $queryParams['start'] : 0;
        $length = isset($queryParams['length']) ? (int) $queryParams['length'] : 10;
        $draw = isset($queryParams['draw']) ? (int) $queryParams['draw'] : 1;
        $search = $queryParams['search'] ?? '';

        /**
         * ------------------------------
         *  FILTRO (status + busca)
         * ------------------------------
         */
        $whereParts = [];

        // Filtro de status (exceto 'todos')
        if (!empty($status) && $status !== 'todos') {
            $whereParts[] = 'status = "' . addslashes($status) . '"';
        }

        // Filtro de busca
        if (!empty($search)) {
            $whereParts[] = 'protocolo LIKE "%' . addslashes($search) . '%"';
        }

        $where = $whereParts ? implode(' AND ', $whereParts) : null;

        /**
         * ------------------------------
         *  PAGINAÇÃO REAL
         * ------------------------------
         * O DataTables envia:
         *   start = offset
         *   length = quantidade por página
         * Precisamos transformar OFFSET → NUMERO DA PAGINA
         */
        $page = ($length > 0) ? (int) floor($start / $length) + 1 : 1;

        /**
         * ------------------------------
         *  TOTAL DE REGISTROS FILTRADOS
         * ------------------------------
         */
        $quantidadetotal = EntityEvento::getEvento($where, null, null, 'COUNT(*) as qtd')
            ->fetchObject()
            ->qtd;

        /**
         * ------------------------------
         *  BUSCAR OS ITENS DA PÁGINA
         * ------------------------------
         */
        $pagination = new Pagination($quantidadetotal, $page, $length);

        $res = EntityEvento::getEvento(
            $where,
            'protocolo ASC',
            $pagination->getLimit()
        );

        /**
         * ------------------------------
         *  MONTAR RESULTADOS
         * ------------------------------
         */
        $results = [];

        while ($obEvento = $res->fetchObject(EntityEvento::class)) {
            $results[] = [
                'status' => str_replace(' ', '-', $obEvento->status),
                'protocolo' => $obEvento->protocolo,
                'tipo' => (new StringManipulation)->formatarTipo($obEvento->tipo),
                'horario-inicial' => $obEvento->dataInicio,
                'pontos-acesso' => Evento::getPontosAcessoTable($obEvento->id),
                'regional' => $obEvento->regional,
                'observacao' => $obEvento->observacao,
                'email' => $obEvento->email ? '✔' : '❌',
                'id' => $obEvento->id
            ];
        }

        /**
         * ------------------------------
         *  MONTAR RESPOSTA DO DATATABLES
         * ------------------------------
         */
        $response = [
            'draw' => $draw,
            'recordsTotal' => $quantidadetotal,
            'recordsFiltered' => $quantidadetotal,
            'data' => $results
        ];

        return json_encode($response);
    }


    /**
     * Método responsável por retornar os pontos de acesso selecionados de evento x
     * @param  $request
     * @return bool|string
     */
    public static function getPontosAcessoEdit($request)
    {
        $response = [];
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obPontoAcesso = new EntityPontoAcesso;
        $results = $obPontoAcesso->getCodeAndNameById($id);

        while ($row = $results->fetchObject(EntityPontoAcesso::class)) {
            $response[] = [
                'id' => $row->codigo,
                'text' => $row->nome
            ];
        }
        // Retornar JSON
        return json_encode($response);
    }

    public static function getComentarios($request)
    {
        $queryParams = $request->getQueryParams();

        $id = $queryParams['id'];
        $comentarios = [];
        $num = 1;

        $results = EntityComentarios::getComentariosByEventoId($id);

        while ($obComentarios = $results->fetchObject(EntityComentarios::class)) {
            $obUser = EntityUser::getUserById($obComentarios->id_usuario_criador);
            $comentarios[] = [
                'id' => $obComentarios->id,
                'num' => $num,
                'data' => $obComentarios->data,
                'autor' => $obUser->nome
            ];
            $num++;
        }
        return json_encode($comentarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    public static function setAlteracao($id, $alteracao)
    {
        $data = new DateTime('America/Sao_Paulo');
        $obAlteracao = new EntityAlteracoes;
        $obAlteracao->evento_id = $id;
        $obAlteracao->alteracao = $alteracao;
        $obAlteracao->data = $data->format('Y-m-d H:i');
        $obAlteracao->id_usuario_criador = Login::getId();
        $obAlteracao->cadastrar();
    }
    public static function setComentarios($request)
    {
        $queryParams = $request->getQueryParams();
        $postVars = $request->getPostVars();

        $id = $queryParams['id'];
        $comentario = $postVars['comentario'] ?? '';
        $id_usuario_criador = Login::getId();
        $data = (new DateTime('America/Sao_Paulo'))->format('Y-m-d H:i');

        $obComentario = new EntityComentarios;

        $obComentario->evento_id = $id;
        $obComentario->comentario = $comentario;
        $obComentario->data = $data;
        $obComentario->id_usuario_criador = $id_usuario_criador;

        $obComentario->cadastrar();
        self::setAlteracao($id, "Adicionado Comentário");
        EvolutionAPI::sendMessage(Evento::getIndividualMessage($id, 'atualizar'));
        return true;
    }
    public static function getComentario($request)
    {
        $queryParams = $request->getQueryParams();

        $id = $queryParams['id'];

        $obComentario = EntityComentarios::getComentarioById($id);
        $response = ['comentario' => nl2br($obComentario->comentario)];
        return json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    }

    public static function getAlteracoes($request)
    {
        $queryParams = $request->getQueryParams();

        $id = $queryParams['id'];
        $alteracoes = [];

        $results = EntityAlteracoes::getAlteracoesByEventoId($id);

        while ($obAlteracoes = $results->fetchObject(EntityAlteracoes::class)) {
            $obUser = EntityUser::getUserById($obAlteracoes->id_usuario_criador);
            $alteracoes[] = [
                'id' => $obAlteracoes->id,
                'alteracao' => $obAlteracoes->alteracao,
                'data' => $obAlteracoes->data,
                'autor' => $obUser->nome
            ];
        }
        return json_encode($alteracoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    public static function getMassivas($request)
    {
        $results = [];

        // Obter os parâmetros da query
        $queryParams = $request->getQueryParams();

        $paginaAtual = $queryParams['page'] ?? 1;
        $search = $queryParams['search'] ?? '';

        // Montar o filtro de busca
        $where = null;
        if (!empty($search)) {
            $where = 'id_massiva LIKE "%' . addslashes($search) . '%"';
        }

        // Obter o total de registros com o filtro
        $quantidadetotal = EntityMassivas::getMassivas($where, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        // Configuração da paginação
        $obPagination = new Pagination($quantidadetotal, $paginaAtual, 15);

        // Buscar os registros filtrados
        $res = EntityMassivas::getMassivas($where, 'nome ASC', $obPagination->getLimit(), '*', 'id_massiva');

        // Construir a lista de resultados
        while ($obMassiva = $res->fetchObject(EntityMassivas::class)) {
            $results[] = [
                'id' => $obMassiva->id_massiva,
                'text' => $obMassiva->id_massiva
            ];
        }

        // Verificar se há mais páginas
        $hasMore = $paginaAtual * 15 < $quantidadetotal;
        // Estrutura JSON com resultados e paginação
        $response = [
            'results' => $results,
            'pagination' => [
                'more' => $hasMore
            ]
        ];


        // Retornar JSON
        return json_encode($response);
    }
}