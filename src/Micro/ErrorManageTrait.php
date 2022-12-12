<?php

namespace Micro;

trait ErrorManageTrait
{
    private $errors = [];

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError($msg){
        $this->errors[] = $msg;
    }

    public function addErrors($erros){
        if(is_array($erros)) {
            $this->errors = array_merge($this->errors, $erros);
        }
    }

    public function clearErrors(){
        $this->errors = [];
    }

    public function haveErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
