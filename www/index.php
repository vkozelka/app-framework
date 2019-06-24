<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

define("DS", DIRECTORY_SEPARATOR);
define("CMS_DIR_WWW", __DIR__);
define("CMS_DIR_ROOT", dirname(CMS_DIR_WWW));
define("CMS_DIR_VENDOR", CMS_DIR_ROOT . DS . "vendor");
define("CMS_DIR_APP", CMS_DIR_ROOT . DS . "app");
define("CMS_DIR_CONFIG", CMS_DIR_ROOT . DS . "config");
define("CMS_DIR_APP_LANGUAGE", CMS_DIR_APP . DS . "languages");
define("CMS_DIR_APP_MODULE", CMS_DIR_APP . DS . "modules");
define("CMS_DIR_APP_SYSTEM", CMS_DIR_APP . DS . "system");
define("CMS_DIR_APP_VIEW", CMS_DIR_APP . DS . "views");
define("CMS_DIR_VAR", CMS_DIR_ROOT . DS . "var");
define("CMS_DIR_VAR_CACHE", CMS_DIR_VAR . DS . "cache");
define("CMS_DIR_VAR_LOG", CMS_DIR_VAR . DS . "logs");
define("CMS_DIR_VAR_SESSION", CMS_DIR_VAR . DS . "sessions");

require_once CMS_DIR_VENDOR . DS . "autoload.php";

$app = \App\System\App::get();

$app->getProfiler()->start("App");
echo $app->run();
$app->getProfiler()->stop("App");

//if ("development" === $app->getEnvironment()) {
//    $app->outputProfiler();
//}