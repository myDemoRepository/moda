<?php

class Application_Model_Razdelsinfo 
{
    protected $object = null;
    function init()
    {
    
       $this->_db = Zend_registry::get('dbAdapter'); 
    }
    function __construct()
    {
    	
          $this->object = new Application_Model_DbTable_Razdelsinfo;
    }
    
    function fetchallrazdels()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT * FROM `razdels_info`";
        return $db->query($sql)->fetchall();
        
    }
    
    function info($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT `description` FROM `razdels_info` WHERE `id` = ".$id;
        return $db->query($sql)->fetch();
    }
   
}