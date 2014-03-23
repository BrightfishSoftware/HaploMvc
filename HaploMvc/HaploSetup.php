<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploSetup
 **/

namespace HaploMvc;

use HaploMvc\Exception\HaploDirNotFoundException,
    HaploMvc\Exception\HaploDirNotWritableException,
    HaploMvc\Exception\HaploException,
    HaploMvc\Exception\HaploPhpConfigException,
    HaploMvc\Exception\HaploClassNotFoundException;

/**
 * Class HaploSetup
 * @package HaploMvc
 */
class HaploSetup
{
    /**
     * Run checks
     *
     * @throws HaploDirNotFoundException
     * @throws HaploDirNotWritableException
     * @throws HaploException
     * @throws HaploPhpConfigException
     * @throws HaploClassNotFoundException
     */
    public static function validate($appBase)
    {
        if (ini_get('register_globals')) {
            throw new HaploPhpConfigException('Please disable register_globals in your php.ini file.');
        }
        
        if (get_magic_quotes_gpc()) {
            throw new HaploPhpConfigException('Please disable magic_quotes_gpc in your php.ini file.');
        }
        
        if (!is_dir($appBase.'/Actions')) {
            throw new HaploDirNotFoundException('Actions directory ('.$appBase.'/Actions) not found.');
        }

        if (!is_dir($appBase.'/Templates')) {
            throw new HaploDirNotFoundException('Templates directory ('.$appBase.'/Templates) not found.');
        }

        if (!is_dir($appBase.'/TemplateFunctions')) {
            throw new HaploDirNotFoundException('Template functions directory ('.$appBase.'/TemplateFunctions) not found.');
        }
        
        if (!is_dir($appBase.'/PostFilters')) {
            throw new HaploDirNotFoundException('Template post filters directory ('.$appBase.'/PostFilters) not found.');
        }
        
        if (!is_dir($appBase.'/Translations')) {
            throw new HaploDirNotFoundException('Translations directory ('.$appBase.'/Translations) not found.');
        }
        
        if (!is_dir($appBase.'/Cache')) {
            throw new HaploDirNotFoundException('Cache directory ('.$appBase.'/Cache) not found');
        }
        
        if (!is_writable($appBase.'/Cache')) {
            throw new HaploDirNotWritableException('Cache directory ('.$appBase.'/Cache) is not writeable.');
        }

        if (!class_exists('\Zend\Escaper\Escaper')) {
            throw new HaploClassNotFoundException('Class \Zend\Escaper\Escaper not found. Required for HaploEscaper.');
        }
    }
}

// check that the framework is set up correctly
HaploSetup::validate(APP_BASE);
