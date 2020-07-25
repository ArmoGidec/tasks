<?php

namespace App\Models;

use Core\Model;
use \MongoDB\BSON\ObjectId;

class User extends Model
{
    public $login = '';
    private $password = '';

    /**
     * @var \App\Models\Session
     */
    public $session = null;

    function __construct($credentails = [])
    {
        $default_credentials = [
            'login' => '',
            'password' => ''
        ];

        $credentails = array_merge($default_credentials, $credentails);

        $this->login = $credentails['login'];
        $this->password = $credentails['password'];

        $this->validate();
    }

    function validate()
    {
        if ($this->login === '') {
            $this->errors['login'] = 'Login is required';
        }

        if ($this->password === '') {
            $this->errors['password'] = 'Password is required';
        }

        return $this->is_valid();
    }

    function authenticate()
    {
        $success = false;
        if ($this->is_valid()) {
            /**
             * @var \MongoDB\Collection
             */
            $collection = self::getDb()->users;
            $user = $collection->findOne([
                'login' => $this->login
            ]);

            if ($user !== null && password_verify($this->password, $user->password)) {
                $this->session = Session::createSession([
                    'user_id' => $user->_id
                ]);
                $success = true;
            } else {
                $this->errors = [
                    'common' => 'Login/Password is invalid'
                ];
            }
        }

        return $success;
    }

    static function check_auth($session_token)
    {
        $session = Session::getSession($session_token);

        if ($session !== null && $session->validate()) {
            $user_id = $session->user_id;
            $user = self::getDb()->users->findOne(['_id' => new ObjectId($user_id)]);

            $user = new User([
                'login' => $user->login,
                'password' => $user->password
            ]);

            $user->session = $session;

            return $user;
        }

        return null;
    }
}
