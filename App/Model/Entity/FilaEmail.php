<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class FilaEmail
{
    public $id;
    public $protocolo;
    public $tipo;
    public $vars;
    public $cliente_email;
    public $cliente_nome;
    public $status;
    public $enviado_em;

    public function cadastrar()
    {
        $this->id = (new Database('fila_emails'))->insert([
            'protocolo' => $this->protocolo,
            'tipo' => $this->tipo,
            'vars' => json_encode($this->vars),
            'cliente_email' => $this->cliente_email,
            'cliente_nome' => $this->cliente_nome,
            'status' => $this->status ?? 'pendente'
        ]);

        return true;
    }
    public function atualizar()
    {
        (new Database('fila_emails'))->update('id =' . $this->id, [
            'protocolo' => $this->protocolo,
            'tipo' => $this->tipo,
            'vars' => $this->vars,
            'cliente_email' => $this->cliente_email,
            'cliente_nome' => $this->cliente_nome,
            'status' => $this->status,
            'enviado_em' => $this->enviado_em
        ]);
        return true;
    }

    public static function getEmails($where = null, $order = null, $limit = null, $fields = '*', $group = null, $params = [])
    {
        return (new Database('fila_emails'))->select($where, $order, $limit, $fields, $group, $params);
    }
    public static function getEmailsByProtocol($protocol)
    {
        return self::getEmails('protocolo = :protocolo', null, null, '*', null, [
            ':protocolo' => $protocol
        ]);
    }

    public function excluir()
    {
        return (new Database('fila_emails'))->delete('id =' . $this->id);

    }
}