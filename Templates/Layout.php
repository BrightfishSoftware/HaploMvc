<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php $this->region('title'); ?>Title<?php $this->end_region(); ?></title>
    </head>
    <body>
        <?php $this->region('body'); ?>
            <p>Body</p>
        <?php $this->end_region(); ?>
        <?php $this->region('footer'); ?>
            <p>Footer</p>
        <?php $this->end_region(); ?>
    </body>
</html>