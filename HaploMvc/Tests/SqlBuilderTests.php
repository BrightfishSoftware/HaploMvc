<?php
namespace HaploMvc\Tests;

use PHPUnit_Framework_TestCase;
use HaploMvc\App;
use HaploMvc\Db\SqlBuilder;

class SqlBuilderTests extends PHPUnit_Framework_TestCase
{
    /** @var SqlBuilder */
    protected $sqlBuilder;

    public function setUp()
    {
        $this->addTranslationDirs();
        $this->sqlBuilder = new SqlBuilder((new App(APP_BASE))->db);
    }

    public function tearDown()
    {
        $this->removeTranslationDirs();
    }

    public function testSimpleGet()
    {
        $this->sqlBuilder->select(['id', 'title', 'body']);
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
        @unlink(APP_BASE.'/Translations/en-US.txt');
        @unlink(APP_BASE.'/Cache/haplo-translations-'.md5('en-US').'.cache');
        rmdir(APP_BASE.'/Translations');
        rmdir(APP_BASE.'/Cache');
    }
}
