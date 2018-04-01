<?php

class My_Helper_Samestatiy extends Zend_View_Helper_Abstract {
    public function samestatiy() 
    {
        $model = new Application_Model_Razdel();
        $security_default = new Security_defaults();
        
        $uri_param = $_SERVER['REQUEST_URI'];
        
        $uri_mas = explode('/', $uri_param);
        if ($uri_mas[2] != '' && $uri_mas[3] != '') {
            
            $razdelName = $uri_mas[2];
            $postName = $uri_mas[3];
           
        } else {
            return ;
        }   
        // собираем данные о похожих статьях 3 штуки
        
        if(
                isset($postName) && isset($razdelName) &&
                in_array($postName, $security_default->getDefaultPosts()) && 
                in_array($razdelName, $security_default->getDefaultRazdel()) )
        {
            $fetchrow = $model->fetchrow($postName);  
            
            $not_id = $fetchrow[0]['id'];
            $tegs_mas = explode(";",$fetchrow[0]['tegs_id']);
            unset($tegs_mas[0]);
            unset($tegs_mas[count($tegs_mas)]);
            sort($tegs_mas);

            $random_teg = $tegs_mas[rand(0,count($tegs_mas)-1)];

            $post_mas = $model->fetchrowallposts($random_teg,$not_id);
            while(count($post_mas) == 0)
            {
                $random_teg = $tegs_mas[rand(0,count($tegs_mas))];
                $post_mas = $model->fetchrowallposts($random_teg,$not_id);
            }

            $this->view->result = $post_mas;    

            $html = $this->view->render('razdel/samestatiy.phtml');
            return $html;
        } else {
            return ;
        }
            
        
     }
}