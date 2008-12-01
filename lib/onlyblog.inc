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
  global $__config;

  //
  // Populate the $__blog_data_items array with qualifying blog data
  // file names
  //
  find_blog_data_files ();

  //
  // Refine the list based on query
  //
  refine_data_items ();

  echo <<<END
  <div class='entry_list'>
END;
  foreach ($__blog_data_items as $data_item) {
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
  }
  echo <<<END
  </div><!-- entry_list -->
END;
}

function transform_text ($entry) {
  $entry = preg_replace ('/\n\n/', "\n<p>", $entry);

  return $entry;
}

function format_header ($data_item) {
  $output = "";

  $parts = preg_split ('/\n/', $data_item['header']);

  if (isset ($data_item['header_title'])) {
    $output .= <<<END
    <div class='header_title'>
      <div class='header_type'>Title</div>
      <div class='header_value'><a href='/?post={$data_item['data_file']}'>
        <h3>{$data_item['header_title']}</h3></a></div>
    </div>
END;
  } else {
    echo "<b>No title<>/b>";
  }
  if (isset ($data_item['header_author'])) {
    $author = $data_item['header_author'];
    if (isset ($data_item['header_author_email'])) {
      $author = '<a href="mailto:' . $data_item['header_author_email'] . '">'
                . $data_item['header_author'] . '</a>';
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
                  "<div class='header_tag'><a href='/?tag={$tag}'" .
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
          //
          // We just found a blog data file. Now process it.
          //
          $stat = lstat ($data_file);
          $__blog_data_items[$stat['ctime']]['data_file'] = $file;

          $post = file_get_contents ($__config['blog_data_dir'] . '/' . $file);

          //
          // The first occurance of '--' separates the header and entry in a post
          //
          list ($__blog_data_items[$stat['ctime']]['header'],
                $__blog_data_items[$stat['ctime']]['entry'])
            = preg_split ('/^--$/ms', $post, 2);

          //
          // Gather header data
          //
          $parts = preg_split ('/\n/', $__blog_data_items[$stat['ctime']]['header']);

          foreach ($parts as $part) {
            if (!preg_match ('/^\s*$/', $part)) {
              list ($type, $value) = preg_split ('/:/', $part, 2);
              $type = preg_replace (array ('/^\s+/','/\s+$/'), '', $type);
              $value = preg_replace (array ('/^\s+/','/\s+$/'), '', $value);

              if ($type == "Title") {
                $__blog_data_items[$stat['ctime']]['header_title'] = $value;
              } elseif ($type == "Author") {
                $__blog_data_items[$stat['ctime']]['header_author'] = $value;
                if (preg_match ('/(.*?)\s*<\s*(.*?@.*)\s*>/', $value, $author_info)) {
                  $__blog_data_items[$stat['ctime']]['header_author'] = $author_info[1];
                  $__blog_data_items[$stat['ctime']]['header_author_email'] = $author_info[2];
                }
              } elseif ($type == "Tags") {
                $value = preg_replace (array ('/^\s+/','/\s+$/'), '', $value);
                $__blog_data_items[$stat['ctime']]['header_tags'] = preg_split ('/\s*,\s*/', $value);
              } else {
                $__blog_data_items[$stat['ctime']]['header_'.$type] = $value;
              }
            }
          }
        }
      }
      closedir($dh);
    }
  }
  krsort ($__blog_data_items, SORT_NUMERIC);
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
?>
