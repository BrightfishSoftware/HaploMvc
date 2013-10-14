<?php
/**
 * @package Home
 **/
namespace Actions;

use \HaploMvc\Action\HaploAction;

/**
 * Class Home
 * @package Actions
 */
class Home extends HaploAction {
    protected function do_all() {
        $template = $this->app->template->create('Home.php');
        $template->display();
    }
}