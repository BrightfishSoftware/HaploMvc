<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited/Ed Eliot
 * @package HaploInput
 **/

namespace HaploMvc;

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

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function get($key, $default = '', $filter = self::FILTER_ALPHA) {
        return self::process_input('get', $key, $default, $filter);
    }

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function post($key, $default = '', $filter = self::FILTER_ALPHA) {
        return self::process_input('post', $key, $default, $filter);
    }

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function request($key, $default = '', $filter = self::FILTER_ALPHA) {
        return self::process_input('request', $key, $default, $filter);
    }

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function session($key, $default = '', $filter = self::FILTER_ALPHA) {
        return self::process_input('session', $key, $default, $filter);
    }

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function cookie($key, $default = '', $filter = self::FILTER_ALPHA) {
        return self::process_input('cookie', $key, $default, $filter);
    }

    /**
     * @param string $key
     * @param string $default
     * @param int $filter
     * @return mixed
     */
    public static function server($key, $default = '', $filter = self::FILTER_ALPHA) {
        return self::process_input('server', $key, $default, $filter);
    }

    /**
     * @param string $type
     * @param string $key
     * @param mixed $default
     * @param int $filter
     * @return mixed
     */
    protected static function process_input($type, $key, $default, $filter) {
        switch ($type) {
            case 'get':
                $input = $_GET;
                break;
            case 'post':
                $input = $_POST;
                break;
            case 'request':
                $input = $_REQUEST;
                break;
            case 'session':
                $input = $_SESSION;
                break;
            case 'cookie':
                $input = $_COOKIE;
                break;
            case 'server':
                $input = $_SERVER;
                break;
            default:
                return $default;
        }

        if (isset($input[$key])) {
            switch ($filter) {
                case self::FILTER_TAGS:
                    return self::strip_tags_recursive($input[$key]);
                case self::FILTER_SPECIAL_CHARS:
                    return self::html_special_chars_recursive($input[$key]);
                case self::FILTER_INT:
                    return (int)$input[$key];
                case self::FILTER_FLOAT:
                    return (float)$input[$key];
                case self::FILTER_BOOL:
                    return (bool)$input[$key];
                case self::FILTER_ALPHA:
                    return preg_replace('/[^a-z0-9]+/i', '', $input[$key]);
                case self::FILTER_ALPHA_DASHES:
                    return preg_replace('/[^a-z0-9\s_-]+/i', '', $input[$key]);
                case self::FILTER_ALPHA_DASHES_QUOTES:
                    return preg_replace('/[^a-z0-9\s\'\"\._-]+/i', '', $input[$key]);
                case self::FILTER_URL:
                    return filter_var($input[$key], FILTER_SANITIZE_URL);
                case self::FILTER_EMAIL:
                    return filter_var($input[$key], FILTER_SANITIZE_EMAIL);
                case self::FILTER_RAW:
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
                $component = self::strip_tags_recursive($component);
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
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        } elseif (!empty($input)) {
            foreach ($input as &$component) {
                $component = self::html_special_chars_recursive($component);
            }
        }

        return $input;
    }
}