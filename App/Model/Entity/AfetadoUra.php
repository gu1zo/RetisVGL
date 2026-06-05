<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class AfetadoUra
{
    public $id;
    public $numero;
    public $cpf_cnpj;
    public $nome;
    public $data;

    public function cadastrar()
    {
        $this->id = (new Database('afetados_ura'))->insert([
            'numero' => $this->numero,
            'cpf_cnpj' => $this->cpf_cnpj,
            'nome' => $this->nome,
            'data' => $this->data
        ]);

        return true;
    }

    public static function getAfetadosUra($where = null, $order = null, $limit = null, $fields = '*', $group = null, $params = [])
    {
        return (new Database('afetados_ura'))->select($where, $order, $limit, $fields, $group, $params);
    }
}