<?php

class My_Helper_PostImage extends Zend_View_Helper_Abstract {

    public function postImage($url)
    {
        $result = '';

        $data = explode('/', $url);
        $count = count($data);
        switch ($count) {
            case 2:/* main page */
                $result = 'http://jenstvenayamoda.ru/public/image/mainbanner/11.jpg';
                break;

            case 3:/* teg or razdel */
                $url = $data[1];
                $security_default = new Security_defaults();
                $model = new Application_Model_Razdel();
                if (in_array($url, $security_default->getDefaultRazdel())) {
                    $cacheId = str_replace('-', '_', $url);

                    if (MyCache::getInstance()->test($cacheId)) {
                        $fetchrow = MyCache::getInstance()->load($cacheId);
                    } else {
                        $fetchrow = $model->getPostsByRazdelName($url);
                        MyCache::getInstance()->save($fetchrow, $cacheId);
                    }
                    $result = 'http://jenstvenayamoda.ru/public/image/statyi/' . $fetchrow[0]['m0'];
                } else {
                    $result = 'http://jenstvenayamoda.ru/public/image/mainbanner/11.jpg';
                }
                break;

            case 4:/* post only */
                $postUrl = $data[2];
                $security_default = new Security_defaults();
                $model = new Application_Model_Razdel();
                if(in_array($postUrl, $security_default->getDefaultPosts())) {
                    $cacheId = str_replace('-', '_', $postUrl);

                    if (MyCache::getInstance()->test($cacheId)) {
                        $fetchrow = MyCache::getInstance()->load($cacheId);
                    } else {
                        $fetchrow = $model->fetchrow($postUrl);
                        MyCache::getInstance()->save($fetchrow, $cacheId);
                    }
                    $result = 'http://jenstvenayamoda.ru/public/image/statyi/' . $fetchrow[0]['m0'];
                }
                break;
        }

        return $result;
    }

}