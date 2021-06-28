<?php


namespace Micro;


class ApiController extends Controller
{


    private $status_added;

    function __construct(){
        $this->status_added = false;

        $this->configErrorHandler();


    }

    private function configErrorHandler(){
        set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
            ErrorTracker::getInstance()->addError("$errno, $errstr, $errfile, $errline, $errcontext");

        });
    }

    protected function addStatus(){
        if(ErrorTracker::getInstance()->haveErrors()){
            foreach (ErrorTracker::getInstance()->getAllErrors() as $error){
                $this->addError($error);
            }
        }

        //si hay errores
        if($this->haveErrors()){
            $this->serverError();
        }else{
            $this->success();
        }
    }

    protected function setStatus($status, $code){
        $this->setVar(Config::$KEY_STATUS, $status);
        $this->setVar(Config::$KEY_STATUS_CODE, $code);


        $this->status_added = true;
    }

    protected function success(){
        $this->setVar(Config::$KEY_STATUS, Config::$API_DEFAULT_VALUE_SUCCESS);
        $this->setVar(Config::$KEY_STATUS_CODE, Config::$API_DEFAULT_SUCCESS_CODE);
        $this->status_added = true;
    }

    protected function serverError($errorCode = '500'){
        $this->setVar(Config::$KEY_STATUS, Config::$API_DEFAULT_VALUE_ERROR);
        $this->setVar(Config::$KEY_STATUS_CODE, $errorCode);

        $this->setVar(Config::$KEY_ERRORS,  $this->errors);
        $this->status_added = true;
    }

    function toJSON($send = true, $headers = true){


        if($send && $headers){
            header('Cache-Control: no-cache, must-revalidate');
            header('Content-type: application/json');
        }


        //si no se ha puesto el status
        if(!$this->status_added){
            $this->addStatus();
        }

        $json = json_encode($this->getAllVars());

        if($send){
            echo $json;

            exit;
        }

        return $json;

    }
}