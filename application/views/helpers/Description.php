<?php

class My_Helper_Description extends Zend_View_Helper_Abstract {
    public function description() 
    {
        $uri_param = $_SERVER['REQUEST_URI'];
        
        $uri_mas = explode('/', $uri_param);
        //return var_dump($uri_mas);
        $last = count($uri_mas)-1;
        unset($uri_mas[0]);
        
        unset($uri_mas[$last]);
      
        if(count($uri_mas) > 0)
        {
            foreach ($uri_mas as $val)
            {
                $data_mas[] = $val;
            }

            if (count($data_mas) == 1) // раздел
            {
                if($data_mas[0] != 'map')
                { 
	            $security = new Security_defaults();
                    $check = $security->checkRazdelName($data_mas[0]);
                    $res = 'Самый полезный сайт о моде! Лучшие фото женственных платьев, юбок, блузок, свадебных и вечерних платьев, интересные подборки и лучшие коллекции специально для вас!';
                    if ($check) {
                        $model = new Application_Model_Razdel();
	                $fetchrow = $model->fetchtitlerow($data_mas[0]);
                        $res = $fetchrow[0]['seo_description'];
	                
                    }
	                return $res;
                }
                else
                {
                	return "Самый полезный сайт о моде! Лучшие фото женственных платьев, юбок, блузок, свадебных и вечерних платьев, интересные подборки и лучшие коллекции специально для вас!";
                }
                
            }
            elseif (count($data_mas) == 2) // статья
            {
                $security = new Security_defaults();
                $check = $security->checkPostName($data_mas[1]);
                $res = 'Самый полезный сайт о моде! Лучшие фото женственных платьев, юбок, блузок, свадебных и вечерних платьев, интересные подборки и лучшие коллекции специально для вас!';

                if ($check) {
                    $model = new Application_Model_Razdel();
                    $fetchrow = $model->fetchrow($data_mas[1]);
                    $res = $fetchrow[0]['m8'];
                }
                
                return $res;
            }
        }
        else // главная страница 
        {
            return "Самый полезный сайт о моде! Лучшие фото женственных платьев, юбок, блузок, свадебных и вечерних платьев, интересные подборки и лучшие коллекции специально для вас!";
        }
        
       
        
     }
}