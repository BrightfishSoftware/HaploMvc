<?php
namespace HaploMvc\Security;

use Zend\Escaper\Escaper as Escaper;

/**
 * Class HaploEscaper
 * @package HaploMvc
 */
class HaploEscaper
{
    /** @var string */
    protected static $encoding = 'UTF-8';
    /** @var Escaper */
    protected static $escaper = null;

    /**
     * @param mixed $input
     * @return mixed
     */
    public static function escapeHtml($input)
    {
        self::init();
        return self::$escaper->escapeHtml($input);
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    public static function escapeAttr($input)
    {
        self::init();
        return self::$escaper->escapeHtmlAttr($input);
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    public static function escapeJs($input)
    {
        self::init();
        return self::$escaper->escapeJs($input);
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    public static function escapeCss($input)
    {
        self::init();
        return self::$escaper->escapeCss($input);
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    public static function escapeUrl($input)
    {
        self::init();
        return self::$escaper->escapeUrl($input);
    }

    protected static function init()
    {
        if (is_null(self::$escaper)) {
            self::$escaper = new Escaper(self::$encoding);
        }
    }

    /**
     * @param string $encoding
     * @return string
     */
    public static function setEncoding($encoding)
    {
        self::$encoding = $encoding;
    }
}
