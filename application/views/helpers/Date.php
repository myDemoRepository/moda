<?php

class My_Helper_Date extends Zend_View_Helper_Abstract {
    public function date() 
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
                    $res = date('Y-m-d\TH:i:sO');
                    if ($check) {
                        $model = new Application_Model_Razdel();
	                $fetchrow = $model->fetchtitlerow($data_mas[0]);
                        $res = date('Y-m-d\TH:i:sO', $fetchrow[0]['m3']);
	                
                    }
	                return $res;
                }
                else
                {
                	return date('Y-m-d\TH:i:sO');
                }
                
            }
            elseif (count($data_mas) == 2) // статья
            {
                $security = new Security_defaults();
                $check = $security->checkPostName($data_mas[1]);
                $res = date('Y-m-d\TH:i:sO');

                if ($check) {
                    $model = new Application_Model_Razdel();
                    $fetchrow = $model->fetchrow($data_mas[1]);
                    $res = date('Y-m-d\TH:i:sO', $fetchrow[0]['m3']);
                }
                
                return $res;
            }
        }
        else // главная страница 
        {
            return date('Y-m-d\TH:i:sO');
        }
        
       
        
     }
}