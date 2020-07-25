<?php

namespace App\Models;

use Core\Model;
use \MongoDB\BSON\ObjectId;
use Firebase\JWT\JWT;

define('key', 'QwerTy0987');

class Session extends Model
{
    public $token = '';
    public $user_id = '';

    /**
     * @param object|array $payload
     * @return Session|null
     */
    static function createSession($payload)
    {
        $payload = array_merge(['ip' => self::getIp()], $payload);

        $session = new self();
        $session->token = JWT::encode($payload, key);
        $session->id = self::saveSession($session->token);

        return $session->id !== null ? $session : null;
    }

    /**
     * @param string $token
     * @return mixed
     */
    private static function saveSession($token)
    {
        /**
         * @var \MongoDB\Collection
         */
        $collection = self::getDb()->session;
        $insertResult = $collection->insertOne(['token' => $token, 'disabled' => false]);
        return $insertResult->getInsertedId();
    }

    /**
     * @return string
     */
    private static function getIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $ip;
    }

    /**
     * @param string $session
     */
    static function getSession($session_token)
    {
        $session = new self();

        $session_object = self::getDb()->session->findOne(
            ['_id' => new ObjectId($session_token), 'disabled' => false]);
        if ($session_object !== null) {
            $session->token = $session_object->token;
            $session->id = $session_object->_id;
        }

        return $session->id ? $session : null;
    }

    function validate()
    {
        $payload = JWT::decode($this->token, key, ['HS256']);

        if ($payload->ip !== self::getIp()) {
            $this->errors['session'] = 'Invalid session';
        } else {
            $this->user_id = json_decode(json_encode($payload), true)['user_id']['$oid'];
        }

        return $this->is_valid();
    }

    /**
     * @return bool
     */
    function deactivateSession()
    {
        return self::getDb()->session->updateOne(
            ['_id' => new ObjectId($this->id)],
            ['$set' => ['disabled' => true]]
        )->getModifiedCount() > 0;
    }
}
