<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }
     
    public function _initRoute() {
        
        // Получаем маршрут, по-умолчанию
        $router = Zend_Controller_Front::getInstance()->getRouter();

        // paginator
        $pagin = new Zend_Controller_Router_Route(
                      '/:page/*',
                      array(
                          'controller' => 'index',
                          'action' => 'index',
                      ),
                      array(
                          'page' => '[0-9]+'
                      )
        );
        $router->addRoute('paginator2', $pagin);

        $route_errors1 = new Zend_Controller_Router_Route_Regex(
                '[\;\!\@\#\$\%\^\&\*\(\)\\\/a-z0-9\-\~\)\(]+',
                array(
                    'controller' => 'razdel',
                    'action' => 'sayterrors'
                )
        );
        $router->addRoute('razdel_erorrs', $route_errors1);
        
        // errors 2 post
        $route_errors2 = new Zend_Controller_Router_Route_Regex(
                '[\;\!\@\#\$\%\^\&\*\(\)\\\/a-z0-9\-\~\)\(]+',
                array(
                    'controller' => 'razdel',
                    'action' => 'sayterrors'
                )
        );
        $router->addRoute('post_erorrs', $route_errors2);
        
        // маршрут для "постов" //
        $route_posts = new Zend_Controller_Router_Route_Regex(
                '([a-z0-9-]+)/([a-z0-9-]+)',
                array(
                    'controller' => 'razdel',
                    'action' => 'posts'
                ),
                array(
                    1 => 'razdelid',
                    2 => 'postid'
                )
        );
        $router->addRoute('posts', $route_posts);
        
        // маршрут для разделов //(?!(?:map)$)
        $route_razdels = new Zend_Controller_Router_Route_Regex(
                '([a-z0-9-]+)',
                array(
                    'controller' => 'razdel',
                    'action' => 'index'
                ),
                array(
                    1 => 'str'
                )
        );
       
        $router->addRoute('razdel', $route_razdels);
       
        // рендер сохранить
        $route_rsave = new Zend_Controller_Router_Route_Regex(
                '(zxoelfkdjc)',
                array(
                    'controller' => 'index',
                    'action' => 'zxoelfkdjc'
                )
        );
       
        $router->addRoute('rsave_route', $route_rsave);
        
        // сохранить
        $route_save = new Zend_Controller_Router_Route_Regex(
                '(savepost)',
                array(
                    'controller' => 'index',
                    'action' => 'savepost'
                )
        );
       
        $router->addRoute('save_route', $route_save);
        
        // Search
        $route_search = new Zend_Controller_Router_Route_Regex(
                '(search)',
                array(
                    'controller' => 'index',
                    'action' => 'search'
                )
        );
       
        $router->addRoute('search_route', $route_search);

        // filters
        $route_search = new Zend_Controller_Router_Route_Regex(
            '([a-z0-9-]+)/(filters)',
            [
                'controller' => 'index',
                'action' => 'filters'
            ],
            [
                1 => 'razdelName',
            ]
        );

        $router->addRoute('search_route', $route_search);


        // Ajax load arcticle
        $load_ajax_route = new Zend_Controller_Router_Route_Regex(
                '(loadarcticleonscroll)',
                array(
                    'controller' => 'razdel',
                    'action' => 'loadarcticleonscroll'
                )
        );
       
        $router->addRoute('load_ajax_route', $load_ajax_route);
        
        // edit arcticle from browser button
        $edit_ajax_route = new Zend_Controller_Router_Route_Regex(
                '(editarticlefrombutton)',
                array(
                    'controller' => 'razdel',
                    'action' => 'editarticlefrombutton'
                )
        );
       
        $router->addRoute('edit_ajax_route', $edit_ajax_route);
        
        
        // save arcticle from browser button
        $save_ajax_route = new Zend_Controller_Router_Route_Regex(
                '(savearticlefromsave)',
                array(
                    'controller' => 'razdel',
                    'action' => 'savearticlefromsave'
                )
        );
       
        $router->addRoute('save_ajax_route', $save_ajax_route);

        // save arcticle from browser button
        $clenCacheroute = new Zend_Controller_Router_Route_Regex(
            '(cleanmemcacheforme)',
            [
                'controller' => 'index',
                'action' => 'cleanmemcacheforme'
            ]
        );

        $router->addRoute('clenCacheroute', $clenCacheroute);

    }
}


