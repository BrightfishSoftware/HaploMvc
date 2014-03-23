<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploConfig
 **/

namespace HaploMvc\Config;

use HaploMvc\Pattern\HaploSingleton;
use HaploMvc\HaploApp;
use HaploMvc\Exception\HaploConfigParseFileException;

class HaploConfig extends HaploSingleton
{
    /**
     * @var HaploApp
     */
    protected $app;
    /**
     * @var string
     */
    protected $environment;

    /**
     * Stores merged config details from all config files
     *
     * @access protected
     * @var array
     **/
    protected $config = array();

    /**
     * Constructor for class
     *
     * @param HaploApp $app
     * @return HaploConfig
     */
    protected function __construct(HaploApp $app)
    {
        $this->app = $app;
        $this->config['_files'] = array();

        if (is_dir($this->app->appBase.'/Config')) {
            $this->getEnvironments();

            if (!empty($this->environment)) {
                $this->parseFiles($this->app->appBase.'/Config', $this->environment['files']);
                $this->config['environment']['name'] = $this->environment['name'];
            } else {
                $files = $this->getFiles($this->app->appBase.'/Config');
                $this->parseFiles($this->app->appBase.'/Config', $files);
            }
        }
    }

    /**
     * Get details about available environments and return matching environment
     * or default
     *
     * @throws HaploConfigParseFileException
     * @return array
     */
    protected function getEnvironments()
    {
        $environmentsFile = $this->app->appBase.'/Config/Environments.ini';
        $server = gethostname();

        if (file_exists($environmentsFile)) {
            $environments = parse_ini_file($environmentsFile, true);

            if (!empty($environments)) {
                foreach ($environments as $key => $details) {
                    if (
                        $key != 'default' &&
                        (
                            $key == $server || // exact match for environment
                            (substr($key, -1) == '*' && substr($server, 0, strlen($key) - 1)) // wildcard
                        )
                    ) {
                        $this->environment = $details;
                        break;
                    }
                }

                if (empty($this->environment)) {
                    $this->environment = $environments['default'];
                }
            } else {
                throw new HaploConfigParseFileException("Couldn't parse environment file ($environmentsFile)");
            }
        }
    }

    /**
     * Read config directory and add filenames for all files with extension .ini to files array
     *
     * @param string $path Path to config directory
     * @return array
     **/
    protected function getFiles($path)
    {
        $files = array();
        $dir = dir($path);

        while (false !== ($file = $dir->read())) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'ini') {
                $files[] = $file;
            }
        }

        sort($files);

        return $files;
    }

    /**
     * Process .ini files and add to config array
     * Values in later files will override ones with the same name in earlier files
     *
     * @param string $path Path to config directory
     * @param array $files Files to process
     * @throws HaploConfigParseFileException
     */
    protected function parseFiles($path, $files)
    {
        foreach ($files as $file) {
            $config = parse_ini_file("$path/$file", true);

            if (!empty($config)) {
                $this->config = $this->mergeConfig($this->config, $config);
                $this->config['_files'][] = "$path/$file";
            } else {
                throw new HaploConfigParseFileException("Couldn't parse configuration file ($path/$file)");
            }
        }
    }

    /**
     * @param array $config1
     * @param array $config2
     * @return array
     */
    protected function mergeConfig(array $config1, array $config2)
    {
        foreach ($config2 as $key => $value) {
            if (is_array($value) && array_key_exists($key, $config1) && is_array($config1[$key])) {
                $config1[$key] = $this->mergeConfig($config1[$key], $value);
            } else {
                $config1[$key] = $value;
            }
        }
        return $config1;
    }

    /**
     * Static helper method used to ensure only one instance of the class is instantiated
     *
     * @param HaploApp $app
     * @return HaploConfig
     */
    static public function getInstance(HaploApp $app = null)
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class]) && !is_null($app)) {
            self::$instances[$class] = new $class($app);
        }
        return self::$instances[$class];
    }

    /**
     * Get specified key from section and key
     *
     * @param string $section The section the key can be found in (corresponds to ini file section)
     * @param string $key The key to retrieve value for
     * @param string $default
     * @return string
     */
    public function getKey($section, $key, $default = '')
    {
        if (isset($this->config[$section][$key])) {
            return $this->config[$section][$key];
        } else {
            return $default;
        }
    }

    /**
     * Dynamically set/update a config key - this doesn't actually change the config files
     *
     * @param $section
     * @param $key
     * @param $value
     * @return boolean
     */
    public function setKey($section, $key, $value)
    {
        return ($this->config[$section][$key] = $value);
    }

    /**
     * Get specified section
     *
     * @param string $section The section to retrieve (corresponds to ini file section)
     * @param array $default
     * @return string
     */
    public function getSection($section, $default = array())
    {
        if (isset($this->config[$section])) {
            return $this->config[$section];
        } else {
            return $default;
        }
    }

    /**
     * Get all configuration options
     *
     * @return array
     **/
    public function getAll()
    {
        return $this->config;
    }
}
