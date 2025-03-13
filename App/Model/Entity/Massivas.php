<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Massivas
{
    public $id;
    public $nome;
    public $codsercli;
    public $cpf_cnpj;
    public $protocolo_sz;
    public $numero;

    public function cadastrar()
    {
        $this->id = (new Database('massivas'))->insert([
            'nome' => $this->nome,
            'codsercli' => $this->codsercli,
            'cpf_cnpj' => $this->cpf_cnpj,
            'protocolo_sz' => $this->protocolo_sz,
            'numero' => $this->numero,
        ]);

        return true;
    }

    public static function getMassivas($where = null, $order = null, $limit = null, $fields = '*', $group = null)
    {
        return (new Database('massivas'))->select($where, $order, $limit, $fields, $group);
    }

    public static function getMassivaByNumber($numero)
    {
        return self::getMassivas('numero =' . $numero)->fetchObject(self::class);
    }
    public function excluir()
    {
        return (new Database('massivas'))->delete('id =' . $this->id);

    }
}