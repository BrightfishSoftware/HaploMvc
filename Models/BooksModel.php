<?php
namespace Models;

use HaploMvc\Db\HaploModel;

class BooksModel extends HaploModel
{
    public function get($bookId)
    {
        echo $this->app->sqlBuilder
            ->select(array('b.book_id', 'title', 'publish_date'))
            ->where('book_id', '=', $bookId)
            ->where(function($sqlBuilder) {
                $sqlBuilder
                    ->where('author_id', '=', 1)
                    ->orWhere('author_id', '=', 3)
                    ->orWhere(function($sqlBuilder) {
                        $sqlBuilder
                            ->where('author_id', '=', 3)
                            ->orWhere('author_id', '=', 9);
                    });
            })
            ->where('publish_date', '=', '2013-10-10')
            ->orderBy('book_id', 'ASC')
            ->orderBy('publish_date', 'DESC')
            ->limit(50)
            ->get('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->orderBy('title', 'ASC')
            ->get('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->where('book_id', '=', $bookId)
            ->delete('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->deleteAll('books');

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
            ->orderBy('title', 'ASC')
            ->limit(50)
            ->get('books');

        echo '<br>';

        echo $this->app->sqlBuilder
            ->count('books');
    }
}
