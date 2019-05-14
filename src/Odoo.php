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
        $this->uid = $common->authenticate($database, $username, $password, []);

        $this->model = ripcord::client("$url/xmlrpc/2/object");
    }

    public function search($object, $search_params = []) {
        $this->model->execute_kw($this->database, $this->uid, $this->password,
            $object, 'search', [
                $search_params
            ]
        );

        return $this->_formatXML();
    }

    public function searchRead($object, $search_params = [], $fields = []) {
        $this->model->execute_kw($this->database, $this->uid, $this->password,
            $object, 'search_read', [
                [$search_params]
            ],
            [
                'fields' => $fields
            ]
        );

        $response = $this->_formatXML();
        $result = [];
        if (is_array($response)) {
            foreach ($response as $res) {
                $result[] = $this->_formatObject($res);
            }
        } else if (is_object($response)) {
            $result[] = $this->_formatObject($response);
        }

        return $result;
    }

    public function create($object, $data) {
        return $this->model->execute_kw($this->database, $this->uid, $this->password,
            $object, 'create', [
                $data
            ]
        );
    }

    private function _formatXML() {
        $xml = simplexml_load_string($this->model->_response);
        $json = json_encode($xml);
        $array = json_decode($json);
        return $array->params->param->value->array->data->value;
    }

    private function _formatObject($res) {
        $obj = new \stdClass();

        foreach ($res->struct->member as $property) {
            if (isset($property->value->string)) {
                $obj->{$property->name} = $property->value->string;
            } else if (isset($property->value->int)) {
                $obj->{$property->name} = $property->value->int;
            } else if (isset($property->value->double)) {
                $obj->{$property->name} = $property->value->double;
            } else if (isset($property->value->boolean)) {
                $obj->{$property->name} = $property->value->boolean == 0 ? FALSE : TRUE;
            } else {
                $obj->{$property->name} = $property->value;
            }
        }

        return $obj;
    }

}
