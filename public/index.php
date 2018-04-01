<?php
//phpinfo();
// Define path to public directory
defined('WWW_PATH')
|| define('WWW_PATH', realpath(dirname(__FILE__) . '/../www'));

// Define path to public directory
defined('LIBRARY_PATH')
|| define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));

// Define path to root directory
defined('ROOT_PATH')
|| define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'library.php';
require_once 'simple_html_dom.php';
require_once 'MyCache.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap()
            ->run();
