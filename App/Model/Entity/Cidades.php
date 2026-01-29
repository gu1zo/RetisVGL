<?php
namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Cidades
{
    public $id;
    public $nome;
    public $massiva;
    public $id_massiva;

    public static function getCidades($where = null, $order = null, $limit = null, $fields = '*', $group = null, $params = [])
    {
        return (new Database('cidades'))->select($where, $order, $limit, $fields, $group, $params);
    }

    public static function getCidadesByName($name)
    {
        return self::getCidades('nome = :nome', null, null, '*', null, [
            ':nome' => $name
        ])->fetchObject(self::class);
    }

    public static function getCidadesById($id)
    {
        return self::getCidades('id = :id', null, null, '*', null, [
            ':id' => $id
        ])->fetchObject(self::class);
    }

    public function cadastrar()
    {
        $this->id = (new Database('cidades'))->insert([
            'nome' => $this->nome,
            'id_massiva' => $this->id_massiva,
            'massiva' => $this->massiva
        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('cidades'))->update('id = ' . $this->id, [
            'nome' => $this->nome,
            'id_massiva' => $this->id_massiva,
            'massiva' => $this->massiva
        ]);
    }
}