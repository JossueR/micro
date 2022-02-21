<?php


namespace Micro;


use Exception;

class Controller extends Component
{

    private static $request_json;
    private static $mode_raw_request = false;

    //Almacena la accion que sera ejecutada
    private static $do;

    //almacena el nombre del script Actual
    private static $handler;

    /**
     *
     *Obtiene un attributo enviado a traves de el post o el get y le aplica trim, bd_escape, htmlentities
     * @param $attr String del attributo
     * @param $post boolean true por defecto, false si se quiere buscar en GET
     * @return array|string|null
     */
    public static function getRequestAttr(string $attr, bool $post = true)
    {

        //si no esta habilitado el modo Raw
        if (!self::$mode_raw_request) {
            //$attr = str_replace(".", "_", $attr);

            if ($post) {
                $var = $_POST;
            } else {
                $var = $_GET;
            }
        } else {
            //modo raw busca la data en el objeto ya serializado
            $var = self::$request_json;
        }


        if (isset($var[$attr])) {


            return self::trim_r($var[$attr]);

        } else {
            if ($post) {
                return self::getRequestAttr($attr, false);
            } else {
                return null;
            }

        }
    }

    /**
     *
     *Asigna un attributo enviado a traves de el post o el get y le aplica trim, bd_escape, htmlentities
     * @param $attr string del attributo
     * @param $val string
     * @param $post true por defecto, false si se quiere buscar en GET
     */
    public static function setRequestAttr(string $attr, $val, bool $post = true)
    {
        //$attr = str_replace(".", "_", $attr);

        if (!is_array($val)) {
            $val = trim($val);
        }

        //si no esta habilitado el modo Raw
        if (!self::$mode_raw_request) {
            if ($post) {
                $_POST[$attr] = $val;
            } else {
                $_GET[$attr] = $val;
            }
        } else {
            self::$request_json[$attr] = $val;
        }
    }


    public static function enableRawRequest()
    {
        $raw = file_get_contents('php://input');
        self::$request_json = json_decode($raw, true);

        //si no puede decodificarlo
        if (!self::$request_json) {
            //usa el request
            self::$request_json = $_REQUEST;
        }

        self::$mode_raw_request = true;
    }

    public static function isRawEnabled(): bool
    {
        return self::$mode_raw_request;
    }

    public static function getAllRequestData($post = true): array
    {


        if (self::isRawEnabled()) {
            $data = self::$request_json;
        } else {
            if ($post) {
                $data = $_POST;
            } else {
                $data = $_GET;
            }
        }

        return $data;
    }

    public static function exec($namespace): bool
    {

        $status = false;


        self::$handler = (isset($_SERVER['REQUEST_URI']))? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
        self::$handler = explode("?", self::$handler);
        self::$handler = self::$handler[0];
        $partes_ruta = pathinfo(self::$handler);


        if(strpos($partes_ruta["dirname"], Config::$PATH_ROOT) !== false){
            $class_path = str_replace(Config::$PATH_ROOT,"", $partes_ruta["dirname"]);

            $namespace .= "\\" . $class_path;
        }




        $className = "\\" . $namespace . "\\" . $partes_ruta["filename"] . Config::$APP_DEFAULT_CONTROLLER_SUFFIX;

        if ($className != "Handler" ) {
            self::$handler = $partes_ruta["filename"];

            if(class_exists($className)) {
                try {

                    $mi_clase = new $className();

                    self::$do = self::getRequestAttr(Config::$APP_DEFAULT_ACTION_PARAM);
                    if(!self::$do){
                        self::$do = self::getRequestAttr(Config::$APP_DEFAULT_ACTION_PARAM,false);
                    }

                    if (method_exists($mi_clase, self::$do . Config::$APP_DEFAULT_CONTROLLER_METHOD_SUFFIX)) {
                        $method = self::$do . Config::$APP_DEFAULT_CONTROLLER_METHOD_SUFFIX;

                        $mi_clase->$method();
                        $status = true;
                    } else {
                        $method = "index" . Config::$APP_DEFAULT_CONTROLLER_METHOD_SUFFIX;

                        if (method_exists($mi_clase, $method)) {
                            $mi_clase->$method();
                            $status = true;
                        }
                    }
                } catch (Exception $e) {
                    //var_dump($e);
                }
            }
        }

        return $status;
    }

    /**
     * Llena un prototipo con los valores que vienen del post o get
     * @param $prototype: arreglo con los datos a cargar
     * @param $post: indica si buscara los valores en post o get
     */
    public function fillPrototype($prototype , $post=true, $map_nulls = false){


        foreach ($prototype as $key => $default_value) {

            //si encuentra un punto
            if(strpos($key, ".") !== false){

                $fragments = explode(".", $key);

                $temp_val = $this->getRequestAttr(array_shift($fragments), $post);
                foreach ($fragments as $subkey){
                    if(isset($temp_val[$subkey])){
                        $temp_val = $temp_val[$subkey];
                    }else{
                        break;
                    }
                }
                $prototype[$key] = $temp_val;
            }else{
                $prototype[$key] = $this->getRequestAttr($key, $post);
            }



            if(is_null($prototype[$key])){
                if($default_value != null){
                    $prototype[$key] = $default_value;
                }else if(!$map_nulls){
                    unset($prototype[$key]);
                }

            }
        }


        return $prototype;
    }

    /**
     * Retorna un arreglo con los nombres de los campos del maper
     * @param array $prototype es un arreglo con los nombre de los campos de un formulario
     * @param array $map Arreglo que contiene la equivalencia de [Nombre_Campo_Original]=Nuevo_nombre
     * @param bool $map_nulls si se establece a true, mapea incluso nulos
     */
    static public function mapPrototype($prototype, $map, $map_nulls = false){

        $searchArray = $prototype;
        foreach ($map as $key => $value) {

            if( (isset($prototype[$key]) && $prototype[$key] != null)
                ||
                $map_nulls
            ){
                unset($searchArray[$key]);

                if(strpos($value, ".") !== false){
                    $searchArray = self::buildArrayData($searchArray,$value, $prototype[$key]);
                }else{
                    $searchArray[$value] = $prototype[$key];
                }

            }
        }


        return $searchArray;
    }

    private static function buildArrayData($repository, $key, $value){
        if(strpos($key, ".") !== false){

            $fragments = explode(".", $key);

            $subKey = array_shift($fragments);

            $key = implode(".", $fragments);

            if(!isset($repository[$subKey])){
                $repository[$subKey] = [];
            }

            $repository[$subKey] = self::buildArrayData($repository[$subKey],$key,$value);
        }else{
            $subKey = $key;
            $key = null;

            $repository[$subKey] = $value;
        }

        return  $repository;

    }
}