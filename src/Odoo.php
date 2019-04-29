<?php

namespace Bagus\Odoo;

use Ripcord\Ripcord;

class Odoo
{
    var $uid;
    var $password;
    var $database;
    var $model;

    public function __construct($username, $password, $database, $url)
    {
        $info = Ripcord::client('https://demo.odoo.com/start')->start();
        $common = ripcord::client("$url/xmlrpc/2/common");
        $this->password = $password;
        $this->database = $database;
        $this->uid = $common->authenticate($database, $username, $password, array());

        $this->model = ripcord::client("$url/xmlrpc/2/object");
    }

    public function search($object, $search_params = []) {
        $this->model->execute_kw($this->database, $this->uid, $this->password,
            $object, 'search', array(
                $search_params
            )
        );

        return $this->_formatReturn();
    }

    private function _formatReturn() {
        $xml = simplexml_load_string($this->model->_response);
        $json = json_encode($xml);
        $array = json_decode($json);
        return $array->params->param->value->array->data->value;
    }

}
