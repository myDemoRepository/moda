<?php

class Application_Model_Razdel
{
    protected $object = null;

    function init()
    {

        $this->_db = Zend_registry::get('dbAdapter');
    }

    function __construct()
    {

        $this->object = new Application_Model_DbTable_Razdels;
    }


    function savePostById($id, $data)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "UPDATE `statyi`
               SET `statyi`.`m1` = '" . $data['m1'] . "'
               WHERE `statyi`.`id`= '" . (int)$id . "' ";
        $db->query($sql);
    }

    function getPostById($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT `statyi`.*
               FROM `statyi`
               WHERE `statyi`.`id`= '" . (int)$id . "' ";

        return $db->query($sql)->fetchall();
    }


    function insert($data)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "INSERT INTO `statyi` 
            (m0,m1,m2,m3,m4,m4_urlname,m5,tegs_id,m6,m7,m8,m9,m10,status) 
            VALUES
            ('" . $data['m0'] . "','" . $data['m1'] . "','" . $data['m2'] . "','" . $data['m3'] . "','" . $data['m4'] . "','" . $data['m4_urlname'] . "','" . $data['m5'] . "','" . $data['tegs_id'] . "','" . $data['m6'] . "','" . $data['m7'] . "','" . $data['m8'] . "','" . $data['m9'] . "','" . $data['m10'] . "','" . $data['status'] . "')";

        $db->query($sql);
    }

    function fetchrowallposts($param, $not_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT `statyi`.*,`razdels_info`.`id` as num, `razdels_info`.`name`, `razdels_info`.`url` 
               FROM `statyi`,`razdels_info` 
               WHERE `statyi`.`m6`=`razdels_info`.`id` 
               AND `statyi`.`tegs_id` LIKE '%;" . $param . ";%' 
               AND `statyi`.`id` <> " . $not_id . "
                ";

        return $db->query($sql)->fetchall();

    }


    function fetchrowmap($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");

        $sql = "SELECT `statyi`.* 
              FROM `statyi`
              WHERE `statyi`.`m6`= '" . $id . "'
              AND `statyi`.`status` = 1    
              ";

        return $db->query($sql)->fetchAll();
    }

    function fetchmap()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");

        $sql = "SELECT `razdels_info`.`*`  
              FROM `razdels_info` 
              ";

        return $db->query($sql)->fetchAll();
    }

    function fetchtitlerow($param)
    {

        $db = Zend_Db_Table::getDefaultAdapter();
        //die(var_dump($db));
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT `razdels_info`.*  
               FROM `razdels_info` 
               WHERE `razdels_info`.`url` =  '" . $param . "' ";

        return $db->query($sql)->fetchall();

    }


    function fetchall($str, $param = "", $postName = "", $offset = 0, $limit = 3)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");

        if ($param == "") {

            $sql = "SELECT `statyi`.*,`razdels_info`.`id` as num,`razdels_info`.`name`,`razdels_info`.`url` 
	              FROM `statyi`,`razdels_info` 
	              WHERE `statyi`.`m6`=`razdels_info`.`id` 
	              AND `razdels_info`.`url` = '" . $str . "'
                      AND `statyi`.`status` = 1
	              ORDER BY `m3` DESC
                      LIMIT " . (int)$offset . " , " . (int)$limit . ";
	              ";
        }

        /* get all posts on radel filters */
        if ($param == "razdel") {
            $razdenName = $str['razdel'];
            $razdelFilters = $str['filters'];
            $sql = "";
        }

        if ($param == "teg") {
            $sql = "SELECT `statyi`.*,`razdels_info`.`id` as num,`razdels_info`.`name`,`razdels_info`.`url`
	               FROM `statyi`,`razdels_info` 
	               WHERE `statyi`.`m6` =  `razdels_info`.`id`
	               AND `statyi`.`tegs_id` LIKE '%;" . $str . ";%'
                       AND `statyi`.`status` = 1    
	               ORDER BY `m3` DESC
	               ";

        }

        /* Отдаем все статьи из текущего раздела кроме 1 */
        if ($param == "except") {
            $sql = "SELECT `statyi`.*,`razdels_info`.`id` as num,`razdels_info`.`name`,`razdels_info`.`url` 
	              FROM `statyi`,`razdels_info` 
	              WHERE `statyi`.`m6`=`razdels_info`.`id` 
	              AND `razdels_info`.`url` = '" . $str . "'
                      AND `statyi`.`status` = 1
                      AND `statyi`.`m4_urlname` <> '{$postName}'
	              ORDER BY `m3` DESC
	              ";

        }

        $result = $db->query($sql)->fetchAll();

        return $result;

    }

    function getTagsNameByTranslit($teg)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT * FROM `new_tegi` WHERE `teg_name` = '" . $teg . "';";
        $res = $db->query($sql)->fetchAll();

        return $res;
    }

    function fetchallpost($map = "")
    {

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        if ($map == "") {
            $sql = "SELECT `statyi`.*,`razdels_info`.`id` as num,`razdels_info`.`name`,`razdels_info`.`url` 
              FROM `statyi`,`razdels_info` 
              WHERE `statyi`.`m6`=`razdels_info`.`id` 
              AND `statyi`.`status` = 1
              ORDER BY `m3` DESC
             ";
        } else {
            $sql = "SELECT `statyi`.`id` as id,`statyi`.`m0` as m0,`statyi`.`m4` as m4,`statyi`.`m4_urlname` as m4_urlname,
              `razdels_info`.`id` as num,`razdels_info`.`name`,`razdels_info`.`url` 
              FROM `statyi`,`razdels_info` 
              WHERE `statyi`.`m6`=`razdels_info`.`id` 
              AND `statyi`.`status` = 1
              ORDER BY `m3` DESC
              
             ";
        }

        return $db->query($sql)->fetchAll();

    }


    /**
     * Function returns one row
     * @param string
     * @return result
     */

    function fetchrow($param)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT `statyi`.*,`razdels_info`.`id` as num, `razdels_info`.`name`, `razdels_info`.`url` 
               FROM `statyi`,`razdels_info` 
               WHERE `statyi`.`m6`=`razdels_info`.`id` 
               AND `statyi`.`m4_urlname` = '" . $param . "'
               AND `statyi`.`status` = 1 ";

        return $db->query($sql)->fetchall();

    }

    function newposts()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT `statyi`.`id`,`statyi`.`m4` as name ,`razdels_info`.`name` as razdel,
               `razdels_info`.`id` as r_id, `statyi`.`m1` as text, `statyi`.`m0` as img, 
               `statyi`.`m4_urlname` as href, `razdels_info`.`url` as razdel_url,
               `statyi`.`m3` as time
               FROM `statyi`,`razdels_info` 
               WHERE `statyi`.`m6` =  `razdels_info`.`id`
               AND `statyi`.`status` = 1
               ORDER BY `m3` DESC
               LIMIT 10";
        return $db->query($sql)->fetchall();
    }


    function searchPostName($param)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT `statyi`.`id`,`statyi`.`m4` as name ,`razdels_info`.`name` as razdel,
               `razdels_info`.`id` as r_id, `statyi`.`m1` as text, `statyi`.`m0` as img, 
               `statyi`.`m4_urlname` as href, `razdels_info`.`url` as razdel_url,
               `statyi`.`m3` as time
               FROM `statyi`,`razdels_info` 
               WHERE `statyi`.`m6` =  `razdels_info`.`id`
               AND `statyi`.`status` = 1
               AND `statyi`.`m4` LIKE '%" . $param . "%'
               ORDER BY `m3` ASC
               LIMIT 10";
        return $db->query($sql)->fetchall();
    }

    // вернуть все статьи с данным тегом
    function fetchallbyteg($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT `statyi`.*,`razdels_info`.`id` as num,`razdels_info`.`name`,`razdels_info`.`description`
               FROM `statyi`,`razdels_info` 
               WHERE `statyi`.`m6` =  `razdels_info`.`id`
               AND `statyi`.`tegs_id` LIKE '%;" . $id . ";%'
               AND `statyi`.`status` = 1
               ORDER BY `m3` ASC
               ";
        //die($sql);       
        return $db->query($sql)->fetchall();
    }

    public function getEshopByPostId($id)
    {
        $result = [];
        if ($id > 0) {
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->query("SET NAMES 'utf8'");
            $sql = "SELECT `statyi`.`id` AS `arcticle_id`, "
                . "`eshops`.`id`, `eshops`.`url`, `eshops`.`url_page` , `eshops`.`selector_img`, `eshops`.`selector_name`,"
                . "`eshops`.`type`, `eshops`.`block_name`, `eshops`.`text_position`, `eshops`.`line_position`,"
                . "`eshops`.`selector_price`, `eshops`.`price_currency`, `eshops`.`selector_url`,  `eshops`.`update_time`"
                . "FROM `statyi`"
                . "LEFT JOIN `post_shops` ON `statyi`.`id` = `post_id`"
                . "LEFT JOIN `eshops` ON `post_shops`.`shop_id` = `eshops`.`id`"
                . "WHERE `statyi`.`id` = '" . (int)$id . "'";

            $result = $db->query($sql)->fetchall();
        }

        return $result;
    }

    public function getPostsByRazdelName($name)
    {
        $result = '';
        if ($name) {
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->query("SET NAMES 'utf8'");
            $sql = "SELECT `statyi`.`id`, `statyi`.`m0`  "
                . "FROM `statyi`"
                . "LEFT JOIN `razdels_info` on `statyi`.`m6` = `razdels_info`.`id`"
                . "WHERE `razdels_info`.`url` = '" . $name . "'"
                . "ORDER BY `statyi`.`id` DESC "
                . "LIMIT 1";

            $result = $db->query($sql)->fetchall();
        }

        return $result;
    }

}