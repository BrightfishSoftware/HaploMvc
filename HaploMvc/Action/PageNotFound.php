<?php
namespace HaploMvc\Action;

/**
 * Class PageNotFound
 * @package HaploMvc
 */
class PageNotFound extends Action
{
    public function doAll() {
        header('HTTP/1.1 404 Not Found');
        echo 'Page Not Found';
    }
}
