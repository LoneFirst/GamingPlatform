<?php
namespace controllers;

use models\users;

class userController
{
    public function index()
    {
        if (users::auth()) {
            redirect('./home');
        }
        view('rl')->render();
    }

    public function register()
    {
        if (users::auth()) {
            redirect('./home');
        }
        $email = $_POST['email'];
        $password = $_POST['password'];
        $time = time();
        if (users::isUserExist($email)) {
            view('rl', ['msg' => '邮箱已被使用,请尝试登陆'])->render();
        } else {
            $token = md5($email.$time);
            users::create(['email' => $email, 'password' => password_hash($password, PASSWORD_BCRYPT), 'token' => $token, 'created_at' => $time]);
            $_SESSION['user'] = $email;
            $uss = $email.':'.$token;
            setcookie('uss', base64_encode($uss), time() + 3600*24);
            redirect('./home');
        }
    }

    public function login()
    {
        if (users::auth()) {
            redirect('./home');
        }
        $email = $_POST['email'];
        $password = $_POST['password'];
        if (!users::isUserExist($email)) {
            view('rl', ['msg' => '用户不存在,请先注册或确认你的邮箱'])->render();
        } elseif (!users::verPassword($email, $password)) {
            view('rl', ['msg' => '密码错误'])->render();
        } else {
            $_SESSION['user'] = $email;
            setcookie('uss', base64_encode($uss), time() + 3600*24);
            redirect('./home');
        }
    }

    public function logout()
    {
        session_unset();
        setcookie('uss', '', time()-1000);
        redirect(FILE_PATH);
    }
}
