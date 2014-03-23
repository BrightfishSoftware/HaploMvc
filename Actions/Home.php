<?php
/**
 * @package Home
 **/
namespace Actions;

use HaploMvc\Action\HaploAction;

/**
 * Class Home
 * @package Actions
 */
class Home extends HaploAction
{
    protected function doAll()
    {
        $booksModel = $this->app->getClass('\Models\BooksModel');
        $booksModel->get(1);
        $template = $this->app->template->create('Home.php');
        $template->display();
    }
}
