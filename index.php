<?php

//
// Include library of functions
//
include ("lib/onlyblog.inc");
include ("config.inc");

//
// Handle requests
//
$page = "";

if (isset ($_POST['action'])) {
} else {
  if (isset ($_GET['action'])) {
  } else {
    $page .= "<h1>Serenity here...</h1>";
  }
}

?>
<?php
//
// Display a page
//

echo '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php
  echo "<title> OnlyBlog </title>\n";
?>
  </title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

  <link rel="StyleSheet" href="/style.css" type="text/css" title="MMA Library Design Style">

  <script type="text/javascript" language="javascript" src="Library.js" />
  <script type="text/javascript">
  //<![CDATA[
  //]]>
  </script>
</head>

<body>
<?php
echo $page;

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('data'));

foreach($files as $file) {
  echo "$file<br>\n";
}
?>

</body>
</html>
