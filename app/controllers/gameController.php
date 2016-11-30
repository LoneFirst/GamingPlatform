<?php
namespace controllers;

use models\users;
use models\keys;
use models\games;

class gameController
{
    private static $gamePath = [
        'ark' => '',
    ];

    public function create()
    {
        if (!users::auth()) {
            return;
        }
        $user = $_SESSION['user'];
        $game = $_POST['game'];
        $c = count(games::select(['id'], ['1' => '1']));
        //self::cp($gamePath['ark'], /*path*/);
        games::create(['game' => $game, 'owner' => $user]);
        echo 'success';
    }

    private static function cp($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::cp($src . '/' . $file,$dst . '/' . $file);
                } else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
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
        $view = view('home', ['section' => '管理']);
        if ($user != games::getOwnerById($gameId)) {
            redirect(FILE_PATH);
        }
        $game = games::select(['id', 'game', 'time', 'limit'], ['id' => $gameId])[0];
        if ($game['time'] < time()) {
            $game['time'] = '已到期';
        } else {
            $game['time'] = date('Y-m-d h:i:s', $value['time']);
        }
        $game['game'] = games::$gameName[$game['game']];
        $view->push('game', $game);

        $h = fopen('C:\xampp\htdocs\tmp\GameUserSettings.ini', 'rb');
        $c = '';
        while(!feof($h)) {
            $line = fgets($h);
            if (!strstr($line, '=')) {
                continue;
            }
            $t = explode('=', $line);
            $c .= self::handleHtml($t[0], $t[1]);
        }
        fclose($h);

        $view->push('managePageHtml', $c);
        $view->render();
    }

    public function change()
    {
        if (!users::auth()) {
            redirect(FILE_PATH);
        }
        $h = fopen(self::$temp, 'rb');
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
        file_put_contents($file, $r);
        echo '<script>history.go(-1)</script>';
    }

    public static $data = [
        // [SessionSettings]
        'SessionName' => '服务器名称',
        'Port' => '服务器源端口',
        'QueryPort' => '目标端口',

        // [ServerSettings]
        'ServerPassword' => '服务器密码',
        'ServerAdminPassword' => '管理员密码',
        'SpectatorPassword' => '观察者密码',
        'RCONEnabled' => '启用RCON端口',
        'RCONPort' => 'RCON端口',
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

        // [/Script/Engine.GameSession]
        'MaxPlayers' => '最大玩家数',

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
