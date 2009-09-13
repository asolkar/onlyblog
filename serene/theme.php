<?php

//  Copyright 2008 Mahesh Asolkar
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.

//
// Serene Theme functions
//

function http_doc_type() {
  echo '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
}

function page_header () {
  global $__status, $__config;

  echo <<<END
    <div id="page_header">
      <div id="blog_name"><a href="{$__config['blog_url']}" title="Go to blog home"><h1>{$__config['blog_name']}</h1></a></div>
      <div id="blog_tag_line"><h2>{$__config['blog_tag_line']}</h3></div>
    </div>
END;
}
function page_footer () {
  global $__status, $__config;

  echo <<<END
    <div id="page_footer">
      <a href="{$__config['blog_url']}">{$__config['blog_name']}</a> is
      an <a href="http://onlyblog.googlecode.com/">OnlyBlog</a> blog
      using the Serene theme.
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
  <link rel="alternate" type="application/rss+xml"
        href="{$__config['blog_url']}/feed.php"
        title="{$__config['blog_name']} feed" />
  <link rel="StyleSheet" href="{$__config['theme_dir']}/{$__config['css_file']}"
        type="text/css" title="Serene Design Style">

  <script type="text/javascript" language="javascript" src="Library.js" />
  <script type="text/javascript">
  //<![CDATA[
  //]]>
  </script>
<script type="text/javascript">

   var disqus_developer = 1;

</script> </head>
<body>
<div id='shrink_wrapper'>
END;

  page_header();

  get_post_list();

  page_footer();

  echo <<<END
</div> <!-- shrink_wrapper -->
END;

  if (isset ($__config['intensedebate_blog_acct'])) {
    echo intense_debate_cmt_cnt_stub ("");
  }
  if (isset ($__config['disqus_blog_acct'])) {
    echo disqus_cmt_cnt_stub ("{$__config['blog_url']}?post={$data_item['data_file']}");
  }


  echo <<<END
</body>
</html>
END;
}
?>
