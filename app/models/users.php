<?php
namespace models;

use core\model;
use models\games;

class users extends model
{
    public static function auth()
    {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        } elseif (isset($_COOKIE['uss'])) {
            $uss = base64_decode($_COOKIE['uss']);
            $uss = explode(':', $uss);
            $st = self::select(['token'], ['user' => $uss[0]]);
            if ($st[0]['token'] == $uss[1]) {
                $_SESSION['user'] = $uss[0];
                return $uss[0];
            } else {
                setcookie('uss', '', time()-1000);
                return false;
            }
        } else {
            return false;
        }
    }

    public static function isUserExist($email)
    {
        $st = self::select(['email'], ['email' => $email]);
        if (isset($st[0]['email'])) {
            return true;
        }
        return false;
    }

    public static function verPassword($email, $password)
    {
        $st = self::select(['password'], ['email' => $email]);
        return password_verify($password, $st[0]['password']);
    }
    
    public static function verQuota($email)
    {
        $st = self::select(['quota'], ['email' => $email]);
        $game = games::select(['id'], ['owner' => $email]);
        if (count($game) < $st[0]['quota']) {
            return true;
        } else {
            return false;
        }
    }
}
