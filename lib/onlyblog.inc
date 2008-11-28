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

$__blog_data_files              = array();

//
// Handle requests
//
function blog_init () {
  global $__status, $__config;

  $__status['page_title'] = $__config['blog_name'];
  //
  // Handle requests
  //
  if (isset ($_POST['action'])) {
  } else {
    if (isset ($_GET['action'])) {
    } else {
      // Page type is 'main'
    }
  }
}

function get_post_list() {
  global $__blog_data_files;
  global $__config;

  //
  // Populate the $__blog_data_files array with qualifying blog data
  // file names
  //
  find_blog_data_files ();

  echo <<<END
  <div class='entry_list'>
END;
  foreach ($__blog_data_files as $data_file) {
    $post = file_get_contents ($__config['blog_data_dir'] . '/' . $data_file);

    //
    // The first occurance of '--' separates the header and entry in a post
    //
    list ($header, $entry) = preg_split ('/^--$/ms', $post, 2);

    //
    // Get the display version of the raw entry text
    //
    if ($__config['transform_text'] == 1) {
      $entry = transform_text ($entry);
    }

    //
    // Get the display version of the raw header text
    //
    $header = format_header ($header);

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

function format_header ($header) {
  $output = "";

  $parts = preg_split ('/\n/', $header);

  foreach ($parts as $part) {
    if (!preg_match ('/^\s*$/', $part)) {
      $output .= format_header_part($part);
    }
  }
  return $output;
}

function format_header_part ($part) {
  $output = "";

  list ($type, $value) = preg_split ('/:/', $part, 2);
  $type = preg_replace (array ('/^\s+/','/\s+$/'), '', $type);
  $value = preg_replace (array ('/^\s+/','/\s+$/'), '', $value);

  //
  // Title does not require any transformation
  //
  if ($type == "Title") {
    $output .= <<<END
    <div class='header_title'>
      <div class='header_type'>$type</div>
      <div class='header_value'>$value</div>
    </div>
END;
  //
  // If email is provided, make the author name a 'mailto:'
  // hyperlink. Otherwise, just display the name
  //
  } elseif ($type == "Author") {
    if (preg_match ('/(.*?)\s*<\s*(.*?@.*)\s*>/', $value, $author_info)) {
      $value = '<a href="mailto:' . $author_info[2] . '">' . $author_info[1] . '</a>';
    }

    $output .= <<<END
    <div class='header_author'>
      <div class='header_type'>$type</div>
      <div class='header_value'>$value</div>
    </div>
END;
  //
  // Tags is a comma-separated list. Display the tags. Later,
  // each tag will be a hyperlink to a tag-filter url
  //
  } elseif ($type == "Tags") {
    $value = preg_replace (array ('/^\s+/','/\s+$/'), '', $value);
    $tags = preg_split ('/\s*,\s*/', $value);

    //
    // Use a referenced foreach, since we want to modify the array
    //
    foreach ($tags as &$tag) {
      $tag = "<div class='header_tag'>" . $tag . "</div>";
    }
    unset ($tag);

    $value = implode (', ', $tags);

    $output .= <<<END
    <div class='header_tags'>
      <div class='header_type'>$type</div>
      <div class='header_value'>$value</div>
    </div>
END;
  //
  // All the other (yet unknown) headers are displayed as they are.
  //
  } else {
    $output .= <<<END
    <div class='header_other'>
      <div class='header_type'>$type</div>
      <div class='header_value'>$value</div>
    </div>
END;
  }
  return $output;
}

function find_blog_data_files () {
  global $__config;
  global $__blog_data_files;

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
          $__blog_data_files[$stat['ctime']] = $file;
        }
      }
      closedir($dh);
    }
  }
}
?>
