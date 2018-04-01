<?php

class My_Helper_GalleryEshopHtml extends Zend_View_Helper_Abstract {

    public function galleryEshopHtml($galleryBlock)
    {
        $result = '';
        if (is_array($galleryBlock)) {
            $html = '';
            $i = 0;
            $last = count($galleryBlock);
            foreach ($galleryBlock as $key => $value) {

                $joinImgUrl = $value['joinImgUrl'];/* 1 - разделы коеим урл; 0 - кастомные фотки не клеим урл*/
                $aUrl = ($joinImgUrl) ? $this->joinUrl($value['siteUrl'], $value['aUrl']) : $value['aUrl'];
                $imgUrl = ($joinImgUrl) ? $this->joinUrl($value['siteUrl'], $value['imgUrl']) : $value['imgUrl'];
                if (mb_detect_encoding($value['name'], ['UTF-8', 'Windows-1251']) != 'UTF-8') {
                    $nameDecoded = mb_convert_encoding($value['name'], "UTF-8", "windows-1251");
                } else {
                    $nameDecoded = $value['name'];
                }

                $name =  $nameDecoded . ' - ' . $value['price'];

                if ($i == 0) {
                    $html .= "<a class='eshop' siteurl='"
                        . $aUrl . "' title='" . $name . "' href='" . $imgUrl . "'>"
                        . "<img src='" . $imgUrl . "' alt=''/>"
                        . "<span class='my-span-price'>" . $value['price'] . "</span>"
                        . "</a>"
                        . "<div class='hidden-eshop'>";
                }
                if ($i > 0) {
                    $html .= "<a class='eshop' siteurl='" . $aUrl . "' title='" . $name . "' href='" . $imgUrl . "'></a>";
                }
                if ($i == $last - 1) {
                    $html .= "</div>";
                }
                $i++;
            }

            $result = $html;
        }

        return $result;
    }


    public function joinUrl($url1 = null, $url2 = null)
    {
        if (mb_substr($url1, -1) === '/') {
            $url1 = mb_substr($url1, 0, -1);
        }

        if (mb_substr($url2, 0, 1) === '/') {
            $url2 = mb_substr($url2, 1);
        }

        if (mb_substr($url2, 0, 4) === 'http') {
            $result = $url2;
        } else {
            $result = $url1 . '/' . $url2;
        }

        return $result;
    }
}