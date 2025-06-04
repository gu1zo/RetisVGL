<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class PontoAcesso
{
    public $id;
    public $codigo;
    public $nome;
    public $latitude;
    public $longitude;

    public function cadastrar()
    {
        $this->id = (new Database('pontos_acesso'))->insert([
            'codigo' => $this->codigo,
            'nome' => $this->nome,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('pontos_acesso'))->update('id = ' . $this->id, [
            'codigo' => $this->codigo,
            'nome' => $this->nome,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);
    }


    public static function getPontosAcesso($where = null, $order = null, $limit = null, $fields = '*', $params = [])
    {
        return (new Database('pontos_acesso'))->select($where, $order, $limit, $fields, null, $params);
    }

    public static function getPontoByCode($codigo)
    {
        return (new Database('pontos_acesso'))->select('codigo = :codigo', null, null, '*', null, [
            'codigo' => $codigo
        ])->fetchObject(self::class);
    }

    public static function getPontoByName($nome)
    {
        return (new Database('pontos_acesso'))->select('nome = :nome', null, null, '*', null, [
            'nome' => $nome
        ])->fetchObject(self::class);
    }

    public static function getCodeByName($nome)
    {
        return (new Database('pontos_acesso'))->select('nome = :nome', null, null, 'codigo', null, [
            'nome' => $nome
        ])->fetchObject(self::class);
    }

    public static function getPontoByCodeAndName($codigo, $nome)
    {
        return (new Database('pontos_acesso'))->select('codigo = :codigo AND nome = :nome', null, null, '*', null, [
            'codigo' => $codigo,
            'nome' => $nome
        ])->fetchObject(self::class);
    }

    public static function getCodeAndNameById($id)
    {
        $tabela = 'pontos_acesso pa JOIN pontos_acesso_afetados paa ON pa.codigo = paa.ponto_acesso_codigo';
        return (new Database($tabela))->select('paa.evento_id = :id', null, null, 'pa.nome, pa.codigo', null, [
            'id' => $id
        ]);
    }
}