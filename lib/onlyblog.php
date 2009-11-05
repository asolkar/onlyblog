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
include 'lib/onlyblog-content.php';
include 'lib/onlyblog-caching.php';

$__status['page_type']          = 'index';
$__status['page_title']         = 'New OnlyBlog';

$__blog_data_items              = array();
$__selected_item_keys           = array();
$__query_params                 = array();

//
// Handle requests
//
function blog_init () {
  global $__status, $__config, $__query_params;

  $__status['page_title'] = $__config['blog_name'];
  $__status['debug'] = '';
  $__status['page_start'] = 0;

  //
  // Handle requests
  //
  if (isset ($_GET['post'])) {
    $__status['page_type'] = 'single_post';
    $__status['data_file'] = $_GET['post'];
  } elseif (isset ($_GET['tag'])) {
    $__status['page_type'] = 'tagged_posts';
    $__status['tag'] = $_GET['tag'];
    $__query_params['tag'] = $_GET['tag'];
  } else {
    if (isset ($_POST['action'])) {
    } else {
      // Page type is 'main'
    }
  }
  if (isset ($_GET['fp'])) {
    $__status['page_start'] = $_GET['fp'];
    $__status['debug'] .= "  /**/ Starting with post " . $__status['page_start'] . "\n";
  }
}

function get_post_list() {
  global $__blog_data_items, $__selected_item_keys;
  global $__status, $__config;

  //
  // Update the cache file if it is stale
  //
  if (is_cache_stale()) {
    update_cache_file();
  }

  //
  // De-serialize data from the cache file to get
  // post list
  //
  load_cache_file ();

  //
  // Refine the list based on query
  //
  refine_data_items ();
  $__status['debug'] .= "  /**/ After refining " . count($__selected_item_keys) . " items to display\n";

  //
  // If we are displaying a single post, now set the title of the page
  //
  if ($__status['page_type'] == 'single_post') {
    $__status['debug'] .= "  /**/ Displaying single post with " . count($__selected_item_keys) . " items\n";
    foreach ($__selected_item_keys as $data_key) {
      $__status['page_title'] = $__blog_data_items[$data_key]['header_title'];
    }
  }
}

function show_post_list() {
  global $__blog_data_items, $__selected_item_keys, $__query_params;
  global $__status, $__config;

  $last_post_id = 0;

?>
  <div class='entry_list'> <!-- page_type = {$__status['page_type']} -->
<?php
  //
  // Using for loop instead of foreach, for easier pagination
  //
  $data_keys = array_keys($__selected_item_keys);
  for ($post_id = 0; $post_id < sizeof($data_keys); $post_id ++) {
    if (($post_id >= $__status['page_start'])
        && ($post_id < ($__status['page_start'] + $__config['posts_per_page']))) {
      $data_item = $__blog_data_items[$data_keys[$post_id]];
      show_data_item ($data_item);
      $__status['debug'] .= "  /**/ Showing $post_id (key={$data_keys[$post_id]})\n";
      $last_post_id = $post_id;
    }
  }
?>
  </div><!-- entry_list -->
<?php
  show_page_nav ();
}

//
// Show pagination links.
// - If in list display mode, show 'older'/'newer' links
// - If in single post mode, show 'previous title'/'next title' links [TODO]
//
function show_page_nav () {
  global $__status;

  if ($__status['page_type'] == 'single_post') {
    show_single_post_page_nav ();
  } else {
    show_list_page_nav();
  }
}

function show_single_post_page_nav () {
  global $__blog_data_items, $__query_params, $__selected_item_keys;
  global $__status, $__config;

  $data_keys = array_keys($__selected_item_keys);
  $current_key = reset($__selected_item_keys);
  $data_item = $__blog_data_items[$current_key];

  $older_item = FALSE;
  $newer_item = FALSE;
  if ($data_item['key_next_post'] != FALSE) {
    $older_item = $__blog_data_items[$data_item['key_next_post']];
  }
  if ($data_item['key_prev_post'] != FALSE) {
    $newer_item = $__blog_data_items[$data_item['key_prev_post']];
  }
?>
  <br class='clear'>
  <div class='page_nav'>
<?php
  //
  // Display 'Older Post' link
  //
  if ($older_item != FALSE) {
    $__query_params['post'] = $older_item['data_file'];
    $query_str = http_build_query($__query_params);
?>
    <div class='page_nav_older page_nav_active'>
      <a href="<?php echo $__config['blog_url'] ?>?<?php echo $query_str ?>">&laquo; <?php echo $older_item['header_title'] ?></a>
<?php
  } else {
?>
    <div class='page_nav_older page_nav_inactive'>
      &laquo; At oldest post
<?php
  }

?>
    </div> <!-- page_nav_older -->
    <div class='page_nav_home'>
      <a href="<?php echo $__config['blog_url'] ?>">Home</a>
    </div> <!-- page_nav -->
<?php
  //
  // Display 'Newer Post' link
  //
  if ($newer_item != FALSE) {
    $__query_params['post'] = $newer_item['data_file'];
    $query_str = http_build_query($__query_params);
?>
    <div class='page_nav_newer page_nav_active'>
      <a href="<?php echo $__config['blog_url'] ?>?<?php echo $query_str ?>"><?php echo $newer_item['header_title'] ?> &laquo;</a>
<?php
  } else {
?>
    <div class='page_nav_newer page_nav_inactive'>
      At newest post &raquo;
<?php
  }
?>
    </div> <!-- page_nav_newer -->
  </div> <!-- page_nav -->
  <br class='clear'>
<?php
}
function show_list_page_nav () {
  global $__blog_data_items, $__query_params, $__selected_item_keys;
  global $__status, $__config;

  $data_keys = array_keys($__selected_item_keys);
  $last_post_id = ($__status['page_start'] + $__config['posts_per_page']);

?>
  <br class='clear'>
  <div class='page_nav'>
<?php
  //
  // Display 'Older Posts' link
  //
  if (sizeof($data_keys) > ($last_post_id)) {
    //
    // More posts to display
    //
    $__query_params['fp'] = $last_post_id;
    $query_str = http_build_query($__query_params);
?>
    <div class='page_nav_older page_nav_active'>
      <a href="<?php echo $__config['blog_url'] ?>?<?php echo $query_str ?>">&laquo; Older Posts</a>
<?php
  } else {
?>
    <div class='page_nav_older page_nav_inactive'>
      &laquo; Older Posts
<?php
  }

?>
    </div> <!-- page_nav_older -->
    <div class='page_nav_home'>
      <a href="<?php echo $__config['blog_url'] ?>">Home</a>
    </div> <!-- page_nav -->
<?php
  //
  // Display 'Older Posts' link
  //
  if ($__status['page_start'] > 0) {
    //
    // More posts to display
    //
    $__query_params['fp'] = $__status['page_start'] - $__config['posts_per_page'];
    if ($__query_params['fp'] <= 0 ) {
      unset ($__query_params['fp']);
    }
    $query_str = http_build_query($__query_params);
?>
    <div class='page_nav_newer page_nav_active'>
      <a href="<?php echo $__config['blog_url'] ?>?<?php echo $query_str ?>">Newer Posts &raquo;</a>
<?php
  } else {
?>
    <div class='page_nav_newer page_nav_inactive'>
      Newer Posts &raquo;
<?php
  }
?>
    </div> <!-- page_nav_newer -->
  </div> <!-- page_nav -->
  <br class='clear'>
<?php
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
?>
  <div class='entry'>
    <div class='entry_header'>
      <?php echo $header ?>
    </div><!-- entry_header -->
    <br class="header_body_separator">
    <div class='entry_body'>
      <?php echo $entry ?>
    </div><!-- entry_body -->
  </div><!-- entry -->
<?php

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

function single_post ($data_item) {
  global $__status;

  return ($data_item['data_file'] == $__status['data_file']);
}

function tagged_posts ($data_item) {
  global $__status;

  return in_array ($__status['tag'], $data_item['header_tags']);
}

function get_item_key ($data_item) {
  global $__status;

  return $data_item['time'];
}

function refine_data_items () {
  global $__blog_data_items, $__selected_item_keys;
  global $__status;

  $refined_data_items = $__blog_data_items;

  if ($__status['page_type'] == 'single_post') {
    $refined_data_items = array_filter ($__blog_data_items, 'single_post');
  } elseif ($__status['page_type'] == 'tagged_posts') {
    $refined_data_items = array_filter ($__blog_data_items, 'tagged_posts');
  } else {
    // All posts
  }

  $__selected_item_keys = array_map ('get_item_key', $refined_data_items);
  $__status['debug'] .= "  /**/ After mapping refined " . count($__selected_item_keys) . " items to display\n";
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
