<?php


namespace Micro;


abstract class SecureApiController extends ApiController
{
    private $access_token;
    private $username;

    function __construct(){
        parent::__construct();
        $this->getAccess();

        //si hay errores
        if($this->haveErrors()){
            //envia errores y termina
            $this->toJSON();
        }else{
            $this->loadSession();
        }
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->access_token;
    }


    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }



    /**
     * @param $user
     * @param $token
     * @return boolean
     */
    protected abstract function validateToken($user, $token): bool;

    protected abstract function loadSession();

    private function getAccess()
    {
        $uname = $this->getRequestAttr(Config::$KEY_ACCESS_USERNAME);
        $token = $this->getRequestAttr(Config::$KEY_ACCESS_TOKEN);

        if($this->validateToken($uname,$token)){
            $this->access_token = $token;
            $this->username = $uname;

            $this->loadSession();
        }else{
            $this->addError("error_invalid_token");
            $this->setStatus('access_denied', '400');
        }
    }
}