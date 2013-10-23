<?php
namespace Models;

use \HaploMvc\Db\HaploModel;

class BooksModel extends HaploModel {
    public function get($bookId) {
        echo $this->app->sqlBuilder
            ->select(array('b.book_id', 'title', 'publish_date'))
            ->where('book_id', '=', $bookId)
            ->where(function($sqlBuilder) {
                $sqlBuilder
                    ->where('author_id', '=', 1)
                    ->or_where('author_id', '=', 3)
                    ->or_where(function($sqlBuilder) {
                        $sqlBuilder
                            ->where('author_id', '=', 3)
                            ->or_where('author_id', '=', 9);
                    });
            })
            ->where('publish_date', '=', '2013-10-10')
            ->order_by('book_id', 'ASC')
            ->order_by('publish_date', 'DESC')
            ->limit(50)
            ->get('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->order_by('title', 'ASC')
            ->get('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->where('book_id', '=', $bookId)
            ->delete('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->delete_all('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->truncate('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->distinct()
            ->select('title')
            ->from('books')
            ->get();

        echo '<br>';

        echo $this->app->sqlBuilder
            ->set('title', 'a book title')
            ->set('publish_date', '2013-10-14')
            ->insert('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->select(array('book_id', 'title'))
            ->order_by('title', 'ASC')
            ->limit(50)
            ->get('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->count('books');
    }
}