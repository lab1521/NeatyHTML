<?php
namespace Lab1521\NeatyHTML;

class Config
{
    protected static $instance;
    protected $blockList;

    public function __construct()
    {
        $this->clear();
    }

    public function clear()
    {
        $this->blockList = [
            'attr' => [],
            'tags' => [],
            'tagOverrides' => [],
        ];
    }

    public static function reset()
    {
        if (! self::$instance) {
            self::$instance = new static();
        }

        $instance = self::$instance;
        $instance->clear();
    }

    public static function settings($key, $moreConfig = [])
    {
        if (! self::$instance) {
            self::$instance = new static();
        }
        $instance = self::$instance;
        return $instance->setupConfigKeys($key, $moreConfig);
    }

    /**
     * Loads configuration keys from relative file.
     *
     * @param string $key        Configuration file types
     * @param array  $moreConfig Additional array configurations
     *
     * @return array
     */
    public function setupConfigKeys($key, $moreConfig = [])
    {
        if (!is_array($moreConfig)) {
            $moreConfig = [];
        }

        if ($this->blockList[$key] && !$moreConfig) {
            return $this->blockList[$key];
        }

        $configFile = require "configs/{$key}.php";
        $this->blockList[$key] = array_merge($configFile, $moreConfig);

        return $this->blockList[$key];
    }
}
