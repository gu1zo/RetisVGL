<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class PontoAcessoAfetado
{
    public $ponto_acesso_codigo;
    public $evento_id;

    public function cadastrar()
    {
        (new Database('pontos_acesso_afetados'))->insert([
            'evento_id' => $this->evento_id,
            'ponto_acesso_codigo' => $this->ponto_acesso_codigo
        ]);

        return true;
    }

    public static function getPontoAcessoAfetadoById($id)
    {
        return self::getPontoAcessoAfetado('evento_id = :id', null, null, '*', ['id' => $id]);
    }

    public static function getPontoAcessoAfetadoByIdAndCode($id, $codigo)
    {
        $where = 'evento_id = :id AND ponto_acesso_codigo = :codigo';
        $params = [
            'id' => $id,
            'codigo' => $codigo
        ];

        return self::getPontoAcessoAfetado($where, null, null, '*', $params)->fetchObject(self::class);
    }

    public static function getPontoAcessoAfetado($where = null, $order = null, $limit = null, $fields = '*', $params = [])
    {
        return (new Database('pontos_acesso_afetados'))->select($where, $order, $limit, $fields, null, $params);
    }

    public static function excluir($evento_id, $ponto_acesso_codigo)
    {
        $where = 'evento_id = :id AND ponto_acesso_codigo = :codigo';
        $params = [
            'id' => $evento_id,
            'codigo' => $ponto_acesso_codigo
        ];

        return (new Database('pontos_acesso_afetados'))->delete($where, $params);
    }
}