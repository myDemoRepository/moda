<?php

class My_Helper_Keywords extends Zend_View_Helper_Abstract {
    public function keywords() 
    {
        $uri_param = $_SERVER['REQUEST_URI'];
        //return 'good';
	        
     	$uri_mas = explode('/', $uri_param);
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
                    $res = 'сайт о моде, женская мода, женственная мода, модный сайт';
                    if ($check) {
	                $model = new Application_Model_Razdel();
	                $fetchrow = $model->fetchtitlerow($data_mas[0]);
                        $res = $fetchrow[0]['seo_keywords'];
                    }
	                return $res;
                }
                else
                {
                	return "сайт о моде, женская мода, женственная мода, модный сайт";
                }
            }
            elseif (count($data_mas) == 2) // статья
            {
                $security = new Security_defaults();
                $check = $security->checkPostName($data_mas[1]);
                $res = 'сайт о моде, женская мода, женственная мода, модный сайт';
                if ($check) {
                    $model = new Application_Model_Razdel();
                    $fetchrow = $model->fetchrow($data_mas[1]);
                    $res = $fetchrow[0]['m9'];
                    
                }
                return $res;
            }
        }
        else // главная страница 
        {
            return "сайт о моде, женская мода, женственная мода, модный сайт";
        }
        
       
        
     }
}