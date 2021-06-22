<?php

require_once '../vendor/autoload.php';

use Micro\Config;
use Micro\Controller;


Config::$APP_DEFAULT_HANDLER = "base";
Config::$PATH_ROOT = "/micro/public/";

if(!Controller::exec("myapp")){
    header("location:" . Config::$PATH_ROOT . Config::$APP_DEFAULT_HANDLER);
}