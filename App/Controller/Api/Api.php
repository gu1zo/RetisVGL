<?php

namespace App\Controller\Api;

use App\http\Request;
use WilliamCosta\DatabaseManager\Pagination;
use App\Controller\Evento\Evento;
use App\Model\Entity\Massivas as EntityMassiva;
use App\Model\Entity\Joins as EntityJoins;
use App\Model\Entity\Cidades as EntityCidades;
use App\Utils\DateManipulation;
use DateTime;
use Exception;
use IntlDateFormatter;

class Api
{
    /**
     * Método responsável por retornar os detalhes da API
     * @param Request $request
     * @return array
     */
    public static function getDetails($request)
    {
        return [
            'nome' => 'API - RetisVGL',
            'versao' => 'v1.0.0',
            'autor' => 'Guilherme Recalcatte Vogel',
            'email' => 'guilhermerecalcatte@gmail.com'
        ];
    }

    /**
     * Método responsável por retoranr os detalhes da paginação
     * @param Request $request
     * @param Pagination $obPagination
     * @return array
     */
    protected static function getPagination($request, $obPagination)
    {
        $queryParams = $request->getQueryParams();

        $pages = $obPagination->getPages();

        return [
            'paginaAtual' => isset($queryParams['page']) ? (int) $queryParams['page'] : 1,
            'quantidadePaginas' => !empty($pages) ? count($pages) : 1
        ];
    }

    public static function getMessage()
    {
        date_default_timezone_set('America/Sao_Paulo');

        // Obtém a data e hora atual
        $formatter = new IntlDateFormatter(
            'pt_BR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::SHORT,
            'America/Sao_Paulo',
            IntlDateFormatter::GREGORIAN,
            'EEEE, d \'de\' MMMM \'de\' yyyy HH:mm:ss'
        );

        $dataAtual = new DateTime();
        $dataFormatada = $formatter->format($dataAtual);



        $manutencao = '';
        $falhas = '';


        $resultados = EntityJoins::getEventoByStatus('em execucao" OR e.status = "pendente');
        if ($resultados->rowCount() > 0) {
            while ($row = $resultados->fetchObject(EntityJoins::class)) {
                $dataInicio = DateTime::createFromFormat('d/m/Y H:i', $row->dataInicio);
                $duracao = DateManipulation::getHourDiff($dataInicio, $dataAtual);
                $lastupdate = $duracao;
                $usuario = $row->usuario_nome;
                $info = $row->observacao;

                $obComentario = EntityJoins::getLastInfoById($row->id);
                if ($obComentario instanceof EntityJoins) {
                    $obComentario->data = DateTime::createFromFormat('d/m/Y H:i', $obComentario->data);
                    $lastupdate = DateManipulation::gethourDiff($obComentario->data, $dataAtual);
                    $usuario = $obComentario->usuario_nome;
                    $info = $obComentario->comentario;
                }
                $string = '';
                if ($row->tipo == 'manutencao' || ($row->tipo == 'backbone' && $row->status == 'pendente')) {
                    $string .= "_EVENTO_ *" . $row->protocolo . " " . Evento::getPontosAcessoTable($row->id) . "*\n";
                    $string .= "_duration:_ " . $duracao . "\n";
                    $string .= "_last update:_ " . $lastupdate . " por " . $usuario . "\n";
                    $string .= "_last info:_ " . $info . "\n\n";
                    $manutencao .= $string;
                } else if ($row->tipo == 'evento' || ($row->tipo == 'backbone' && $row->status == 'em execucao')) {
                    $string .= "_EVENTO_ *" . $row->protocolo . " " . Evento::getPontosAcessoTable($row->id) . "*\n";
                    $string .= "_duration:_ " . $duracao . "\n";
                    $string .= "_last update:_ " . $lastupdate . " por " . $usuario . "\n";
                    $string .= "_last info:_ " . $info . "\n\n";
                    $falhas .= $string;
                }
            }
        }

        if ($manutencao == '') {
            $manutencao = "_" . $dataFormatada . "_ \nNenhuma tarefa pendente para o projeto";
        }
        if ($falhas == '') {
            $falhas = "_" . $dataFormatada . "_ \nNenhuma tarefa pendente para o projeto\n";
        }

        $message = "*GESTÃO DE FALHAS GGNET*\n" . $falhas . "\n*MANUTENÇÕES PROGRAMADAS GGNET*\n" . $manutencao;
        return $message;
    }

    public static function massiva($request)
    {
        $queryParams = $request->getQueryParams();

        $cidade = $queryParams['cidade'] ?? '';
        try {
            if ($cidade != '') {
                $obCidade = EntityCidades::getCidadesByName($cidade);

                if (!$obCidade instanceof EntityCidades) {
                    throw new Exception("A cidade informada não existe");
                }
                $massiva = $obCidade->massiva == 1 ? true : false;

                $data = [
                    'massiva' => $massiva
                ];

            } else {
                throw new Exception("Nenhuma cidade informada");
            }
        } catch (Exception $e) {
            $data = [
                'erro' => true,
                'message' => $e->getMessage()
            ];
        }
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    public static function setAfetado($request)
    {
        $postVars = $request->getPostVars();
        try {
            $nome = $postVars['nome'] ?? throw new Exception('Nome não definido');
            $protocolo = $postVars['protocolo'] ?? throw new Exception('Protocolo não definido');
            $numero = $postVars['numero'] ?? throw new Exception('Número não definido');
            $codsercli = $postVars['codsercli'] ?? throw new Exception('Número não definido');
            $id_massiva = $postVars['id_massiva'] == 0 ? null : $postVars['id_massiva'];

            $obMassiva = EntityMassiva::getMassivaByNumber($numero);

            if ($obMassiva instanceof EntityMassiva) {
                throw new Exception("Afetado já cadastrado");
            }

            $obMassiva = new EntityMassiva;

            $obMassiva->nome = $nome;
            $obMassiva->protocolo_sz = $protocolo;
            $obMassiva->numero = $numero;
            $obMassiva->codsercli = $codsercli;
            $obMassiva->id_massiva = $id_massiva;
            $obMassiva->cadastrar();

            $data = [
                'error' => false,
                'message' => "Cadastrado com sucesso"
            ];
        } catch (Exception $e) {
            $data = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}