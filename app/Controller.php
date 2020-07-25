<?php

namespace Core;

use App\Models\User;

class Controller
{
    private $twig = null;

    protected $errors = [];

    protected $request = null;
    protected $cookies = null;
    protected $user = null;
    protected $is_authenticated = false;

    function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
        $this->twig = new \Twig\Environment($loader);
        $this->request = $_REQUEST;
        $this->cookies = $_COOKIE;
        
        if (isset($this->cookies['session'])) {
            $this->user = User::check_auth($this->cookies['session']);
            $this->is_authenticated = $this->user !== null;
        }
    }

    function render($template_name, $ctx = [])
    {
        $ctx = array_merge([
            'errors' => $this->errors,
            'is_authenticated' => $this->is_authenticated
        ], $ctx);
        echo $this->twig->render($template_name, $ctx);
    }

    function redirect($url)
    {
        header("Location: $url");
    }
}
