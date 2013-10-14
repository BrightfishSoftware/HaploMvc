<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploHttp
 **/

namespace HaploMvc\Http;

/**
 * Class HaploHttp
 * @package HaploMvc
 */
class HaploHttp {
    /**
     * @param HaploHttpOptions $options
     * @return array
     */
    static protected function options(HaploHttpOptions $options) {
        $allOptions = array(
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1
        );

        if (!is_null($options)) {
            $allOptions = array_merge($allOptions, array(
                CURLOPT_CONNECTTIMEOUT => $options->connectTimeout,
                CURLOPT_TIMEOUT => $options->requestTimeout,
                CURLOPT_USERAGENT => $options->userAgent
            ));
        }

        return $allOptions;
    }

    /**
     * @param string $url
     * @param HaploHttpOptions $options
     * @return array
     */
    static public function get($url, HaploHttpOptions $options = null) {
        $options = self::options($options);
        
        // initiate session
        $curl = curl_init($url);
        // set options
        curl_setopt_array($curl, $options);
        // request URL
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // close session
        curl_close($curl);
        
        return array($result, $httpCode);
    }

    /**
     * @param string $url
     * @param array $params
     * @param HaploHttpOptions $options
     * @return array
     */
    static public function post($url, array $params = array(), HaploHttpOptions $options = null) {
        $options = self::options($options);

        // initiate session
        $curl = curl_init($url);
        // set options
        curl_setopt_array($curl, array_merge($options, array(
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_POST => 1
        )));
        // request URL
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // close session
        curl_close($curl);
        
        return array($result, $httpCode);
    }

    /**
     * @param string $url
     * @param HaploHttpOptions $options
     * @return mixed
     */
    static public function head($url, HaploHttpOptions $options = null) {
        $options = self::options($options);
        
        // initiate session
        $curl = curl_init($url);
        // set options
        curl_setopt_array($curl, array_merge($options, array(
            CURLOPT_NOBODY => 1
        )));
        // request URL
        curl_exec($curl);
        $headers = curl_getinfo($curl);
        // close session
        curl_close($curl);
        
        return $headers;
    }
}