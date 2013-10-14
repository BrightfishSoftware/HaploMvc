<?php
namespace HaploMvc\Tests;

use PHPUnit_Framework_TestCase,
    HaploMvc\Db\HaploDb,
    HaploMvc\Db\HaploQueryBuilder;

class HaploQueryBuilderTests extends PHPUnit_Framework_TestCase {
    /** @var HaploDb */
    protected $db;
    /** @var HaploQueryBuilder */
    protected $queryBuilder;

    public function setUp() {
        $this->db = HaploDb::get_instance();
        $this->queryBuilder = new HaploQueryBuilder($this->db);
        $this->queryBuilder->dry_run(true);
    }

    public function tearDown() {

    }

    public function test_simple_get() {
        $this->queryBuilder->select(array('id, title', 'body'));
        $sql = $this->queryBuilder->get('posts');
        $this->assertEquals('SELECT `id`, `title`, `body` FROM `posts`', $sql);
    }
}