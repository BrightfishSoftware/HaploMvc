<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploSetup
 **/

namespace HaploMvc;

/**
 * Class HaploSetup
 * @package HaploMvc
 */
class HaploSetup {
    /**
     * Run checks
     *
     * @throws HaploDirNotFoundException
     * @throws HaploDirNotWritableException
     * @throws HaploException
     * @throws HaploPhpConfigException
     */
    public static function validate($appBase) {
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
            throw new HaploException('Templates directory ('.$appBase.'/Templates) not found.');
        }
        
        if (!is_dir($appBase.'/PostFilters')) {
            throw new HaploDirnotFoundException('Template post filters directory ('.$appBase.'/PostFilters) not found.');
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
    }
}

// check that the framework is set up correctly
HaploSetup::validate(APP_BASE);