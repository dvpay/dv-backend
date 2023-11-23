<?php

class PathHelper
{
    static protected $self;

    protected $base_path = '';

    public function __construct()
    {
        $current_directory = __DIR__;

        $explode = explode(DIRECTORY_SEPARATOR, $current_directory);

        if (in_array('public', $explode)) {
            $this->base_path = str_replace(
                DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'install',
                '',
                $current_directory
            );
        }else {
            $this->base_path = str_replace(
                'install',
                'src',
                $current_directory
            );
        }
    }

    public function getBasepath(): string
    {
        return $this->base_path.DIRECTORY_SEPARATOR;
    }

    public function publicPath(): string
    {
        return $this->getBasepath()."public".DIRECTORY_SEPARATOR;
    }

    public function getStoragePath(): string
    {
        return $this->getBasepath()."storage".DIRECTORY_SEPARATOR;
    }

    public function getConfigPath(): string
    {
        return $this->getBasepath()."config".DIRECTORY_SEPARATOR;
    }

    public static function new(): PathHelper
    {
        if (gettype(self::$self) != 'object') {
            self::$self = (new self());
        }
        return self::$self;
    }
}
