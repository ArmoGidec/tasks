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
    private $ip = null;

    /**
     * @param object|array $payload
     * @return Session|null
     */
    static function createSession($payload)
    {
        $session = new self();

        $session->user_id = $payload['user_id'];
        $session->ip = self::getIp();
        $session->id = $session->save();

        return $session->id !== null ? $session : null;
    }

    /**
     * @param string $token
     * @return mixed
     */
    private function save()
    {
        $insertResult = self::getDb()->session->insertOne([
            'ip' => $this->ip,
            'user_id' => $this->user_id,
            'disabled' => false
        ]);
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
    static function getSession($session_id)
    {
        $session = new self();

        $session_object = self::getDb()->session->findOne(
            ['_id' => new ObjectId($session_id), 'disabled' => false]);
        if ($session_object !== null) {
            $session->id = $session_id;
            $session->ip = $session_object->ip;
            $session->user_id = $session_object->user_id;
        }

        return $session->id ? $session : null;
    }

    function validate()
    {
        if ($this->ip !== self::getIp()) {
            $this->errors['session'] = 'Invalid session';
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
