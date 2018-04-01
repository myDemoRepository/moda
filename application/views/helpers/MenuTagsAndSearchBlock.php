<?php

class My_Helper_MenuTagsAndSearchBlock extends Zend_View_Helper_Abstract {

    public function menuTagsAndSearchBlock()
    {
        $lib = new Security_defaults();
        $info = $lib->getCurrentRazdelAndPostName();

        $view = new Zend_View();
        $view->setScriptPath(APPLICATION_PATH . '/views/scripts/index/');
        $view->info = $info;
        if (isset($info['razdelUrl']) && $info['razdelUrl']) {
            $tagsList = $lib->getCurrentRazdelTags($info['razdelUrl']);
            $view->tagsList = $tagsList;
        }


        $result = $view->render('menuTagsAndSearchBlock.phtml');

        return $result;
    }
}