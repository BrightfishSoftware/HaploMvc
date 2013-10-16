<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited/Ed Eliot
 * @package HaploInput
 **/

namespace HaploMvc\Input;

/**
 * Class HaploInput
 * @package HaploMvc
 */
class HaploInput {
    const FILTER_TAGS = 1;
    const FILTER_SPECIAL_CHARS = 2;
    const FILTER_INT = 3;
    const FILTER_FLOAT = 4;
    const FILTER_BOOL = 5;
    const FILTER_ALPHA = 6;
    const FILTER_ALPHA_DASHES = 7;
    const FILTER_ALPHA_DASHES_QUOTES = 8;
    const FILTER_URL = 9;
    const FILTER_EMAIL = 10;
    const FILTER_RAW = 11;

    /** @var string */
    public static $encoding = 'UTF-8';

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function get($key, $default = '', $filter = self::FILTER_ALPHA) {
        return static::process_input($_GET, $key, $default, $filter);
    }

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function post($key, $default = '', $filter = self::FILTER_ALPHA) {
        return static::process_input($_POST, $key, $default, $filter);
    }

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function request($key, $default = '', $filter = self::FILTER_ALPHA) {
        return static::process_input($_REQUEST, $key, $default, $filter);
    }

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function session($key, $default = '', $filter = self::FILTER_ALPHA) {
        return static::process_input($_SESSION, $key, $default, $filter);
    }

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function cookie($key, $default = '', $filter = self::FILTER_ALPHA) {
        return static::process_input($_COOKIE, $key, $default, $filter);
    }

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function server($key, $default = '', $filter = self::FILTER_ALPHA) {
        return static::process_input($_SERVER, $key, $default, $filter);
    }

    /**
     * @param string $input
     * @param string $key
     * @param mixed $default
     * @param int $filter
     * @return mixed
     */
    protected static function process_input($input, $key, $default, $filter) {
        if (isset($input[$key])) {
            switch ($filter) {
                case static::FILTER_TAGS:
                    return static::strip_tags_recursive($input[$key]);
                case static::FILTER_SPECIAL_CHARS:
                    return static::html_special_chars_recursive($input[$key]);
                case static::FILTER_INT:
                    return (int)$input[$key];
                case static::FILTER_FLOAT:
                    return (float)$input[$key];
                case static::FILTER_BOOL:
                    return (bool)$input[$key];
                case static::FILTER_ALPHA:
                    return preg_replace('/[^a-z0-9]+/i', '', $input[$key]);
                case static::FILTER_ALPHA_DASHES:
                    return preg_replace('/[^a-z0-9\s_-]+/i', '', $input[$key]);
                case static::FILTER_ALPHA_DASHES_QUOTES:
                    return preg_replace('/[^a-z0-9\s\'\"\._-]+/i', '', $input[$key]);
                case static::FILTER_URL:
                    return filter_var($input[$key], FILTER_SANITIZE_URL);
                case static::FILTER_EMAIL:
                    return filter_var($input[$key], FILTER_SANITIZE_EMAIL);
                case static::FILTER_RAW:
                    return $input[$key];
            }
        }

        return $default;
    }

    /**
     * @param mixed $input
     * @return string
     */
    protected static function strip_tags_recursive($input) {
        if (is_scalar($input)) {
            $input = strip_tags($input);
        } elseif (!empty($input)) {
            foreach ($input as &$component) {
                $component = static::strip_tags_recursive($component);
            }
        }

        return $input;
    }

    /**
     * @param mixed $input
     * @return string
     */
    protected static function html_special_chars_recursive($input) {
        if (is_scalar($input)) {
            $input = htmlspecialchars($input, ENT_QUOTES, static::$encoding);
        } elseif (!empty($input)) {
            foreach ($input as &$component) {
                $component = static::html_special_chars_recursive($component);
            }
        }

        return $input;
    }
}