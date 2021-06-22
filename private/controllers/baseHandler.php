<?php
namespace myapp;

class baseHandler extends \Micro\ApiController
{
    function indexAction(){
        $this->setVar("hola","ok");
        $this->addError("mal");
        $this->toJSON();
    }
}