<?php
/**
 * @package Home
 **/
namespace Actions;

use \HaploMvc\HaploAction;

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