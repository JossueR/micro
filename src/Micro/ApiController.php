<?php


namespace Micro;


class ApiController extends Controller
{
    const KEY_STATUS = "status";
    const KEY_STATUS_CODE = "status_code";
    const KEY_ACCESS_TOKEN = "token";
    const KEY_ERRORS = "errors";

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
        //si hay errores
        if($this->haveErrors()){
            $this->serverError();
        }else{
            $this->success();
        }
    }

    protected function setStatus($status, $code){
        $this->setVar(self::KEY_STATUS, $status);
        $this->setVar(self::KEY_STATUS_CODE, $code);


        $this->status_added = true;
    }

    protected function success(){
        $this->setVar(self::KEY_STATUS, 'success');
        $this->setVar(self::KEY_STATUS_CODE, '100');
        $this->status_added = true;
    }

    protected function serverError($errorCode = '500'){
        $this->setVar(self::KEY_STATUS, 'server_error');
        $this->setVar(self::KEY_STATUS_CODE, $errorCode);

        $this->setVar(self::KEY_ERRORS,  $this->errors);
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