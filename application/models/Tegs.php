<?php

class Application_Model_Tegs
{
    protected $object = null;
    function init()
    {
       
    }
    function __construct()
    {
          $this->object = new Application_Model_DbTable_Tegs;
    }
    function fetchall()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql="SELECT tegs_id FROM `statyi`";
      
        return $db->query($sql)->fetchAll();

    }
    function fetchallTegs()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql="SELECT * FROM `new_tegi`";

        return $db->query($sql)->fetchAll();

    }
    
    
}