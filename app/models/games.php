<?php
namespace models;

use core\model;

class games extends model
{
    public static $gameName = [
        'ark' => '方舟',
    ];
    
    public static function getOwnerById($id)
    {
        $r = games::select(['owner'], ['id' => $id]);
        return $r[0]['owner'];
    }
}
