<?php

class Application_Model_Comments
{
    protected $object = null;
    function init()
    {
       
    }
    function __construct()
    {
          $this->object = new Application_Model_DbTable_Comments;
    }
    
    function insert($data)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $db->insert('comments', $data);
    }
    
    // покажи все коментарии к статье (модерированые)
    function fetchall($post_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql="SELECT * FROM `comments`  
              WHERE `post_id` = '".$post_id."'
              AND `status` = '1'    
             ";
        return $db->query($sql)->fetchAll();
        
    }
    
    // покажи все коментарии к 
    function show_unedit($post_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql="SELECT * FROM `comments`  
              WHERE `post_id` = '".$post_id."'
              AND `status` = '0'    
             ";
        return $db->query($sql)->fetchAll();
        
    }
    
}