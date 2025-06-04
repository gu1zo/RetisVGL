<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Fila
{
    public $id;
    public $nome;
    public $codsercli;
    public $protocolo_sz;
    public $numero;
    public $cpf_cnpj;

    public function cadastrar()
    {
        $this->id = (new Database('fila'))->insert([
            'nome' => $this->nome,
            'codsercli' => $this->codsercli,
            'protocolo_sz' => $this->protocolo_sz,
            'cpf_cnpj' => $this->cpf_cnpj,
            'numero' => $this->numero,
        ]);

        return true;
    }

    public static function getMassivas($where = null, $order = null, $limit = null, $fields = '*', $group = null, $params = [])
    {
        return (new Database('fila'))->select($where, $order, $limit, $fields, $group, $params);
    }

    public static function getMassivaByNumber($numero)
    {
        return self::getMassivas('numero = :numero', null, null, '*', null, [
            ':numero' => $numero
        ])->fetchObject(self::class);
    }

    public function excluir()
    {
        return (new Database('fila'))->delete('id =' . $this->id);

    }
}