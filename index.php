<?php

//
// Include library of functions
//
include 'lib/config.inc';
include 'config.inc';
include 'lib/onlyblog.inc';

blog_init();
?>
<?php

echo '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title><?php echo $__status['page_title'] ?></title>
  </title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

  <link rel="StyleSheet" href="<?php echo $__config['css_file'] ?>" type="text/css" title="Design Style">

  <script type="text/javascript" language="javascript" src="Library.js" />
  <script type="text/javascript">
  //<![CDATA[
  //]]>
  </script>
</head>
<body>
<h1><?php echo $__config['blog_name'] ?></h1>
<?php
 get_post_list()
?>
</body>
</html>
