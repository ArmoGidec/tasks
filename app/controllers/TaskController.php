<?php

namespace App\Controllers;

use App\Models\Task;
use Core\Controller;

class TaskController extends Controller
{
    /**
     * @param string $task_id
     */
    function completeTask($task_id) {
        $task = Task::getOne($task_id);
        if ($this->is_authenticated && !$task->done) {
            $task->completeTask();
        }

        return $this->redirect('/');
    }

    /**
     * @param string $task_id
     */
    function viewChangePage($task_id) {
        if ($this->is_authenticated) {
            $task = Task::getOne($task_id);
            return $this->render('change-page.twig', [
                'task' => $task
            ]);
        }

        return $this->redirect('/');
    }

    /**
     * @param string $task_id
     */
    function changeTask($task_id)
    {
        if ($this->is_authenticated) {
            $task = Task::getOne($task_id);

            if (!$task->changeText($this->request['task_text'])) {
                $this->errors['task_text'] = 'Server error. Try to save task text later';
                return $this->viewChangePage($task_id);
            }

            return $this->redirect('/');
        }
        
        return $this->redirect('/login');
    }
}