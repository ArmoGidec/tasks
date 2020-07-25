<?php

namespace App\Models;

use Core\Model;
use \MongoDB\BSON\ObjectId;

class Task extends Model
{
    public $user_name = '';
    public $user_email = '';
    public $text = '';
    public $done = false;
    public $changed = false;

    /**
     * @param object|array $params
     */
    function __construct($params)
    {
        $default_params = [
            'user_name' => '',
            'user_email' => '',
            'text' => '',
            'done' => false,
            'changed' => false
        ];

        $params = array_merge($default_params, $params);

        $this->user_name = $params['user_name'];
        $this->user_email = $params['user_email'];
        $this->text = $params['text'];
        $this->done = $params['done'];
        $this->changed = $params['changed'];

        $this->validate();
    }

    /**
     * @return Task
     */
    function save()
    {
        if ($this->is_valid()) {
            $insert_result = parent::getDb()->tasks->insertOne([
                'user_name' => $this->user_name,
                'user_email' => $this->user_email,
                'text' => $this->text,
                'done' => $this->done,
                'changed' => $this->changed
            ]);

            $this->id = $insert_result->getInsertedId();
        }
        return $this;
    }

    /**
     * @return bool
     */
    function validate()
    {
        if ($this->user_email === '') {
            $this->errors['user_email'] = 'User email is required';
        } elseif (!filter_var($this->user_email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['user_email'] = 'Invalid email field';
        }

        if ($this->user_name === '') {
            $this->errors['user_email'] = 'User name is required';
        }
        if ($this->text === '') {
            $this->errors['task_text'] = 'Task text is required';
        }

        return $this->is_valid();
    }

    static function getAll()
    {
        $tasks = iterator_to_array(self::getDb()->tasks->find());
        return array_map(function ($task_entity) {
            $task = new Task([
                'user_name' => $task_entity->user_name,
                'user_email' => $task_entity->user_email,
                'text' => $task_entity->text,
                'done' => $task_entity->done,
                'changed' => $task_entity->changed
            ]);
            $task->id = (string) $task_entity->_id;
            return $task;
        }, $tasks);
    }

    /**
     * @param string $task_id
     * @return Task
     */
    static function getOne($task_id)
    {
        $task = self::getDb()->tasks->findOne(['_id' => new ObjectId($task_id)]);

        $task = new Task([
            'user_name' => $task->user_name,
            'user_email' => $task->user_email,
            'text' => $task->text,
            'done' => $task->done,
            'changed' => $task->changed
        ]);

        $task->id = $task_id;

        return $task;
    }

    /**
     * @return bool
     */
    function completeTask()
    {
        return self::getDb()->tasks->updateOne(
            ['_id' => new ObjectId($this->id)],
            ['$set' => ['done' => true]]
        )->getModifiedCount() > 0;
    }

    /**
     * @return bool
     */
    function changeText($text)
    {
        return self::getDb()->tasks->updateOne(
            ['_id' => new ObjectId($this->id)],
            ['$set' => ['changed' => true, 'text' => $text]]
        )->getModifiedCount() > 0;
    }
}
