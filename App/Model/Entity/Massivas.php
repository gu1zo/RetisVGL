<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Massivas
{
    public $id;
    public $nome;
    public $codsercli;
    public $protocolo_sz;
    public $numero;
    public $id_massiva;
    public $cpf_cnpj;
    public $avisado;
    public function cadastrar()
    {
        $this->id = (new Database('massivas'))->insert([
            'protocolo_sz' => $this->protocolo_sz,
            'numero' => $this->numero,
            'id_massiva' => $this->id_massiva,
            'nome' => $this->nome,
            'cpf_cnpj' => $this->cpf_cnpj,
            'codsercli' => $this->codsercli,
            'avisado' => 0
        ]);

        return true;
    }

    public static function getMassivas($where = null, $order = null, $limit = null, $fields = '*', $group = null, $params = [])
    {
        return (new Database('massivas'))->select($where, $order, $limit, $fields, $group, $params);
    }

    public static function getMassivaByNumber($numero)
    {
        return self::getMassivas('numero = :numero', null, null, '*', null, [
            'numero' => $numero
        ])->fetchObject(self::class);
    }

    public static function getMassivasById($id)
    {
        return self::getMassivas('id = :id', null, null, '*', null, [
            'id' => $id
        ])->fetchObject(self::class);
    }

    public static function getMassivasByIdMassiva($id_massiva)
    {
        return self::getMassivas('id_massiva = :id_massiva', null, null, '*', null, [
            'id_massiva' => $id_massiva
        ]);
    }

    public function excluir()
    {
        return (new Database('massivas'))->delete('id = ' . $this->id);
    }

    public function atualizar()
    {
        (new Database('massivas'))->update('id = ' . $this->id, [
            'protocolo_sz' => $this->protocolo_sz,
            'numero' => $this->numero,
            'id_massiva' => $this->id_massiva,
            'nome' => $this->nome,
            'cpf_cnpj' => $this->cpf_cnpj,
            'codsercli' => $this->codsercli,
            'avisado' => $this->avisado
        ]);

        return true;
    }
}