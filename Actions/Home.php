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
        $booksModel = new BooksModel($this->app);
        $booksModel->get(1);
        $template = $this->app->template->create('Home.php');
        $template->display();
    }
}
