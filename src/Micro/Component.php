<?php


namespace Micro;


class Component
{
    private $_vars = array();
    protected $errors = array();

    /**
     *
     * Carga variable para ser accesada en las vistas
     * @param String $key
     * @param Mixed $value
     */
    public function setVar($key, $value){
        $this->_vars[$key] = $value;
    }

    /**
     *
     * Obtiene variable registrada con setVar.
     * retorna nulo si no existe
     * @param Mixed $key
     */
    public function getVar($key){
        return (isset($this->_vars[$key]))? $this->_vars[$key] : null;
    }

    public function getAllVars(){
        return $this->_vars;
    }

    public function setALLVars($all){
        $this->_vars = $all;
    }

    public function haveErrors(){
        return (count($this->errors) > 0);
    }

    public function addError($msg){
        $this->errors[] = $msg;
    }

    public function getAllErrors(){
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function addErrors( $errors){
        $this->errors = array_merge($this->errors, $errors);
    }

    public static function trim_r($arr)
    {
        if(is_array($arr)){
            return array_map('self::trim_r', $arr);
        }else if(is_bool($arr) ){
            return $arr;
        }else{
            return trim($arr);
        }
    }
}
