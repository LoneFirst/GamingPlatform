<?php
namespace controllers;

use models\users;
use models\keys;
use models\games;

class gameController
{
    public static $gamePath = [
        'ark' => 'D:\ARK\base',
        'userBase' => 'D:\ARK',
    ];
    
    public static $iniName = [
        'user' => '.GameUserSettings.ini',
        'game' => '.Game.ini',
    ];

    public function create()
    {
        if (!users::auth()) {
            return;
        }
        $user = $_SESSION['user'];
        $game = $_POST['game'];
        if (!users::verQuota($user)) {
            return;
        }
        $st = games::select(['id'], ['game' => 'ark']);
        $c = count($st);
        $port = 7777 + $c * 3;
        $qp = 27015 + $c * 3;
        $rp = 27017 + $c * 3;
        $id = $c + 1;
        $filePath = self::$gamePath['userBase'].'\\'.$port;
        //cp(self::$gamePath['ark'], $filePath, 1);

        $h = fopen($filePath.'\ShooterGame\Saved\Config\WindowsServer\\'.self::$iniName['user'], 'rb');
        $c = '';
        while(!feof($h)) {
            $line = fgets($h);
            if (!strstr($line, '=')) {
                $c .= $line;
                continue;
            }
            $t = explode('=', $line);
            switch ($t[0]) {
                case 'Port':
                    $c .= 'Port='.$port.PHP_EOL;
                    break;

                case 'QueryPort':
                    $c .= 'QueryPort='.$qp.PHP_EOL;
                    break;

                case 'RCONPort':
                    $c .= 'RCONPort='.$rp.PHP_EOL;
                    break;

                case 'MaxPlayers':
                    $c .= 'MaxPlayers=0'.PHP_EOL;
                    break;

                default:
                    $c .= $line;
                    break;
            }
        }
        fclose($h);
        file_put_contents($filePath.'\ShooterGame\Saved\Config\WindowsServer\\'.self::$iniName['user'], $c);

        games::create(['id' => $id, 'game' => $game, 'owner' => $user]);
        echo 'success';
    }

    public function manage()
    {
        if (!users::auth()) {
            redirect(FILE_PATH);
        }
        if (!isset($_GET['id'])) {
            redirect(FILE_PATH);
        }

        $gameId = $_GET['id'];
        $user = $_SESSION['user'];
        $view = view('home', ['section' => '管理', 'user' => $user]);
        if ($user != games::getOwnerById($gameId)) {
            redirect(FILE_PATH);
        }
        $game = games::select(['id', 'game', 'time', 'limit'], ['id' => $gameId])[0];
        if ($game['time'] < time()) {
            $game['time'] = '已到期';
        } else {
            $game['time'] = date('Y-m-d h:i:s', $game['time']);
        }
        $game['game'] = games::$gameName[$game['game']];
        $view->push('game', $game);

        $gameId = $_GET['id'];
        $port = 7774 + $gameId * 3;
        $filePath = self::$gamePath['userBase'].'\\'.$port.'\ShooterGame\Saved\Config\WindowsServer';


        // GameUserSettings 设置
        //$h = fopen('C:\xampp\htdocs\tmp\GameUserSettings.ini', 'rb');
        $h = fopen($filePath.'\\'.self::$iniName['user'], 'rb');
        $c = '';
        while(!feof($h)) {
            $line = fgets($h);
            if (!strstr($line, '=')) {
                continue;
            }
            $t = explode('=', $line);
            switch ($t[0]) {
                case 'Port':
                    $Port = substr($t[1], 0, -2);
                    break;
                case 'QueryPort':
                    $QueryPort = substr($t[1], 0, -2);
                    break;

                case 'RCONPort':
                    $RCONPort = substr($t[1], 0, -2);
                    break;

                default:
                    $c .= self::handleHtml($t[0], $t[1]);
                    break;
            }
        }
        fclose($h);

        //$h = fopen('C:\xampp\htdocs\tmp\A.ini', 'rb');
        $h = fopen($filePath.'\\'.self::$iniName['game'], 'rb');
        $gameChangeHtml = '';
        while(!feof($h)) {
            $line = fgets($h);
            if (!strstr($line, '=') && !strstr($line, '.')) {
                $line = substr($line, 1, -3);
                $tmp = explode(',', $line);
                $gameChangeHtml .= '<div class="form-group">
                      <label class="col-sm-6 control-label">经验值模式</label>
                      <div class="col-sm-6">
                          <select name="expMode" class="form-control">
                              <option value="cs" '.($tmp[0] == 'cs'?'selected':'').'>成神</option>
                              <option value="fgf" '.($tmp[0] == 'fgf'?'selected':'').'>仿官方</option>
                          </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-6 control-label">人物等级</label>
                      <div class="col-sm-6">
                        <input name="rwdj" class="form-control" value="'.$tmp[1].'">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-6 control-label">每一级技能点</label>
                      <div class="col-sm-6">
                        <input name="mjjnd" class="form-control" value="'.$tmp[2].'">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-6 control-label">驯服龙等级</label>
                      <div class="col-sm-6">
                        <input name="xfldj" class="form-control" value="'.$tmp[3].'">
                      </div>
                    </div>';
                break;
            } else {
                if (!strstr($line, '=')) {
                    continue;
                }
                
                $t = explode('=', $line);
                if (!array_key_exists($t[0], self::$trans)) {
                    continue;
                }
                $gameChangeHtml .= '
                <div class="form-group">
                  <label class="col-sm-6 control-label">'.self::$trans[$t[0]].'</label>
                  <div class="col-sm-6">
                    <input name="'.str_replace(']', '】', str_replace('[', '【', $t[0])).'" class="form-control" value="'.$t[1].'">
                  </div>
                </div>';
            }
        }
        fclose($h);

        $view->push('managePageHtml', $c);
        $view->push('gameChangeHtml', $gameChangeHtml);
        $view->push('Port', $Port);
        $view->push('QueryPort', $QueryPort);
        $view->push('RCONPort', $RCONPort);
        $view->render();
    }

    public function change()
    {
        if (!users::auth()) {
            redirect(FILE_PATH);
        }

        $gameId = $_GET['id'];
        $port = 7774 + $gameId * 3;
        $filePath = self::$gamePath['userBase'].'\\'.$port.'\ShooterGame\Saved\Config\WindowsServer\\'.self::$iniName['user'];

        $h = fopen($filePath, 'rb');
        while(!feof($h)) {
            $line = fgets($h);
            if (!strstr($line, '=')) {
                $r .= $line;
                continue;
            }
            $t = explode('=', $line);
            if (isset($_POST[$t[0]])) {
                $r .= $t[0].'='.$_POST[$t[0]].PHP_EOL;
            } else {
                $r .= $line;
            }
        }
        fclose($h);
        // file 需要设置
        file_put_contents($filePath, $r);
        echo '<script>history.go(-1)</script>';
    }

    public function gameChange()
    {
        if (!users::auth()) {
            redirect(FILE_PATH);
        }

        $gameId = $_GET['id'];
        $port = 7774 + $gameId * 3;
        $filePath = self::$gamePath['userBase'].'\\'.$port.'\ShooterGame\Saved\Config\WindowsServer\\'.self::$iniName['game'];

        $h = fopen($filePath, 'rb');
        while(!feof($h)) {
            $line = fgets($h);
            if (!strstr($line, '=') && !strstr($line, '.')) {
                $r .= '['.$_POST['expMode'].','.$_POST['rwdj'].','.$_POST['mjjnd'].','.$_POST['xfldj'].']'.PHP_EOL;
                $r .= 'LevelExperienceRampOverrides=(';
                $exp = 10;
                $tmp = '';
                for ($i=0;$i<$_POST['rwdj'];$i++) {
                    $tmp .= 'ExperiencePointsForLevel['.$i.']='.$exp.',';
                    if ($_POST['expMode'] == 'cs') {
                        $exp = 10;
                    } else if ($_POST['expMode'] == 'fgf') {
                        $exp += $i * 5 + 10;
                    }
                }
                $tmp = substr($tmp, 0, -1);
                $r .= $tmp.')'.PHP_EOL;
                $tmp = '';
                $r .= 'LevelExperienceRampOverrides=(';
                $exp = 10;
                for ($i=0;$i<$_POST['xfldj'];$i++) {
                    $tmp .= 'ExperiencePointsForLevel['.$i.']='.$exp.',';
                    if ($_POST['expMode'] == 'cs') {
                        $exp = 10;
                    } else if ($_POST['expMode'] == 'fgf') {
                        $exp += $i * 5 + 10;
                    }
                }
                $tmp = substr($tmp, 0, -1);
                $r .= $tmp.')'.PHP_EOL;
                for ($i=0;$i<$_POST['rwdj'];$i++) {
                    $r .= 'OverridePlayerLevelEngramPoints='.$_POST['mjjnd'].PHP_EOL;
                }
                break;
            }
            if (!strstr($line, '=')) {
                $r .= $line;
                continue;
            }
            $t = explode('=', $line);
            if (isset($_POST[str_replace(']', '】', str_replace('[', '【', $t[0]))])) {/*
                if (strstr($t[0], '_')) {
                    $num = intval(preg_replace('/\[([0-9]+)\]/', '\\1', $t[0]));
                    $str = preg_replace('/([A-Za-z_]+)/', '\\1', $t[0]);
                    $r .= $t[0].'='.$_POST[$str][$num].PHP_EOL;
                } else {*/
                    $r .= $t[0].'='.$_POST[str_replace(']', '】', str_replace('[', '【', $t[0]))].PHP_EOL;
                /*}*/
            } else {
                $r .= $line;
            }
        }
        fclose($h);
        // file 需要设置
        file_put_contents($filePath, $r);
        echo '<script>history.go(-1)</script>';
    }

    public function start()
    {
        if (!users::auth()) {
            exit();
        }
        $gameId = $_GET['id'];
        $port = 7774 + $gameId * 3;
                
        $filePath = self::$gamePath['userBase'].'\\'.$port.'\ShooterGame\Saved\Config\WindowsServer\\';

        copy($filePath.self::$iniName['user'], $filePath.'GameUserSettings.ini');
        @touch($filePath.'Game.ini');
        
        $h = fopen($filePath.self::$iniName['game'], 'rb');
        $r = '';
        while(!feof($h)) {
            $line = fgets($h);
            if (strstr($line, ',')) {
                $line = substr($line, 1, -3);
                $params = explode(',', $line);
                $r .= 'LevelExperienceRampOverrides=(';
                $exp = 10;
                $tmp = '';
                for ($i=0;$i<$params[1];$i++) {
                    $tmp .= 'ExperiencePointsForLevel['.$i.']='.$exp.',';
                    if ($params[0] == 'cs') {
                        $exp = 10;
                    } else if ($params[0] == 'fgf') {
                        $exp += $i * 5 + 10;
                    }
                }
                $tmp = substr($tmp, 0, -1);
                $r .= $tmp.')'.PHP_EOL;
                $tmp = '';
                $r .= 'LevelExperienceRampOverrides=(';
                $exp = 10;
                for ($i=0;$i<$params[3];$i++) {
                    $tmp .= 'ExperiencePointsForLevel['.$i.']='.$exp.',';
                    if ($params[0] == 'cs') {
                        $exp = 10;
                    } else if ($params[0] == 'fgf') {
                        $exp += $i * 5 + 10;
                    }
                }
                $tmp = substr($tmp, 0, -1);
                $r .= $tmp.')'.PHP_EOL;
                for ($i=0;$i<$params[1];$i++) {
                    $r .= 'OverridePlayerLevelEngramPoints='.$params[2].PHP_EOL;
                }
                break;
            } else {
                $r .= $line;
            }
        }
        fclose($h);
        file_put_contents($filePath.'Game.ini', $r);

        $filePath = self::$gamePath['userBase'].'\\'.$port.'\RunServer.cmd';
        session_write_close();
        shell_exec($filePath);
        exit();
    }

    public function stop()
    {
        if (!users::auth()) {
            exit();
        }
        
        $gameId = $_GET['id'];
        $port = 7774 + $gameId * 3;
        $filePath = self::$gamePath['userBase'].'\\'.$port.'\StopServer.cmd';
        shell_exec($filePath);
    }

    public function upgrade()
    {
        if (!users::auth()) {
            redirect(FILE_PATH);
        }
        $gameId = $_GET['id'];
        $port = 7774 + $gameId * 3;
        $filePath = self::$gamePath['userBase'].'\\UPDATE\\'.$port.'\update.bat';
        session_write_close();
        shell_exec($filePath);
    }

    public function delete()
    {
        if (!users::auth()) {
            redirect(FILE_PATH);
        }
        $gameId = $_GET['id'];
        $port = 7774 + $gameId * 3;
        //$filePath = self::$gamePath['userBase'].'\\'.$port.'delete.cmd';
        shell_exec($filePath);
        echo 'success';
    }

    public function updated()
    {
        $gameId = $_GET['id'];
        $port = 7774 + $gameId * 3;
        $filePath = self::$gamePath['userBase'].'\\UPDATE\\'.$port.'\1.txt';
        if (file_exists($filePath)) {
            response()->json(['status' => true]);
        } else {
            response()->json(['status' => false]);
        }

    }

    public function changeMap()
    {
        if (!users::auth()) {
            redirect(FILE_PATH);
        }
        $gameId = $_POST['gameId'];
        $port = $_POST['port'];
        $qp = $_POST['qp'];
        $limit = $_POST['limit'];
        $map = $_POST['map'];
        $port = 7774 + $gameId * 3;
        //$path = self::$gamePath['userBase'].'\\'.$port;
        $filePath = self::$gamePath['userBase'].'\\'.$port.'\RunServer.cmd';
        $filePathwm = self::$gamePath['userBase'].'\\'.$port.'\StopServer.cmd';
        $r = 'echo "hi" > D:\ARK\\'.$port.'\running.txt'.PHP_EOL;
        $r .= 'start D:\ARK\\'.$port.'\ShooterGame\Binaries\Win64\ShooterGameServer.exe'.' '.$map.'?listen?Port='.$port.'?QueryPort='.$qp.'?MaxPlayers='.$limit.' -nosteamclient -game -server -log';
        $wmr = 'wmic process where "name=\'ShooterGameServer.exe\' and ExecutablePath=\'D:\\\\ARK\\\\'.$port.'\\\\ShooterGame\\\\Binaries\\\\Win64\\\\ShooterGameServer.exe\'" call Terminate';
        $wmr .= PHP_EOL.'del /f /s /q /f D:\ARK\\'.$port.'\running.txt';
        if (!file_exists($filePath)) {
            touch($filePath);
        }
        if (!file_exists($filePathwm)) {
            touch($filePathwm);
        }
        file_put_contents($filePath, $r);
        file_put_contents($filePathwm, $wmr);
        echo 'success';
    }
    
    public function serverStatus()
    {
        $gameId = $_GET['id'];
        $port = 7774 + $gameId * 3;
        $filePath = self::$gamePath['userBase'].'\\'.$port.'\running.txt';
        if (file_exists($filePath)) {
            response()->json(['status' => true, 'port' => $port]);
        } else {
            response()->json(['status' => false, 'port' => $port]);
        }
    }

    public static $trans = [
        'MaxTribeLogs' => '最大值部落日志[设置每个部落保持的部落日志项的最大数目（默认为100）]',
        'bDisableFriendlyFire' => '禁用 PVP 友军伤害[如果启用，玩家无法处理伤害或杀死其他部落成员，恐龙和建筑，]',
        'bPvEDisableFriendlyFire' => '禁用 PVE 伤害[如果启用，玩家无法处理伤害或杀死其他部落成员，恐龙和建筑]',
        'bDisableLootCrates' => '禁用战利品箱子[如果启用，将不再产生战利品箱子(但不会影响到神器箱子的产生)]',
        'MaxNumberOfPlayersInTribe' => '部落玩家数量:[设置为 0 ,部落玩家是无限的.设置数字大于 0 作为限制部落玩家数量.]',
        'bIncreasePvPRespawnInterval' => '增加PVP刷新间隔[如果启用，使增加PVP刷新间隔设置]',
        'bAutoPvETimer' => 'bAutoPvETimer',
        'bPvEAllowTribeWar' => '允许部落战争[如果启用，部落之间可以宣战并在约定的时间内进行战争]',
        'bPvEAllowTribeWarCancel' => '允许取消部落战争[如果启用,部落能够取消前一个商定的战争，哪怕实际上已经开始了的战争.]',
        'bAllowCustomRecipes' => '允许自定义的食谱[如果启用，玩家可以使用自定义的面向快速成型的食谱/烹饪系统（包括技能为基础的结果）]',
        'CustomRecipeEffectivenessMultiplier' => '自定义食谱有效倍数[为客户指定乘数配方有效性。更高的值增加配方的有效性]',
        'CustomRecipeSkillMultiplier' => '自定义食谱技能倍数[为客户指定乘数配方的技能。更高的值增加配方的技能]',
        'CraftXPMultiplier' => '获得经验倍率[制作]',
        'GenericXPMultiplier' => '获得经验倍率[通用，随着时间的推移]',
        'HarvestXPMultiplier' => '获得经验倍率[收获]',
        'KillXPMultiplier' => '获得经验倍率[杀死]',
        'SpecialXPMultiplier' => '获得经验倍率[特殊活动]',
        'OverrideMaxExperiencePointsPlayer' => '玩家最大经验值',
        'PlayerHarvestingDamageMultiplier' => '玩家收获伤害[指定材料的收获参数.数值越高,则对作物越高的损害,从而提高资源的获得量]',
        'OverrideMaxExperiencePointsDino' => '龙最大经验值',
        'DinoHarvestingDamageMultiplier' => '龙收获伤害[指定材料的收获参数.数值越高,则对作物越高的损害,从而提高资源的获得量]',
        'DinoTurretDamageMultiplier' => '龙对炮塔伤害[指定损坏炮塔（炮塔子弹和炮弹）的参数,数值越高,对炮塔的伤害,较低的值减少.]',
        'MatingIntervalMultiplier' => '交配间隔[指定时间驯服恐龙之间的乘数交配。较低的值减少交配的时间.]',
        'EggHatchSpeedMultiplier' => '蛋孵化速度[指定的时间，受精卵孵化的倍数。较高的值降低卵孵化时间.]',
        'BabyMatureSpeedMultiplier' => '恐龙宝宝成熟的速度[指定的时间，小恐龙成长为成年恐龙的倍数。较高的值降低婴儿恐龙成熟到成人的时间.]',
        'BabyFoodConsumptionSpeedMultiplier' => '恐龙宝宝食品消耗速度[增加速度，小恐龙会消耗食物。更高的值会使恐龙宝宝更经常地吃食物.]',
        'BabyImprintingStatScaleMultiplier' => '印记统计表[多大程度上影响统计数据质量印记.设置为0,有效地禁用系统.]',
        'BabyCuddleIntervalMultiplier' => '拥抱间隔[宝宝想要拥抱的次数.通常意味着你需要更频繁地与他们拥抱获得优质印记.]',
        'BabyCuddleGracePeriodMultiplier' => '拥抱宽限期[延缓拥抱宝宝前印记质量开始下降的时间,乘数计算.]',
        'BabyCuddleLoseImprintQualitySpeedMultiplier' => '没拥抱失去印记质量速度[如果你还没拥抱宝宝,增加速度,降低基因质量的宽限期后]',
        'ResourceNoReplenishRadiusPlayers' => 'ResourceNoReplenishRadiusPlayers',
        'ResourceNoReplenishRadiusStructures' => 'ResourceNoReplenishRadiusStructures',
        'GlobalSpoilingTimeMultiplier' => '世界破坏时间[世界范围的破坏时间易腐烂的东西。更高的值延长时间]',
        'GlobalItemDecompositionTimeMultiplier' => '世界项目分解时间[]世界范围的分解时间项下降,全球战利品袋等。更高的价值延长时间.',
        'GlobalCorpseDecompositionTimeMultiplier' => '世界尸体腐烂时间[玩家尸体和恐龙分解时间,较高的值,延长时间.]',
        'CropDecaySpeedMultiplier' => '农作物衰变速度[农作物腐烂时间的范围。较低的值延长衰减.]',
        'CropGrowthSpeedMultiplier' => '农作物生长速度[农作物生长发育时间,较高的值使农作物生长更快.]',
        'LayEggIntervalMultiplier' => '产蛋间隔[恐龙产蛋间隔,较低的值导致恐龙下蛋更快]',
        'PoopIntervalMultiplier' => '排便间隔[玩家和恐龙排便的时间间隔,较低的数值可以使玩家和恐龙排便快.]',
        'StructureDamageRepairCooldown' => '建筑损伤修复的冷却时间[从上次损坏的建筑修复的冷却时间服务器选项。设置为0禁用它，官方默认为180秒]',
        'PvPZoneStructureDamageMultiplier' => 'PVP洞穴伤害[在PVP中如果在洞穴/洞入口建筑则会按指定比例参数对建筑进行伤害]',
        'bFlyerPlatformAllowUnalignedDinoBasing' => '允许不结盟的恐龙停在飞行平台鞍上[如果启用，非结盟的部落的恐龙能够站在自己部落的平台鞍上]',
        'bPassiveDefensesDamageRiderlessDinos' => '伤害被动防御的恐龙[如果启用，马刺栅栏会伤害野生/无主的恐龙.]',

        'PerLevelStatsMultiplier_Player[0]' => '玩家生命',
        'PerLevelStatsMultiplier_Player[1]' => '玩家耐力',
        'PerLevelStatsMultiplier_Player[2]' => '玩家麻痹值',
        'PerLevelStatsMultiplier_Player[3]' => '玩家氧气',
        'PerLevelStatsMultiplier_Player[4]' => '玩家食物',
        'PerLevelStatsMultiplier_Player[5]' => '玩家水',
        'PerLevelStatsMultiplier_Player[6]' => '玩家温度',
        'PerLevelStatsMultiplier_Player[7]' => '玩家负重',
        'PerLevelStatsMultiplier_Player[8]' => '玩家近战伤害',
        'PerLevelStatsMultiplier_Player[9]' => '玩家移动速度',
        'PerLevelStatsMultiplier_Player[10]' => '玩家坚韧（抗寒抗热）',
        'PerLevelStatsMultiplier_Player[11]' => '玩家制造速度',
        'PerLevelStatsMultiplier_DinoTamed[0]' => '驯服龙生命',
        'PerLevelStatsMultiplier_DinoTamed[1]' => '驯服龙耐力',
        'PerLevelStatsMultiplier_DinoTamed[2]' => '驯服龙麻痹值',
        'PerLevelStatsMultiplier_DinoTamed[3]' => '驯服龙氧气',
        'PerLevelStatsMultiplier_DinoTamed[4]' => '驯服龙食物',
        'PerLevelStatsMultiplier_DinoTamed[5]' => '驯服龙水',
        'PerLevelStatsMultiplier_DinoTamed[6]' => '驯服龙温度',
        'PerLevelStatsMultiplier_DinoTamed[7]' => '驯服龙负重',
        'PerLevelStatsMultiplier_DinoTamed[8]' => '驯服龙近战伤害',
        'PerLevelStatsMultiplier_DinoTamed[9]' => '驯服龙移动速度',
        'PerLevelStatsMultiplier_DinoWild[0]' => '野生龙生命',
        'PerLevelStatsMultiplier_DinoWild[1]' => '野生龙耐力',
        'PerLevelStatsMultiplier_DinoWild[2]' => '野生龙麻痹值',
        'PerLevelStatsMultiplier_DinoWild[3]' => '野生龙氧气',
        'PerLevelStatsMultiplier_DinoWild[4]' => '野生龙食物',
        'PerLevelStatsMultiplier_DinoWild[5]' => '野生龙水',
        'PerLevelStatsMultiplier_DinoWild[6]' => '野生龙温度',
        'PerLevelStatsMultiplier_DinoWild[7]' => '野生龙负重',
        'PerLevelStatsMultiplier_DinoWild[8]' => '野生龙近战伤害',
        'PerLevelStatsMultiplier_DinoWild[9]' => '野生龙移动速度',
    ];

    public static $data = [
        'ServerPVE' => '服务器是否为PVE	',
        'HarvestAmountMultiplier' => '总收获倍数[值越大，一次攻击获取材料越多]',
        'DayTimeSpeedScale' => '白天流失速度[值越大，白天时间越短]',
        'NightTimeSpeedScale' => '夜晚流失速度[值越大，夜晚时间越短]',
        'DayCycleSpeedScale' => '时间循环速度[值越大方舟世界内一天对应现实时间越少]',
        'XPMultiplier' => '经验倍率[玩家以及恐龙的经验倍率]',
        'PerPlatformMaxStructuresMultiplier' => '平台鞍最大建筑倍数',
        'NewMaxStructuresInRange' => '最大建筑数量[定范围内的最大建筑碎片的数量限制]',
        'TamingSpeedMultiplier' => '驯服速度[值越大驯服越快]',
        'MaxTamedDinos' => '最大驯服恐龙数量[默认4000]',
        'DifficultyOffset' => '难度设定[固定生成]',
        'DinoCountMultiplier' => '恐龙产卵[最高不建议超过5，值越大世界内恐龙越多]',
        'ServerPassword' => '服务器密码[开启后会需要密码才能进入服务器]',
        'ServerAdminPassword' => '管理员密码[刷东西用的密码，请勿外泄]',
        'ServerCrosshair' => '准星是否启用',
        'globalVoiceChat' => '全球语音聊天',
        'proximityChat' => '附近聊天[如果启用，只有附近的玩家可以看到聊天消息]',
        'alwaysNotifyPlayerLeft' => '玩家离线提醒[如果启用，其余玩家下线会提醒]',
        'alwaysNotifyPlayerJoined' => '玩家上线提醒',
        'ServerHardcore' => '硬汉模式[玩家死后无法重生，必须重新创建一个角色才能游戏]',
        'ServerForceNoHud' => '强制无HUD',
        'AllowThirdPersonPlayer' => '允许第三人称[关闭后不能使用第三人称]',
        'ShowMapPlayerLocation' => '地图显示位置[关闭后地图不再显示自身位置]',
        'ShowFloatingDamageText' => '显示伤害',
        'EnablePVPGamma' => 'PVP伽马设置[开启后PVP服务器玩家也可以设置伽马值]',
        'DisablePvEGamma' => '关闭PVE伽马设置[设置关闭PVE服务器玩家也可以设置伽马值]',
        'PreventTribeAlliances' => '禁止部落联盟[如果启用，则部落不能联盟]',
        'PreventDiseases' => '禁止疾病[如果为是，将关闭疾病]',
        'NonPermanentDiseases' => '非永久性疾病[如果为是，将使疾病不是永久性的，重生后会消除]',
        'AllowCaveBuildingPvE' => '是否允许洞穴内建造	',
        'EnableExtraStructurePreventionVolumes' => '防止资源区建设[固定生成]',
        'NoTributeDownloads' => '禁止角色数据下载	',
        'AllowFlyerCarryPVE' => '是否允许飞行携带[部分功能龙是否能够抓人]',
        'AutoSavePeriodMinutes' => '自动保存时间[分钟为单位，最低10分钟]',
        'PreventOfflinePvP' => '防止离线PVP[如果启用，则禁止袭击离线玩家]',
        'AllowHitMarkers' => '击中提示',
        'ActiveMods' => 'MODID列表[以，为分隔符确定数量，会对个别MODID进行限制]',
        'PlayerCharacterHealthRecoveryMultiplier' => '人物生命恢复[人物生命恢复倍率，值越大恢复越快]',
        'PlayerCharacterStaminaDrainMultiplier' => '人物体力流失[值越大，人物体力流失越快]',
        'PlayerCharacterWaterDrainMultiplier' => '人物水分流失[值越大，人物水分下降越快]',
        'PlayerCharacterFoodDrainMultiplier' => '人物饥饿流失[值越大，人物饥饿下降越快]',
        'PlayerDamageMultiplier' => '人物伤害倍率[值越大造成伤害越高]',
        'PlayerResistanceMultiplier' => '人类抗性[值越大吸收伤害越多，值越小吸收伤害越少.]',
        'DinoCharacterStaminaDrainMultiplier' => '恐龙体力流失[值越大，恐龙体力流失越快]',
        'DinoCharacterHealthRecoveryMultiplier' => '恐龙生命恢复[值越大，恐龙生命回复越快]',
        'DinoDamageMultiplier' => '野生龙伤害倍率[值越大造成伤害越高]',
        'TamedDinoDamageMultiplier' => '驯服龙伤害倍率[驯服龙造成的伤害倍率]',
        'TamedDinoResistanceMultiplier' => '驯服龙抗性[值越大吸收伤害越多，值越小吸收伤害越少.]',
        'DinoResistanceMultiplier' => '野生龙抗性[值越大吸收伤害越多，值越小吸收伤害越少.]',
        'DinoCharacterFoodDrainMultiplier' => '恐龙饥饿流失[值越大，恐龙饥饿下降越快]',
        'AllowAnyoneBabyImprintCuddle' => '允许任何人照顾小龙[如果启用，任何人都能够照顾一个恐龙宝宝（拥抱等）]',
        'PvEDinoDecayPeriodMultiplie' => 'PvE恐龙衰变周期[对恐龙所有时间衰减的基础乘数。只有当PVE选中禁用恐龙衰变.]',
        'DisableImprintDinoBuff' => '禁用恐龙留痕BUFF[如果启用，将禁用恐龙留痕玩家统计奖金，正常情况下任何具体留在恐龙举有压痕质量，获取额外的伤害/抗性BUFF.]',
        'DisableDinoDecayPvE' => '禁用PVE恐龙衰变[如果启用，在PVE模式中禁用恐龙所有权逐渐衰减；否则每个恐龙都可以被任何玩家获得.]',
        'AllowRaidDinoFeeding' => '允许泰坦龙喂养[如果启用，允许您的服务器泰坦龙将被永久驯服（即允许它们被喂养）]',
        'RaidDinoCharacterFoodDrainMultiplier' => '食物消耗倍数[指定的泰坦龙食物消耗倍数。较高的值增加食物的消耗，较低的值减少食物消耗.]',
        'PvPStructureDecay' => 'PVP建筑衰变[如果启用，在PVP经过一段时间的闲置后建筑会自动衰减.]',
        'StructureDamageMultiplier' => '建筑（炮台）伤害[值越大，建筑（包括炮台）造成的伤害越高]',
        'OverrideStructurePlatformPrevention' => '允许炮台在平台鞍[如果启用，允许自动炮塔是可建设于恐龙平台鞍工作.]',
        'AutoDestroyOldStructuresMultiplier' => '自动销毁旧建筑[允许自动摧毁建筑,附近没有部落的一段时间后.服务器自动清除废弃的建筑,如果希望自动关闭功能.设置为 0 禁用它.]',
        'ForceAllStructureLocking' => '强制所有建筑锁定[如果启用，允许锁定所有项目容器.]',
        'StructureResistanceMultiplier' => '建筑抗性[值越大吸收伤害越多，值越小吸收伤害越少.]',
        'MaxPlatformSaddleStructureLimit' => '平台鞍最大建筑数量[平台鞍的最大建筑碎片数量]',
        'FastDecayUnsnappedCoreStructures' => '快速腐蚀核心建筑[如果启用，加快柱子地基腐蚀速度.]',
        'DisableStructureDecayPVE' => 'PVE建筑不衰变[如果关闭，在PVE经过一段时间的闲置后建筑会自动衰减.]',
        'PvEStructureDecayDestructionPeriod' => 'PVE建筑衰减周期[指定PVE模式下建筑自动衰减的时间.]',
        'PvEStructureDecayPeriodMultiplier' => 'PVE建筑衰减倍数[指定玩家建筑的PVE自动衰减倍数.]',
        'PvEAllowStructuresAtSupplyDrops' => '允许结构在供应丢弃PvE[如果启用，将阻止将结构放置在电源放置位置。]',
        'OnlyAutoDestroyCoreStructures' => '只自动销毁核心结构[如果启用，将防止任何非核心/非基础结构自动销毁（但是他们仍然会得到自动销毁，如果一个地板，他们在得到自动销毁）]',
        'OnlyDecayUnsnappedCoreStructures' => '只有衰变解开核心结构[如果启用,只有解开核心结构将衰变。用于消除孤独的支柱/垃圾邮件服务器上的基础.]',
        'ClampResourceHarvestDamage' => '资源收获伤害[如果启用，夹有多少收获伤害你可以做一个资源的剩余资源的健康.（不建议设置）]',
        'KickIdlePlayersPeriod' => '不知道啥玩意[固定生成]',
        'TribeLogDestroyedEnemyStructures' => '部落日志[固定生成]',
        'SpectatorPassword' => '不知道啥玩意[固定生成]',
        'RCONServerGameLogBuffer' => '不知道啥玩意[固定生成]',
        'ListenServerTetherDistanceMultiplier' => '不知道啥玩意[固定生成]',
        'AdminLogging' => '不知道啥玩意[固定生成]',
        'ResourcesRespawnPeriodMultiplier' => '资源重生速率[较低的值导致更频繁的节点重生.]',
        'HarvestHealthMultiplier' => '收获持久[指定可以收获(树木,岩石,尸体等)生命值倍数.这样的对象可以在被摧毁前承受更多伤害,从而提高整体收获]',
        'StructurePreventResourceRadiusMultiplier' => '不知道啥玩意[固定生成]',
        'RCONEnabled' => 'RCON端口是否开启	',
   
        'SessionName' => '服务器名称【建议不超过20个字】',
        'Message' => '进服公告',
        'Duration' => '进服公告持续时间，单位为秒',

 /*
        // [ServerSettings]
        'ServerPassword' => '服务器密码',
        'ServerAdminPassword' => '管理员密码',
        'SpectatorPassword' => '观察者密码',
        'RCONEnabled' => '启用RCON端口',
        'RCONServerGameLogBuffer' => 'RCON服务器日志缓冲区',
        'AdminLogging' => '管理聊天日志',
        'ActiveMods' => 'MOD ID',
        'AutoSavePeriodMinutes' => '自动保存时间',
        'TribeLogDestroyedEnemyStructures' => '部落日志摧毁敌人的结构',
        'ServerHardcore' => '专家模式',
        'ServerPVE' => 'PVE模式',
        'AllowCaveBuildingPvE' => '开启PVE洞穴建筑',
        'EnableExtraStructurePreventionVolumes' => '禁止在资源丰富地区建筑',
        'DifficultyOffset' => '难度',
        'NoTributeDownloads' => '关闭人物下载',
        'PreventOfflinePvP' => '防止离线PVP',
        'PreventTribeAlliances' => '防止部落联盟',
        'PreventDiseases' => '禁止疾病',
        'NonPermanentDiseases' => '禁止永久性疾病',
        'globalVoiceChat' => '全服语音',
        'proximityChat' => '附近玩家文字聊天',
        'alwaysNotifyPlayerLeft' => '玩家离线通知',
        'alwaysNotifyPlayerJoined' => '玩家上线通知',
        'ServerCrosshair' => '准心',
        'ServerForceNoHud' => '头顶名字显示',
        'AllowThirdPersonPlayer' => '使用第三人称视角',
        'ShowMapPlayerLocation' => '显示玩家在地图上的位置',
        'EnablePVPGamma' => '',
        'DisablePvEGamma' => '',
        'ShowFloatingDamageText' => '启用RPG风格伤害显示 [浮动伤害]',
        'AllowHitMarkers' => '',
        'AllowFlyerCarryPVE' => 'PVE 翼龙可以抓取任何生物?',
        'XPMultiplier' => '全局经验倍数 [数字越大升级越快]',
        'PlayerDamageMultiplier' => '玩家攻击力 [越大越强且越大死的越快] [建议默认]',
        'PlayerResistanceMultiplier' => '玩家防御力 [注意数字越大玩家越弱] [建议默认]',
        'PlayerCharacterWaterDrainMultiplier' => '玩家口渴度 [越大越快]',
        'PlayerCharacterFoodDrainMultiplier' => '玩家饥饿度 [食物消耗速度 越小越慢]',
        'PlayerCharacterStaminaDrainMultiplier' => '玩家耐力消耗倍数',
        'PlayerCharacterHealthRecoveryMultiplier' => '玩家生命回复速度 [默认一秒回0.2填写100倍就是一秒回20]',
        'DinoDamageMultiplier' => '玩家攻击力 [越大越强且越大死的越快] [建议默认]',
        'TamedDinoDamageMultiplier' => '',
        'DinoResistanceMultiplier' => '恐龙防御力 [越大越弱] [建议默认]',
        'TamedDinoResistanceMultiplier' => '',
        'MaxTamedDinos' => '最大可被驯服的恐龙数量',
        'DinoCharacterFoodDrainMultiplier' => ' 恐龙饥饿度 [食物消耗速度 越小越慢]',
        'DinoCharacterStaminaDrainMultiplier' => '恐龙体力消耗度 [越大越快] [建议默认]',
        'DinoCharacterHealthRecoveryMultiplier' => '恐龙生命回复速度 [越大越快]',
        'DinoCountMultiplier' => '整体恐龙刷新速度 [需要清理一次野生恐龙才会有效]',
        'AllowRaidDinoFeeding' => '允许永久驯服泰坦龙[启用后不能关闭否则服务器可能无法启动]',
        'RaidDinoCharacterFoodDrainMultiplier' => '',
        'DisableDinoDecayPvE' => '禁用恐龙在 PVE 模式自动恢复领养 [启用后恐龙可随意领养]',
        'PvEDinoDecayPeriodMultiplier' => '改变恐龙在 PVE 模式下的恢复乘数 [一个已被驯服恐龙将更快的变为领养模式][填写0恐龙任何人可领养][越大时间越长建议0.1-1]',
        'DisableImprintDinoBuff' => '禁止恐龙获得伤害/抗性buff',
        'AllowAnyoneBabyImprintCuddle' => '允许任何人照顾婴儿',
        'TamingSpeedMultiplier' => '驯服速度 [越大越快]',
        'HarvestAmountMultiplier' => '采集倍数 [数字越大收获越多][警告倍数越高越卡]',
        'ResourcesRespawnPeriodMultiplier' => '资源刷新速度 [越大越慢]',
        'HarvestHealthMultiplier' => '',
        'ClampResourceHarvestDamage' => '一键采集 [启用后所有物品一次采集同时按比例消弱，变态服必开]',
        'DayCycleSpeedScale' => '时间循环速度 ，填写0.038即和现实流速相同',
        'DayTimeSpeedScale' => '白天循环速度 [越大越快] [填写0永远白天]',
        'NightTimeSpeedScale' => '夜晚循环速度 [越大越快] [填写0永远夜晚]',
        'StructureResistanceMultiplier' => '建筑防御力 [越大越弱]  [建议1-5]',
        'StructureDamageMultiplier' => '建筑攻击力 [越大越强] [建议1-5]',
        'PvPStructureDecay' => 'PVP 模式使用建筑自动损毁[玩家离线一段时间后才会开始计时]',
        'NewMaxStructuresInRange' => '',
        'PerPlatformMaxStructuresMultiplier' => '改变移动平台的最大建造乘数[高]',
        'MaxPlatformSaddleStructureLimit' => '改变移动平台的最大建造数 [适用于建造在可移动物品/生物上的建筑][宽]',
        'OverrideStructurePlatformPrevention' => '',
        'PvEAllowStructuresAtSupplyDrops' => '',
        'DisableStructureDecayPVE' => '禁止自动拆除 PvE 模式无主建筑 [启用后建筑可以随意拆除]',
        'PvEStructureDecayDestructionPeriod' => '改变 PVE 建筑自动销毁周期 [越大间隔越长 建议0.1-1]',
        'PvEStructureDecayPeriodMultiplier' => '',
        'AutoDestroyOldStructuresMultiplier' => '自动清除废弃的建筑时间加成',
        'ForceAllStructureLocking' => '自动锁定所有箱子',
        'OnlyAutoDestroyCoreStructures' => '',
        'OnlyDecayUnsnappedCoreStructures' => '[New]启动核心类的建筑腐烂 [可用于清除PvP服务器中荒废孤立的柱子/地基]',
        'FastDecayUnsnappedCoreStructures' => '[New]将 地基/柱子 保护时间减少5倍 [可用于清除PvP服务器中荒废孤立的柱子/地基]',

        // [MultiHome]
        'MultiHome' => '',
*/
        // [/Script/Engine.GameSession]
        //'MaxPlayers' => '最大玩家数',

        // [MessageOfTheDay]
        'Message' => '设置服务器的“当天的消息”，当玩家连接到，将显示给他们',
        'Duration' => '持续时间', 
    ];

    private static function handleHtml($key, $value)
    {
        if (!array_key_exists($key, self::$data) || self::$data[$key] == '') {
            //array_push(self::$miss, $key);
            return '';
        }
        $r = '';
        if (strstr($value, 'True')) {
        $r .= '
        <tr>
            <div class="form-group">
              <label class="col-sm-6 control-label">'.self::$data[$key].'</label>
              <div class="col-sm-6">
                  <select name="'.$key.'" class="form-control">
                      <option value="True" selected="selected">启用</option>
                      <option value="False">禁用</option>
                  </select>
              </div>
            </div>
            </tr>';
        } elseif (strstr($value, 'False')) {
        $r .= '
        <tr>
            <div class="form-group">
              <label class="col-sm-6 control-label">'.self::$data[$key].'</label>
              <div class="col-sm-6">
                  <select name="'.$key.'" class="form-control">
                      <option value="True">启用</option>
                      <option value="False" selected="selected">禁用</option>
                  </select>
              </div>
            </div></tr>';
        } else {
        $r .= '
        <tr>
            <div class="form-group">
              <label class="col-sm-6 control-label">'.self::$data[$key].'</label>
              <div class="col-sm-6">
                <input name="'.$key.'" class="form-control" value="'.$value.'">
              </div>
            </div></tr>';
        }
        return $r;
    }
}
