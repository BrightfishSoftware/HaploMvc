<?php
/**
 * @package Home
 **/
namespace Actions;

use \HaploMvc\Action\HaploAction,
    \Models\BooksModel;

/**
 * Class Home
 * @package Actions
 */
class Home extends HaploAction {
    protected function do_all() {
        $booksModel = new BooksModel();
        $booksModel->get(1);
        $template = $this->app->template->create('Home.php');
        $template->display();
    }
}