<?php
namespace Models;

use \HaploMvc\Db\HaploModel;

class BooksModel extends HaploModel {
    public function get($bookId) {
        $this->builder->return_sql(true);
        echo $this->builder
            ->select(array('book_id', 'title', 'publish_date'))
            ->where('book_id', '=', $bookId)
            ->get('books');

        echo '<br>';

        echo $this->builder
            ->order_by('title', 'ASC')
            ->get('books');

        echo '<br>';

        echo $this->builder
            ->where('book_id', '=', $bookId)
            ->delete('books');

        echo '<br>';

        echo $this->builder
            ->delete_all('books');

        echo '<br>';

        echo $this->builder
            ->truncate('books');

        echo '<br>';

        echo $this->builder
            ->distinct()
            ->select('title')
            ->from('books')
            ->get();

        echo '<br>';

        echo $this->builder
            ->set('title', 'a book title')
            ->set('publish_date', '2013-10-14')
            ->insert('books');

        echo '<br>';

        echo $this->builder
            ->select(array('book_id', 'title'))
            ->order_by('title', 'ASC')
            ->limit(50)
            ->get('books');

        echo '<br>';

        echo $this->builder
            ->count('books');
    }
}