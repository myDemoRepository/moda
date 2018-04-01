<?php

class My_Helper_MetaSeo extends Zend_View_Helper_Abstract {

    public function metaseo($url)
    {


        $result =  false;
        $regexp = '/(\/teg\/)/';

        if (preg_match($regexp, $url)) {
            $result = true;
        }

        $security = new Security_defaults();
        $postsNames = $security->getDefaultPosts();
        $flag = 0;
        foreach ($postsNames as $key => $value) {
            if (strpos($url, $value) !== false) {
                $flag = 1;
                break;
            }
        }
        if ($flag == 0) {
            $result = true;
        }

        if ($url == '/') {
            $result = false;
        }

        return $result;
    }
}