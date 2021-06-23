<?php


namespace Micro;


use ReflectionClass;

class Config
{
    public static $APP_DEFAULT_CONTROLLER_SUFFIX="Handler";
    public static $APP_DEFAULT_CONTROLLER_METHOD_SUFFIX="Action";
    public static $APP_DEFAULT_ACTION_PARAM="do";
    public static $PATH_ROOT;

    public static $APP_DEFAULT_HANDLER;
    public static $APP_DEFAULT_SECURE_HANDLER;

    private static $raw_config;

    public static function loadConfigFile($path_to_json_file){
        $raw_file = file_get_contents($path_to_json_file);
        if($raw_file) {
            $json_conf = json_decode($raw_file, true);
            self::$raw_config = $json_conf;

            $reflectedClass = new ReflectionClass(Config::class);

            $props = $reflectedClass->getStaticProperties();
            foreach ($props as $prop_name => $value) {
                $value = self::getConfig($prop_name);

                if($value != null){
                    $reflectedClass->setStaticPropertyValue($prop_name, $value);
                }

            }
        }
    }

    /** Busca en un array $search_obj el valor que se encuentra en la ruta $config_path
     *
     * @param string $config_path indica la ruta para llegar al valor, debe estar separada por puntos
     * @param array $search_obj arreglo asociativo como los que devuelve json_decode(..., true)
     * @return mixed|null
     */
    public static function getConfig($config_path, $search_obj=null){
        $path_slices = explode(".", $config_path);
        $value = null;

        if($search_obj == null){
            $search_obj = self::$raw_config;
        }

        foreach ($path_slices as $path) {
            if(isset($search_obj) && isset($search_obj[$path])){
                $search_obj = $search_obj[$path];
            }else{
                $search_obj = null;
                break;
            }
        }

        if($search_obj){
            $value = $search_obj;
        }

        return $value;
    }
}