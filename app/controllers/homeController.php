<?php
namespace controllers;

use models\users;
use models\keys;
use models\games;
use controllers\gameController;

class homeController
{
    public function __construct()
    {
        if (!users::auth()) {
            redirect('./');
        }
    }

    public function index()
    {
        $user = $_SESSION['user'];
        $verQuota = users::verQuota($user);
        $gameList = games::select(['id', 'game', 'time', 'limit'], ['owner' => $user]);
        if ($gameList) {
            foreach ($gameList as $key => $value) {
                if ($value['time'] < time()) {
                    $gameList[$key]['time'] = '已到期';
                } else {
                    $gameList[$key]['time'] = date('Y-m-d h:i:s', $value['time']);
                }

                $gameList[$key]['game'] = games::$gameName[$value['game']];
            }
        }
        $view = view('home', ['section' => '纵览', 'user' => $user]);
        $view->push('verQuota', $verQuota);
        $view->push('gameList', $gameList)->render();
    }

    public function convert()
    {
        if (!users::auth()) {
            redirect('./');
        }
        $user = $_SESSION['user'];
        if (isset($_POST['gameId'])) {
            $gameId = $_POST['gameId'];
        } elseif (isset($_GET['gameId'])) {
            $gameId = $_GET['gameId'];
        }
        
        if (isset($_POST['key'])) {
            $key = $_POST['key'];
        } elseif (isset($_GET['key'])) {
            $key = $_GET['key'];
        }
        if (isset($key)) {
            $st = keys::select(['used', 'type', 'value'], ['key' => $key])[0];
            $value = $st['value'];
            if (isset($st['value'])) {
                if (is_null($st['used'])) {
                    $type = $st['type'];
                    $game = games::select(['time', 'limit'], ['id' => $gameId])[0];
                    if ($type == 'time') {
                        if (!isset($gameId)) {
                            $msg = [false, '请在游戏管理页面使用此兑换码'];
                        } elseif ($game['time'] < time()) {
                            $value = time() + $st['value'];
                            games::update(['id' => $gameId], [$type => $value]);
                        } else {
                            $value = $game['time'] + $st['value'];
                            games::update(['id' => $gameId], [$type => $value]);
                        }
                    } elseif ($type == 'limit') {
                        if (!isset($gameId)) {
                            $msg = [false, '请在游戏管理页面使用此兑换码'];
                        } else {
                        
                            $value = $game['limit'] + $st['value'];

                            $port = 7774 + $gameId * 3;
                            $filePath = gameController::$gamePath['userBase'].'\\'.$port.'\ShooterGame\Saved\Config\WindowsServer\\'.gameController::$iniName['user'];
                            $h = fopen($filePath, 'rb');
                            $c = '';
                            while(!feof($h)) {
                            /*
                                $line = fgets($h);
                                if (!strstr($line, '=')) {
                                    $c .= $line;
                                    continue;
                                }
                                $t = explode('=', $line);
                                switch ($t[0]) {
                                    case 'MaxPlayers':
                                        $c .= 'MaxPlayers='.$value.PHP_EOL;
                                        break;
                                    default:
                                        $c .= $line;
                                        break;
                                }
                                */
                                $c .= fread($h, 4096);
                            }
                            $c = preg_replace('/MaxPlayers=[0-9]+/', 'MaxPlayers='.$value, $c);
                            fclose($h);
                            file_put_contents($filePath, $c);
                            games::update(['id' => $gameId], [$type => $value]);
                        }
                    } elseif ($type == 'quota') {
                        $st = users::select(['quota'], ['email' => $user])[0];
                        $value = $st['quota'] + $value;
                        users::update(['email' => $user], ['quota' => $value]);
                    }
                    keys::update(['key' => $key], ['used' => $user]);
                    $msg = ['status' => true, 'msg' => '兑换成功'];
                } else {
                    $msg = ['status' => false, 'msg' => '兑换码已被使用'];
                }
            } else {
                $msg = ['status' => false, 'msg' => '兑换码不存在'];
            }
        }
        if (\core\request::method() == 'POST') {
            response()->json($msg);
        } else {
            $view = view('home', ['section' => '兑换', 'user' => $user]);
            $view->push('msg', $msg)->render();
        }
    }

    // public function ark()
    // {
    //     $view = view('home', ['section' => '方舟']);
    //     $user = $_SESSION['user'];
    //     $user = users::select(['email', 'created_at', 'ark'], ['email' => $user]);
    //     $user = $user[0];
    //     if ($user['ark'] < time()) {
    //         $view->push('msg', [false, '您的配额已到期']);
    //     }
    //     $arkTime = date('Y-m-d H:i:s', $user['ark']);
    //     $view->push('arkTime', $arkTime);
    //     $view->render();
    // }
}
