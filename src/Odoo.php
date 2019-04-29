<?php

namespace Bagus\Odoo;

use Ripcord;

class Odoo
{

    public function __construct()
    {
        $info = ripcord::client('https://demo.odoo.com/start')->start();
        list($url, $db, $username, $password) = array($info['host'], $info['database'], $info['user'], $info['password']);
        print_r($info);
    }

}
