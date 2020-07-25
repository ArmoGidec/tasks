<?php

namespace App\Controllers;

use App\Models\Task;
use Core\Controller;

class HomeController extends Controller
{
    function index()
    {
        $created = isset($this->request['created']) ? $this->request['created'] : false;
        return $this->render('home.twig', [
            'tasks' => Task::getAll(),
            'created' => $created
        ]);
    }

    function createTask()
    {
        $user_name = $_POST['user_name'];
        $user_email = $_POST['user_email'];
        $task_text = $_POST['task_text'];
        $task = new Task([
            'user_name' => $user_name,
            'user_email' => $user_email,
            'text' => $task_text
        ]);

        $saved_task = $task->save();

        if (!$saved_task->id) {
            $this->errors = $saved_task->errors;
            return $this->index();
        }

        return $this->redirect('/?created=true');
    }
}
