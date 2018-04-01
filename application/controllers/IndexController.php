<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        MyCache::getInstance();
    }

    function cleanmemcacheformeAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            MyCache::cleanAllCache();
            die(Zend_Json::encode(['status' => 1]));
        } else {
            die(Zend_Json::encode(['status' => 0]));
        }
    }

    // рендер 
    function zxoelfkdjcAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {

            // пасс сетингс
            $login_set = 1;
            $pass_set = 1;
            $id_set = 1;

            $login = $this->getRequest()->getPost('l');
            $pass = $this->getRequest()->getPost('p');
            $id = (int) $this->getRequest()->getPost('i');

            if ($login == $login_set && $pass == $pass_set && $id == $id_set) {
                // список разделов
                $razdels = new Application_Model_Razdelsinfo();
                $res = $razdels->fetchallrazdels();
                $result = array();
                foreach ($res as $k => $v) {
                    $result[$v['id']] = $v['name'];
                }
                $this->view->razdels_mas = $result;

                $html = $this->view->render('index/admin.phtml');
                die(Zend_Json::encode(array('status' => 1, 'html' => $html)));
            } else
                die(Zend_Json::encode(array('status' => 2, 'html' => 'попытка взлома ... робот уже выслан ... беги ...')));
        } else
            $this->render('login');
    }

    // сохрание поста
    function savepostAction()
    {

        if ($this->getRequest()->isXmlHttpRequest()) {
            $razdel_id = (int) $this->getRequest()->getPost('razdels_info');
            $foto_prev = htmlspecialchars($this->getRequest()->getPost('img_name'));
            $post_name = htmlspecialchars($this->getRequest()->getPost('h1_post'));
            $post_body = htmlspecialchars($this->getRequest()->getPost('body_post'));
            $post_tegs = htmlspecialchars($this->getRequest()->getPost('teg_post'));
            $post_desc = htmlspecialchars($this->getRequest()->getPost('desc'));
            $post_keys = htmlspecialchars($this->getRequest()->getPost('keys'));

            // переводим в транслит имя статьи
            $translite = new Security_defaults();
            $string = trim($translite->GetInTranslit($post_name));
            $string = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/", "", $string);
            $string = preg_replace('| +|', ' ', $string);
            $pattern = "/\s/";
            $replacement = "-";
            $post_tr_name = preg_replace($pattern, $replacement, $string);

            // работаем с тегами к статье
            $teg_string = trim($post_tegs);
            $teg_mas = explode(',', $teg_string);

            foreach ($teg_mas as $k => $v) {
                $string_t = trim($translite->GetInTranslit($v));
                $string_t = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/", "", $string_t);
                $string_t = preg_replace('| +|', ' ', $string_t);
                $teg_mas[$k] = preg_replace($pattern, $replacement, $string_t);
            }
            $tegs_convert = ';' . implode(';', $teg_mas) . ';';

            // собираем данные для добавления
            $posts = new Application_Model_Razdel();
            $data = array(
                'm0' => $post_tr_name . '/' . $foto_prev . 'm.jpg',
                'm1' => $post_body,
                'm2' => '',
                'm3' => time(),
                'm4' => $post_name,
                'm4_urlname' => $post_tr_name,
                'm5' => $post_tegs,
                'tegs_id' => $tegs_convert,
                'm6' => $razdel_id,
                'm7' => $post_name,
                'm8' => $post_desc,
                'm9' => $post_keys,
                'm10' => 'да',
                'status' => 0
            );
            $posts->insert($data);

            // имя раздела    
            $razdels = new Application_Model_Razdelsinfo();
            $res = $razdels->info($razdel_id);

            die(Zend_Json::encode(array('status' => 1, 'razdel' => $res['description'], 'post' => $post_tr_name)));
        } else
            die(Zend_Json::encode(array('status' => 2)));
    }

    public function indexAction()
    {
        $model = new Application_Model_Razdel();
        $fetchall = $model->newposts();
        if (count($fetchall) > 0) {
            $postBlock1 = array();
            $postBlock2 = array();

            for ($i = 0; $i < 6; $i++) {
                $postBlock1[] = $fetchall[$i];
            }
            for ($i = 6; $i < 10; $i++) {
                $postBlock2[] = $fetchall[$i];
            }
        }
        $this->view->postsBlock1 = $postBlock1;
        $this->view->postsBlock2 = $postBlock2;


    }

    public function poststegshowAction()
    {
        die('ttesstt');
    }

    public function searchAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $model = new Application_Model_Razdel();
            $param = $this->getRequest()->getPost('search');
            $pattern = '/^[\sа-яёa-z0-9-]+$/iu';

            if (preg_match($pattern, $param) && $param != '') {
                $fetchall = $model->searchPostName($param);
                $this->view->searchresult = $fetchall;
                $count = count($fetchall);
                $html = $this->view->render('index/search.phtml');
                die(Zend_Json::encode(array('status' => 1, 'html' => $html, 'count' => $count)));
            } else {
                die(Zend_Json::encode(array('status' => 0)));
            }
        }
    }

    /**
     * Get info by razdel filters in request
     */
    public function filtersAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $model = new Application_Model_Razdel();
            $library = new Security_defaults();
            $param = $this->getRequest()->getPost('filter');
            $currentRazdel = $library->getCurrentRazdelAndPostName();
            if (isset($currentRazdel['razdelUrl']) && $currentRazdel['razdelUrl']) {
                $razdelFilters = $library->getCurrentRazdelTags($currentRazdel['razdelUrl']);
                $razdelFiltersFormatted = [];
                foreach ($razdelFilters as $key => $value) {
                    $razdelFiltersFormatted[] = $value['tagUrl'];
                }
            }

            $securedFilters = [];
            if (is_array($param) && count($param)) {
                foreach ($param as $key => $value) {
                    if (in_array($value, $razdelFiltersFormatted)) {
                        $securedFilters[] = $value;
                    }
                }
            }

            ////
            $offset = 0;
            $data = [
                'razdel' => $currentRazdel,
                'filters' => $securedFilters,
            ];

            $fetchall = $model->fetchall($data, "razdel", "", $offset, 1);
            $countP = count($fetchall);
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

            ////



            //$posts = $model->searchPostsByFilters($securedFilters);
            //$this->view->posts = $posts;
            //$count = count($posts);

            if (false) {

                die(Zend_Json::encode(array('status' => 1, 'html' => $html, 'count' => $countP)));
            } else {
                die(Zend_Json::encode(array('status' => 0)));
            }
        }
    }
}
