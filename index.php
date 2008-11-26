<?php

//
// Include library of functions
//
include 'lib/config.inc';
include 'config.inc';
include 'lib/onlyblog.inc';
include $__config['theme_dir'] . "/theme.php";

blog_init();

index_page();
?>
