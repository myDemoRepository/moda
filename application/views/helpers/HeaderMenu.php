<?php

class My_Helper_HeaderMenu extends Zend_View_Helper_Abstract {

    public function headerMenu()
    {
        $view = new Zend_View();
        $view->setScriptPath(APPLICATION_PATH . '/views/scripts/index/');
        $view->menu = [];
        $class = new Security_defaults();

        $result = $class->getHeaderMenu();
        if (is_array($result)) {
            $rawData = [];
            $rawSubData = [];
            foreach ($result as $key => $value) {
                $rawData[$value['url']] = $value;
                if ($value['sub_name']) {
                    $rawSubData[$value['url']][] = [
                        'sub_name' => $value['sub_name'],
                        'sub_url' => $value['sub_url'],
                        'sub_seo_description' => $value['sub_seo_description'],
                        'sub_seo_keywords' => $value['sub_seo_keywords'],
                    ];
                }
            }
        }
        $formattedData = [];
        foreach ($rawData as $key => $value) {
            if (is_array($rawSubData[$value['url']])) {
                $formattedData[$value['url']] = [
                        'name' => $value['name'],
                        'url' => $value['url'],
                        'seo_description' => $value['seo_description'],
                        'seo_keywords' => $value['seo_keywords'],
                        'sub' => $rawSubData[$value['url']]
                    ];
            } else {
                $formattedData[$value['url']] = [
                    'name' => $value['name'],
                    'url' => $value['url'],
                    'seo_description' => $value['seo_description'],
                    'seo_keywords' => $value['seo_keywords'],
                ];
            }
        }

        $view->menu = $formattedData;
        $result = $view->render('headerMenu.phtml');

        return $result;
    }
}