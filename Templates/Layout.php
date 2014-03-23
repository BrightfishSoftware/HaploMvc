<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php $this->region('title'); ?>Title<?php $this->endRegion(); ?></title>
    </head>
    <body>
        <?php $this->region('body'); ?>
            <p>Body</p>
        <?php $this->endRegion(); ?>
        <?php $this->region('footer'); ?>
            <p>Footer</p>
        <?php $this->endRegion(); ?>
    </body>
</html>