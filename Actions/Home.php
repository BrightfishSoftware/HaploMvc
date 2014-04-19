<?php
namespace Actions;

use HaploMvc\Action\HaploAction;
use Models\BooksModel;

/**
 * Class Home
 * @package Actions
 */
class Home extends HaploAction
{
    protected function doAll()
    {
        $template = $this->app->template->create('Home.php');
        $template->display();
    }
}
