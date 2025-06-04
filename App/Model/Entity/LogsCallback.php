<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class LogsCallback
{
    public $id;
    public $error;
    public $protocolo;
    public $protocolo_sz;
    public $status;
    public $data;

    public function cadastrar()
    {
        $this->id = (new Database('logs_callback'))->insert([
            'protocolo_sz' => $this->protocolo_sz,
            'error' => $this->error,
            'protocolo' => $this->protocolo,
            'data' => $this->data
        ]);

        return true;
    }

    public static function getLogs($where = null, $order = null, $limit = null, $fields = '*', $group = null, $params = [])
    {
        return (new Database('logs_callback'))->select($where, $order, $limit, $fields, $group, $params);
    }

    public static function getLogsById($id)
    {
        return self::getLogs('id = :id', null, null, '*', null, [
            'id' => $id
        ])->fetchObject(self::class);
    }

    public static function getLogsByProtocolo($protocolo)
    {
        return self::getLogs('protocolo_sz = :protocolo_sz', null, null, '*', null, [
            'protocolo_sz' => $protocolo
        ])->fetchObject(self::class);
    }

    public static function getLogsByStatus($status)
    {
        return self::getLogs('status = :status', null, null, '*', null, [
            'status' => $status
        ]);
    }

    public function excluir()
    {
        return (new Database('logs_callback'))->delete('id = ' . $this->id);
    }

    public function atualizar()
    {
        (new Database('logs_callback'))->update('id = ' . $this->id, [
            'protocolo_sz' => $this->protocolo_sz,
            'error' => $this->error,
            'protocolo' => $this->protocolo,
            'status' => $this->status,
            'data' => $this->data
        ]);

        return true;
    }
}