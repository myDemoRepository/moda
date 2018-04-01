<?php

class My_Helper_Title extends Zend_View_Helper_Abstract {

    public function title() 
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
                    $res = 'Женственная мода';
                    if ($check) {
                        $model = new Application_Model_Razdel();
                        $fetchrow = $model->fetchtitlerow($data_mas[0]);
                        $res = $fetchrow[0]['name'];
                    }
	                return $res;
	         }
	         else
	         {
	         	return "Женственная мода";
	         }       
            }
            elseif (count($data_mas) == 2) // статья
            {
                $security = new Security_defaults();
                $check = $security->checkPostName($data_mas[1]);
                $res = 'Женственная мода';
                if ($check) {
                    $model = new Application_Model_Razdel();
                    $fetchrow = $model->fetchrow($data_mas[1]);
                    $res = $fetchrow[0]['m7'];
                }
                

                return $res;
            }
            
        }
        else
        {
            return "Женственная мода";
        }     
       
        
     }
}