<?php

namespace Core;

use MongoDB\Client;

class Model
{
    /**
     * @var MongoDB\Database
     */
    private static $db = false;
    protected static function getDb()
    {
        if (self::$db === false) {
            $login = 'diatomiccoder';
            $password = 'QwerTy0987';
            $dbname = 'dicoderDB';
            $connection = new Client("mongodb+srv://$login:$password@cluster0.shkax.mongodb.net/$dbname?retryWrites=true&w=majority");

            self::$db = $connection->selectDatabase('dicoderDB');
        }

        return self::$db;
    }

    public $id = null;

    public $errors = [];

    function is_valid()
    {
        return count($this->errors) === 0;
    }

    function validate()
    {
    }
}
