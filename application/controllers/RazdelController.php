<?php

class RazdelController extends Zend_Controller_Action
{

    public function init()
    {
    
       //$db = Zend_Db_Table::getDefaultAdapter();
//       $profiler = $db->getProfiler();
//       $profiler->setEnabled(true);
       
    }

    public function sayterrorsAction() {
        //die('erorrs');
        return $this->forward("index", "index");
    }
    
    // загрузить статью по скролу
    public function loadarcticleonscrollAction()
    {
        $get = $this->getAllParams();
        $model = new Application_Model_Razdel();
        $str = htmlspecialchars($get['razdel']);
        $offset = $get['begin'];

        if($str) {
            /*Check str on library security */
            $security = new Security_defaults();
            $check = $security->checkRazdelName($str);

            if ($check) {
                $fetchall = $model->fetchall($str, "", "", $offset, 1);
            } else {
                die (Zend_Json::encode(array('status' => 0)));
            }
            
            
            if(count($fetchall)>0) {    
                foreach ($fetchall as $k=>$v) {
                    $count = substr_count($v['m2'], '$')-1;
                    $fetchall[$k]['coments'] = $count;

                }
                $this->view->description = $fetchall[0]['url'];
                $this->view->name = $fetchall[0]['name'];
                $this->view->razdelUrl = $fetchall[0]['url'];
                $this->view->text = $fetchall;
                $this->view->counterPosition = $offset;
                $html = $this->view->render('razdel/loadArcticleOnScroll.phtml');
                
                die (Zend_Json::encode(array('status' => 1,'html' => $html)));
                
            } else {
                
               die (Zend_Json::encode(array('status' => 0)));
               
            }   
        } else {
            die (Zend_Json::encode(array('status' => 0)));
            
        }
        
    }
    
    // загрузить сырую статью
    public function editarticlefrombuttonAction()
    {
        $get = $this->getAllParams();
        $model = new Application_Model_Razdel();
        if (isset($get['id'])) {
            $postId = (int)$get['id'];

            if ($postId > 0) {
                $result = $model->getPostById($postId);
                die (Zend_Json::encode(array('status' => 1, 'html' => $result)));
            } else {
                die (Zend_Json::encode(array('status' => 0)));
            }
        } else {
            die (Zend_Json::encode(array('status' => 0)));
        }
        
    }
    
    // сохранить статью
    public function savearticlefromsaveAction()
    {
        $get = $this->getAllParams();
        $model = new Application_Model_Razdel();
        $postId = (int)$get['id'];
        $data = array();
        $data['m1'] = $get['content'];
        $password = 'lenkapenka';
        if ($postId > 0 && $password === $get['pass']) {
            $model->savePostById($postId, $data);
            die (Zend_Json::encode(array('status' => 1)));
        } else {
            die (Zend_Json::encode(array('status' => 0)));
        }
    }
    
    
    
    
    // показать раздел
    public function indexAction()
    {

        $model = new Application_Model_Razdel();
        $str = htmlspecialchars($this->_getParam('str'));
        //return $this->_forward("index", "index");
        //die(var_dump($str));
        $str_mark = 'razdel';
        
        if($str == 'teg')
        {
            return $this->redirect("/");
        }


        $this->view->info_mark = $str_mark;    

        /*Check str on library security */
        $security = new Security_defaults();
        $check = $security->checkRazdelName($str);

        if ($check) {
            $fetchall = $model->fetchall($str);
        } else {
            $fetchall = array();
        }


        if(count($fetchall)>0)
        {
            foreach ($fetchall as $k=>$v)
            {
                $count = substr_count($v['m2'], '$')-1;
                $fetchall[$k]['coments'] = $count;

            }
            $this->view->description = $fetchall[0]['url'];
            $this->view->name = $fetchall[0]['name'];
            $this->view->razdelUrl = $fetchall[0]['url'];
            $this->view->text = $fetchall;
            $this->render('razdel');
        } else {
           return $this->redirect("/");
        }


        
    }
    
    // показать статью
    public function postsAction()
    {
        $model = new Application_Model_Razdel();
        $razdel_name = htmlspecialchars($this->_getParam('razdelid'));
        $str = htmlspecialchars($this->_getParam('postid'));
        $security_default = new Security_defaults();

        if($razdel_name == 'teg' && in_array($str, $security_default->getDefaultTegs())) {

        	$model = new Application_Model_Razdel();
	        $str_mark = 'razdel';
        	$this->view->info_mark = $str_mark;    
	        if ($str) {
	            
	            $fetchall = $model->fetchall($str, 'teg');
                $tegsName = $model->getTagsNameByTranslit($str);

	            if(count($fetchall) > 0)
	            {    
	                foreach ($fetchall as $k=>$v)
	                {
	                    $count = substr_count($v['m2'], '$')-1;
	                    $fetchall[$k]['coments'] = $count;
	
	                }
	                //$this->view->description = $fetchall[0]['description'];
                    $this->view->razdelUrl = 'teg/'. $str;
	                $this->view->name = $tegsName[0]['teg'];
	                $this->view->text = $fetchall;
	                $this->view->mark = 0;
	                
	            } else {
                    $this->view->mark = 1;
                }
                /* get all tegs with names */
                $tegModel = new Application_Model_Tegs();
                $this->view->tegList = $tegModel->fetchallTegs();
                $this->view->currentTeg = $str;
	            $this->render('razdel');
	        }
	        else {
                $this->redirect('index');
            }
        } else {
            
            if(in_array($str, $security_default->getDefaultPosts())) {
                $cacheId = str_replace('-', '_', $str);

                if (MyCache::getInstance()->test($cacheId)) {
                    $fetchrow = MyCache::getInstance()->load($cacheId);
                } else {
                    $fetchrow = $model->fetchrow($str);
                    MyCache::getInstance()->save($fetchrow, $cacheId);
                }

                if(count($fetchrow)>0) {
                    $this->view->description = $fetchrow[0]['url'];
                    $this->view->name = $fetchrow[0]['name'];
                    $this->view->razdelUrl = $fetchrow[0]['description'];
                    $this->view->text = $fetchrow;
                    $this->view->param = $this->_getParam('post');
                    $this->view->mark = 1;
                    $this->view->postUrl = $fetchrow[0]['m4_urlname'];
                    $this->view->postName = $fetchrow[0]['m4'];
                    $this->view->htmlBlock = $this->getMagazineHtmlBlock($fetchrow[0]['id']);;

                        $fetchall = $model->fetchall($razdel_name,'except',$fetchrow[0]['m4_urlname']);
                        if (count($fetchall)>0) {
                            $i = 0;
                            foreach ($fetchall as $key => $value) {
                                if ($i == 4) {
                                    break;
                                }
                                $resArray[$key] = $value;
                                $i++;
                            }

                        }

                    $this->view->mostPopularBlock = $resArray;

                    $this->render('statiy');
                } else {
                    return $this->redirect("/");
                }

            } else {
                return $this->redirect("/");
            }

        }
    }

    /**
     * Get online shop block with foto and prices taker via curl or memcached(1 day)
     * by article id we take website and options for parsing
     *
     * @param $articleId
     *
     * @return string
     */
    public function getMagazineHtmlBlock($articleId)
    {
        $result = [];
        if ($articleId) {
            $siteData = $this->getEshopContent($articleId);

            $galleryData = $this->getGalleryBlockBySiteData($siteData);

            $this->view->urlData = $siteData;

            if (is_array($galleryData) && count($galleryData) > 0) {
                $textPositionData = [];
                $i = 0;
                usort($galleryData, function($a, $b){
                    return ($a['text_position'] - $b['text_position']);
                });
                foreach ($galleryData as $key => $value) {
                    if ($value['text_position'] == $i) {
                        $textPositionData[] = $value;
                    } else {
                        $this->view->gallery = $textPositionData;
                        $result[] = $this->view->render('razdel/eshop/index.phtml');
                        $textPositionData = [];
                        $textPositionData[] = $value;
                        $i++;
                    }
                }
                $this->view->gallery = $textPositionData;
                $result[] = $this->view->render('razdel/eshop/index.phtml');
            }
        }
        //die(var_dump($result));

        return $result;
    }

    /**
     * Get related eshops by arcticle id
     *
     * @param int $id
     *
     * @return array
     */
    public function getEshopContent($id)
    {
        $result = [];
        if ((int)$id > 0) {
            $model = new Application_Model_Razdel();
            $result = $model->getEshopByPostId($id);
        }

        return $result;
    }

    public function getGalleryBlockBySiteData($data)
    {
        $result = [];
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                if ($value['url'] != null) {
                    $result[] = $this->getRequestByType($value);
                }
            }
        }

        return $result;
    }

    public function getRequestByType($params)
    {
        $url = $params['url'] . $params['url_page'];
        list(,$cacheId) = explode('://', $params['url']);
        $cacheId = str_replace(['.', '-'], '_', $cacheId);
        $cacheId = $cacheId . '_' . $params['id'];

        if (MyCache::getInstance()->test($cacheId)) {
            $resultData = MyCache::getInstance()->load($cacheId);
        } else {
            if ($params['type'] == 1) { /* has razdel */
                $html = file_get_html($url);
                $resultData = $this->parseResult($html, $params);
            } else {
                $resultData[$params['url'] . '_' . $params['id']] = [
                    'block0' => [
                        'siteUrl' => $params['url'],
                        'aUrl' => $params['selector_url'],
                        'imgUrl' => $params['selector_img'],
                        'name' => $params['selector_name'],
                        'price' => $params['selector_price'],
                        'joinImgUrl' => 0,
                        'blockName' => $params['block_name'],
                    ],
                ];
            }
            /* position block on page and line pos */
            $resultData['text_position'] = $params['text_position'];
            $resultData['line_position'] = $params['line_position'];

            MyCache::getInstance()->save($resultData, $cacheId);
        }

        return $resultData;
    }

    public function parseResult($html, $params)
    {
        $selectorUrl = $params['selector_url'];
        $selectorImg = $params['selector_img'];
        $selectorName = $params['selector_name'];
        $selectorPrice = $params['selector_price'];

        $i = 0;
        foreach($html->find($selectorUrl) as $element) {
            $result[$params['url'] . '_' . $params['id']]['block' . $i]['aUrl'] = $element->href;
            $i++;
        }
        $i = 0;
        foreach($html->find($selectorImg) as $element) {
            $result[$params['url'] . '_' . $params['id']]['block' . $i]['imgUrl'] = $element->src;
            $i++;
        }
        $i = 0;
        foreach($html->find($selectorName) as $element) {
            $result[$params['url'] . '_' . $params['id']]['block' . $i]['name'] = $element->innertext;
            $i++;
        }
        $i = 0;
        foreach($html->find($selectorPrice) as $element) {
            if ($params['price_currency']) {
                $result[$params['url'] . '_' . $params['id']]['block' . $i]['price'] = $element->innertext . ' ' . $params['price_currency'];
            } else {
                $result[$params['url'] . '_' . $params['id']]['block' . $i]['price'] = $element->innertext;
            }
            $result[$params['url'] . '_' . $params['id']]['block' . $i]['joinImgUrl'] = 1;
            $result[$params['url'] . '_' . $params['id']]['block' . $i]['siteUrl'] = $params['url'];
            $result[$params['url'] . '_' . $params['id']]['block' . $i]['blockName'] = $params['block_name'];
            $i++;
        }

        return $result;
    }

    // вернуть форму ввода сообщения
    function commentformAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) 
        {
            $cnum = rand(1,999);
            
            $this->view->value = $cnum;
            $html = $this->view->render('razdel/add_comments_form.phtml'); 
            die (Zend_Json::encode(array('status' => 1,'html' => $html, 'c_number' => $cnum)));
        }
    }
    
    
    // добавить комментарий в comments со статусом не модерированый
    function addcommentsAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) 
        {
            $comments = new Application_Model_Comments();
            $from = htmlspecialchars($this->getRequest()->getPost('from'));
            $email = htmlspecialchars($this->getRequest()->getPost('email'));
            $post_id = (int)$this->getRequest()->getPost('postid');
            $post = htmlspecialchars($this->getRequest()->getPost('text'));
            
            
            $data = array(
                'post_id' => $post_id,
                'from_email'=>$email,
                'from' => $from,
                'post' => $post,
                'date' => time(),
                'status' => 0
                );
            $comments->insert($data);
            
            die (Zend_Json::encode(array('status' => 1)));
        }
    }
    
    // содержимое тега
    function showtegspostsAction()
    {
        if($this->getRequest()->isXmlHttpRequest())
        {
            $teg_id = (int)$this->getRequest()->getPost('teg');
            $model = new Application_Model_Razdel();
            $posts = $model->fetchallbyteg($teg_id);
            
            $this->view->postsbyteg = $posts;
            $html = $this->view->render('razdel/tegs_posts.phtml');
            die (Zend_Json::encode(array('html'=>$html)));
        }
    }
}

