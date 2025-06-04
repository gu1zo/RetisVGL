<?php
namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Comentarios
{
    public $id;
    public $evento_id;
    public $comentario;
    public $data;
    public $id_usuario_criador;

    public function cadastrar()
    {
        $this->id = (new Database('comentarios'))->insert([
            'evento_id' => $this->evento_id,
            'comentario' => $this->comentario,
            'data' => $this->data,
            'id_usuario_criador' => $this->id_usuario_criador,
        ]);

        return true;
    }

    public static function getComentarios($where = null, $order = null, $limit = null, $fields = '*', $group = null, $params = [])
    {
        return (new Database('comentarios'))->select($where, $order, $limit, $fields, $group, $params);

    }

    public static function getComentariosByEventoId($id)
    {
        return self::getComentarios('evento_id = :evento_id', null, null, '*', null, [
            ':evento_id' => $id
        ]);
    }

    public static function getComentarioById($id)
    {
        return self::getComentarios('id = :id', null, null, '*', null, [
            ':id' => $id
        ])->fetchObject(self::class);
    }

    public function excluir()
    {
        return (new Database('comentarios'))->delete('id = ' . $this->id);
    }
}