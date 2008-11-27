<?php

function http_doc_type() {
  echo '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
}

function page_header () {
  global $__status, $__config;

  echo <<<END
    <div id="page_header">
      <div id="blog_name"><h1>{$__config['blog_name']}</h1></div>
      <div id="blog_tag_line"><h3>{$__config['blog_tag_line']}</h3></div>
    </div>
END;
}
function page_footer () {
  global $__status, $__config;

  echo <<<END
    <div id="page_footer">
      {$__config['blog_name']} is an OnlyBlog blog using the Serene theme.
    </div>
END;
}

function index_page () {
  global $__status, $__config;

  echo <<<END
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title>{$__status['page_title']}</title>
  </title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

  <link rel="StyleSheet" href="{$__config['theme_dir']}/{$__config['css_file']}"
        type="text/css" title="Serene Design Style">

  <script type="text/javascript" language="javascript" src="Library.js" />
  <script type="text/javascript">
  //<![CDATA[
  //]]>
  </script>
</head>
<body>
END;

  page_header();

  get_post_list();

  page_footer();

  echo <<<END
</body>
</html>
END;
}
?>
