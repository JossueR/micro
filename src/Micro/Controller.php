<?php


namespace Micro;


use Exception;

class Controller extends Component
{

    private static $request_json;
    private static $mode_raw_request = false;

    //Almacena la accion que sera ejecutada
    public static $do;

    //almacena el nombre del script Actual
    public static $handler;

    /**
     *
     *Obtiene un attributo enviado a traves de el post o el get y le aplica trim, bd_escape, htmlentities
     * @param $attr String del attributo
     * @param $post boolean true por defecto, false si se quiere buscar en GET
     * @return array|string|null
     */
    public static function getRequestAttr($attr, $post = true)
    {

        //si no esta habilitado el modo Raw
        if (!self::$mode_raw_request) {
            $attr = str_replace(".", "_", $attr);

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
    public static function setRequestAttr($attr, $val, $post = true)
    {
        $attr = str_replace(".", "_", $attr);

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

    public static function isRawEnabled()
    {
        return self::$mode_raw_request;
    }

    public static function getAllRequestData($post = true)
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

    public static function exec($namespace){

        $status = false;
        self::$do = self::getRequestAttr(Config::$APP_DEFAULT_ACTION_PARAM);
        if(!self::$do){
            self::$do = self::getRequestAttr(Config::$APP_DEFAULT_ACTION_PARAM,false);
        }

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
}