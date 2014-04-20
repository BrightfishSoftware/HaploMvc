<?php
namespace HaploMvc\Tests;

use PHPUnit_Framework_TestCase;
use HaploMvc\HaploApp;
use HaploMvc\Db\HaploSqlBuilder;

class HaploSqlBuilderTests extends PHPUnit_Framework_TestCase
{
    /** @var HaploSqlBuilder */
    protected $sqlBuilder;

    public function setUp()
    {
        $this->addTranslationDirs();
        $app = new HaploApp(APP_BASE);
        $this->sqlBuilder = new HaploSqlBuilder($app->db);
    }

    public function tearDown()
    {
        $this->removeTranslationDirs();
    }

    public function testSimpleGet()
    {
        $this->sqlBuilder->select(array('id', 'title', 'body'));
        $sql = $this->sqlBuilder->get('posts');
        $this->assertEquals('SELECT `id`, `title`, `body` FROM `posts`;', $sql);
    }

    protected function addTranslationDirs()
    {
        mkdir(APP_BASE.'/Translations');
        mkdir(APP_BASE.'/Cache');
        touch(APP_BASE.'/Translations/en-US.txt');
    }

    protected function removeTranslationDirs()
    {
        unlink(APP_BASE.'/Translations/en-US.txt');
        unlink(APP_BASE.'/Cache/haplo-translations-'.md5('en-US').'.cache');
        rmdir(APP_BASE.'/Translations');
        rmdir(APP_BASE.'/Cache');
    }
}
