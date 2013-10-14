<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploEscaper
 **/

namespace HaploMvc\Security;

use Zend\Escaper\Escaper as Escaper;

/**
 * Class HaploEscaper
 * @package HaploMvc
 */
class HaploEscaper {
    /** @var string */
    protected static $encoding = 'UTF-8';
    /** @var Escaper */
    protected static $escaper = null;

    /**
     * @param mixed $input
     * @return mixed
     */
    public static function escape_html($input) {
        self::init();
        return self::$escaper->escapeHtml($input);
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    public static function escape_attr($input) {
        self::init();
        return self::$escaper->escapeHtmlAttr($input);
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    public static function escape_js($input) {
        self::init();
        return self::$escaper->escapeJs($input);
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    public static function escape_css($input) {
        self::init();
        return self::$escaper->escapeCss($input);
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    public static function escape_url($input) {
        self::init();
        return self::$escaper->escapeUrl($input);
    }

    protected static function init() {
        if (is_null(self::$escaper)) {
            self::$escaper = new Escaper(self::$encoding);
        }
    }

    /**
     * @param string $encoding
     * @return string
     */
    public static function set_encoding($encoding) {
        self::$encoding = $encoding;
    }
}