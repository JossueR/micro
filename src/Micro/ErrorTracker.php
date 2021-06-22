<?php


namespace Micro;


class ErrorTracker extends Component
{
    /**
     * @var ErrorTracker
     */
    private static $instance;

    /**
     * @return ErrorTracker
     */
    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new ErrorTracker();
        }

        return self::$instance;
    }
}