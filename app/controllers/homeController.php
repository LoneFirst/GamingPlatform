<?php
namespace controllers;

use models\users;
use models\keys;
use models\games;

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
        view('home', ['section' => '纵览'])->push('gameList', $gameList)->render();
    }

    public function convert()
    {
        if (!users::auth()) {
            redirect('./');
        }
        $user = $_SESSION['user'];
        $gameId = $_POST['gameId'];
        if (isset($_POST['key'])) {
            $st = keys::select(['used', 'type', 'value'], ['key' => $_POST['key']])[0];
            if (isset($st['value'])) {
                if (is_null($st['used'])) {
                    $type = $st['type'];
                    $game = games::select([$type], ['id' => $gameId])[0];
                    if ($type == 'time') {
                        if ($game['time'] < time()) {
                            $value = time() + $st['value'];
                        } else {
                            $value = $game['time'] + $st['value'];
                        }
                    } elseif ($type == 'limit') {
                        $value = $game['limit'] + $st['value'];

                        $port = 7774 + $gameId * 3;
                        $filePath = $gamePath['userBase'].'\\'.$port.'\ShooterGame\Saved\Config\WindowsServer\GameUserSettings.ini';
                        $h = fopen($filePath, 'rb');
                        $c = '';
                        while(!feof($h)) {
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
                        }
                        fclose($h);
                        file_put_contents($filePath);

                    }
                    games::update(['id' => $gameId], [$type => $value]);
                    keys::update(['key' => $_POST['key']], ['used' => $user]);
                    $msg = ['status' => true, 'msg' => '兑换成功'];
                } else {
                    $msg = ['status' => false, 'msg' => '兑换码已被使用'];
                }
            } else {
                $msg = ['status' => false, 'msg' => '兑换码不存在'];
            }
        }
        response()->json($msg);
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
