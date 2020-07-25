<?php

namespace App\Controllers;

use App\Models\User;
use Core\Controller;

class AuthController extends Controller
{
    function getPage()
    {
        $this->render('login.twig', [
            'hide_login' => true
        ]);
    }

    function login()
    {
        $user = new User($this->request);
        if (!$user->authenticate()) {
            $this->errors = $user->errors;
            return $this->getPage();
        }

        setcookie('session', $user->session->id);

        $this->redirect('/');
    }

    function logout()
    {
        if ($this->is_authenticated) {
            $session = $this->user->session;
            $session->deactivateSession() && setcookie('session', null, -1);
        }

        $this->redirect('/');
    }
}
