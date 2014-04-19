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
        $app = new HaploApp(dirname(dirname(__DIR__)));
        $this->sqlBuilder = new HaploSqlBuilder($app->db);
    }

    public function tearDown()
    {

    }

    public function testSimpleGet()
    {
        $this->sqlBuilder->select(array('id', 'title', 'body'));
        $sql = $this->sqlBuilder->get('posts');
        $this->assertEquals('SELECT `id`, `title`, `body` FROM `posts`;', $sql);
    }
}
