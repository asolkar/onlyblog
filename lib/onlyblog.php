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
// OnlyBlog Library
//
include 'lib/onlyblog-caching.php';

$__status['page_type']          = 'index';
$__status['page_title']         = 'New OnlyBlog';

$__blog_data_items              = array();

//
// Handle requests
//
function blog_init () {
  global $__status, $__config;

  $__status['page_title'] = $__config['blog_name'];
  //
  // Handle requests
  //
  if (isset ($_GET['post'])) {
    $__status['page_type'] = 'single_post';
    $__status['data_file'] = $_GET['post'];
  } elseif (isset ($_GET['tag'])) {
    $__status['page_type'] = 'tagged_posts';
    $__status['tag'] = $_GET['tag'];
  } else {
    if (isset ($_POST['action'])) {
    } else {
      // Page type is 'main'
    }
  }
}

function get_post_list() {
  global $__blog_data_items;
  global $__status, $__config;

  if ($__status['page_type'] == 'single_post') {
    single_blog_data_file ($__status['data_file']);
  } else {
    //
    // Populate the $__blog_data_items array with qualifying blog data
    // file names
    //
    find_blog_data_files ();

    //
    // Refine the list based on query
    //
    refine_data_items ();
  }
}

function show_post_list() {
  global $__blog_data_items;
  global $__status, $__config;

  echo <<<END
  <div class='entry_list'> <!-- page_type = {$__status['page_type']} -->
END;
  foreach ($__blog_data_items as $data_item) {
    show_data_item ($data_item);
  }
  echo <<<END
  </div><!-- entry_list -->
END;
}

function show_data_item ($data_item) {
  global $__status, $__config;

  //
  // Get the display version of the raw entry text
  //
  if ($__config['transform_text'] == 1) {
    $entry = transform_text ($data_item['entry']);
  }

  //
  // Get the display version of the raw header text
  //
  $header = format_header ($data_item);

  //
  // Display the post
  //
  echo <<<END
  <div class='entry'>
    <div class='entry_header'>
      $header
    </div><!-- entry_header -->
    <br class="header_body_separator">
    <div class='entry_body'>
      $entry
    </div><!-- entry_body -->
  </div><!-- entry -->
END;

  if (isset ($__config['intensedebate_blog_acct'])) {
    if ($__status['page_type'] == 'single_post') {
      echo intense_debate_stub ("{$__config['blog_url']}?post={$data_item['data_file']}");
    } else {
      echo "<span class=\"comments\"><a href=\"{$__config['blog_url']}?post={$data_item['data_file']}#respond\">Comments</a></span>";
    }
  }
  if (isset ($__config['disqus_blog_acct'])) {
    if ($__status['page_type'] == 'single_post') {
      echo disqus_stub ("{$__config['blog_url']}?post={$data_item['data_file']}");
    } else {
      echo "<span class=\"comments\"><a href=\"{$__config['blog_url']}?post={$data_item['data_file']}#disqus_thread\">Comments</a></span>";
    }
  }

}

function transform_text ($entry) {
  $entry = preg_replace ('/\n\n/', "\n<p>", $entry);

  return $entry;
}

function format_header ($data_item) {
  global $__config;

  $output = "";

  $parts = preg_split ('/\n/', $data_item['header']);

  if (isset ($data_item['header_title'])) {
    $output .= <<<END
    <div class='header_title'>
      <div class='header_type'>Title</div>
      <div class='header_value'><a href='{$__config['blog_url']}?post={$data_item['data_file']}'>
        <h3>{$data_item['header_title']}</h3></a></div>
    </div>
END;
  } else {
    echo "<b>No title</b>";
  }
  if (isset ($data_item['header_author'])) {
    $author = $data_item['header_author'];
    if (isset ($data_item['header_author_email'])) {
      $author = '<a href="mailto:' . $data_item['header_author_email'] . '">'
                . $data_item['header_author'] . '</a>';
    }
    if (isset ($data_item['time'])) {
      $author .= "<span class='header_time'> on " . date("F j, Y g:i a", $data_item['time']) . "</span>";
    }
    $output .= <<<END
    <div class='header_author'>
      <div class='header_type'>Author</div>
      <div class='header_value'>$author</div>
    </div>
END;
  }
  if (isset ($data_item['header_tags'])) {
    $tag_values = array();

    foreach ($data_item['header_tags'] as $tag) {
      array_push ($tag_values,
                  "<div class='header_tag'><a href='{$__config['blog_url']}?tag={$tag}'" .
                  " title='Show all posts tagged {$tag}'>" . $tag . "</a></div>");
    }

    $value = implode (', ', $tag_values);

    $output .= <<<END
    <div class='header_tags'>
      <div class='header_type'>$type</div>
      <div class='header_value'>$value</div>
    </div>
END;
  }

  return $output;
}

function find_blog_data_files () {
  global $__config;
  global $__blog_data_items;
  global $__status;

  $data_dir = $__config['blog_data_dir'];

  //
  // Find blog data files in $__config['blog_data_dir'] as specified in the
  // blog configuration. A blog data file is any file in this directory that is
  // of type 'file', is readable and has an extension '.blog'
  //
  if (is_dir($data_dir)) {
    if ($dh = opendir($data_dir)) {
      while (($file = readdir($dh)) !== false) {
        $data_file = $data_dir . '/' . $file;
        if (is_file($data_file)
            && is_readable($data_file)
            && preg_match('/\.blog$/', $file)) {

          $data_item = array ();

          //
          // We just found a blog data file. Now process it.
          //
          $stat = lstat ($data_file);
          $key = $stat['ctime'];
          $data_item['data_file'] = $file;

          $post = file_get_contents ($__config['blog_data_dir'] . '/' . $file);

          //
          // The first occurance of '--' separates the header and entry in a post
          //
          list ($data_item['header'], $data_item['entry'])
            = preg_split ('/^--/ms', $post, 2);

          //
          // Gather header data
          //
          get_header_data ($data_item);

          //
          // If post header has time, use it as key, else use change time
          // as assigned above
          //
          if (isset ($data_item['time'])) {
            $key = $data_item['time'];
          }

          $__blog_data_items[$key] = $data_item;
        }
      }
      closedir($dh);
    }
  }
  krsort ($__blog_data_items, SORT_NUMERIC);
}

function single_blog_data_file ($file) {
  global $__config;
  global $__blog_data_items;
  global $__status;

  $data_dir = $__config['blog_data_dir'];

  $data_item = array ();

  //
  // We just found a blog data file. Now process it.
  //
  $data_file = $data_dir . '/' . $file;
  $stat = lstat ($data_file);
  $key = $stat['ctime'];
  $data_item['data_file'] = $file;

  $post = file_get_contents ($__config['blog_data_dir'] . '/' . $file);

  //
  // The first occurance of '--' separates the header and entry in a post
  //
  list ($data_item['header'], $data_item['entry'])
    = preg_split ('/^--/ms', $post, 2);

  //
  // Gather header data
  //
  get_header_data ($data_item);
  $__status['page_title'] = $data_item['header_title'];


  //
  // If post header has time, use it as key, else use change time
  // as assigned above
  //
  if (isset ($data_item['time'])) {
    $key = $data_item['time'];
  }

  $__blog_data_items[$key] = $data_item;
}

function get_header_data (&$data_item) {
  $parts = preg_split ('/\n/', $data_item['header']);

  foreach ($parts as $part) {
    if (!preg_match ('/^\s*$/', $part)) {
      list ($type, $value) = preg_split ('/:/', $part, 2);
      $type = preg_replace (array ('/^\s+/','/\s+$/'), '', $type);
      $value = preg_replace (array ('/^\s+/','/\s+$/'), '', $value);

      if ($type == "Title") {
        $data_item['header_title'] = $value;
      } elseif ($type == "Author") {
        $data_item['header_author'] = $value;
        if (preg_match ('/(.*?)\s*<\s*(.*?@.*)\s*>/', $value, $author_info)) {
          $data_item['header_author'] = $author_info[1];
          $data_item['header_author_email'] = $author_info[2];
        }
      } elseif ($type == "Tags") {
        $value = preg_replace (array ('/^\s+/','/\s+$/'), '', $value);
        $data_item['header_tags'] = preg_split ('/\s*,\s*/', $value);
      } elseif ($type == "Time") {
        $value = preg_replace (array ('/^\s+/','/\s+$/'), '', $value);
        $data_item['time'] = strtotime ($value);
      } else {
        $data_item['header_'.$type] = $value;
      }
    }
  }
  if (isset($data_item['time'])) {
    $key = $data_item['time'];
  }
}

function single_post ($data_item) {
  global $__status;

  return ($data_item['data_file'] == $__status['data_file']);
}

function tagged_posts ($data_item) {
  global $__status;

  return in_array ($__status['tag'], $data_item['header_tags']);
}

function refine_data_items () {
  global $__blog_data_items;
  global $__status;

  if ($__status['page_type'] == 'single_post') {
    $__blog_data_items = array_filter ($__blog_data_items, 'single_post');
  } elseif ($__status['page_type'] == 'tagged_posts') {
    $__blog_data_items = array_filter ($__blog_data_items, 'tagged_posts');
  } else {
    // All posts
  }
}

//
// IntenseDebate stubs
//
function intense_debate_stub ($post_url) {
  global $__status, $__config;

  $output = "";

  // var idcomments_post_id ='{$post_url}';

  $output .= <<<END
<script>
var idcomments_acct = '{$__config["intensedebate_blog_acct"]}';
var idcomments_post_id;
var idcomments_post_url = '{$post_url}';
</script>
<span id="IDCommentsPostTitle" style="display:none"></span>
<script type='text/javascript' src='http://www.intensedebate.com/js/genericCommentWrapperV2.js'></script>
END;

  return $output;
}

function intense_debate_cmt_cnt_stub ($post_url) {
  global $__status, $__config;

  $output = "";

  // var idcomments_post_id ='{$post_url}';

  $output .= <<<END
<script>
var idcomments_acct = '{$__config["intensedebate_blog_acct"]}';
var idcomments_post_id;
var idcomments_post_url = '{$post_url}';
</script>
<script type="text/javascript" src="http://www.intensedebate.com/js/genericLinkWrapperV2.js"></script>
END;

  return $output;
}

//
// Disqus stubs
//
function disqus_stub ($post_url) {
  global $__status, $__config;

  $output = "";

  $output .= <<<END
<div id="disqus_thread"></div><script type="text/javascript" src="http://disqus.com/forums/{$__config['disqus_blog_acct']}/embed.js"></script><noscript><a href="http://{$__config['disqus_blog_acct']}.disqus.com/?url=ref">View the discussion thread.</a></noscript><a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
END;

  return $output;
}

function disqus_cmt_cnt_stub ($post_url) {
  global $__status, $__config;

  $output = "";

  $output .= <<<END
<script type="text/javascript">
//<![CDATA[
(function() {
	var links = document.getElementsByTagName('a');
	var query = '?';
	for(var i = 0; i < links.length; i++) {
	if(links[i].href.indexOf('#disqus_thread') >= 0) {
		query += 'url' + i + '=' + encodeURIComponent(links[i].href) + '&';
	}
	}
	document.write('<script charset="utf-8" type="text/javascript" src="http://disqus.com/forums/{$__config['disqus_blog_acct']}/get_num_replies.js' + query + '"></' + 'script>');
})();
//]]>
</script>
END;

  return $output;
}
?>
