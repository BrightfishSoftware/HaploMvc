<?php
namespace HaploMvc\Tests;

use HaploMvc\Db\HaploMySqlDbDriver;
use PHPUnit_Framework_TestCase,
    HaploMvc\Db\HaploDb,
    HaploMvc\Db\HaploSqlBuilder;

class HaploSqlBuilderTests extends PHPUnit_Framework_TestCase {
    /** @var HaploDb */
    protected $db;
    /** @var HaploSqlBuilder */
    protected $sqlBuilder;

    public function setUp() {
        $this->db = HaploDb::get_instance(new HaploMySqlDbDriver());
        $this->sqlBuilder = new HaploSqlBuilder($this->db);
    }

    public function tearDown() {

    }

    public function test_simple_get() {
        $this->sqlBuilder->select(array('id', 'title', 'body'));
        $sql = $this->sqlBuilder->get('posts');
        $this->assertEquals('SELECT `id`, `title`, `body` FROM `posts`;', $sql);
    }
}
