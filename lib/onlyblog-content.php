<?php
//  Copyright 2009 Mahesh Asolkar
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
// OnlyBlog content handling related functions
//

//
// Populate the $__blog_data_items array with qualifying blog data
// file names
//
function find_blog_data_files () {
  global $__config, $__status, $__blog_data_items;

  $data_dir = $__config['blog_data_dir'];

  //
  // Find blog data files in $__config['blog_data_dir'] as specified in the
  // blog configuration. A blog data file is any file in this directory that is
  // of type 'file', is readable and has an extension as defined in
  // $__config['post_file_extension']
  //
  if (is_dir($data_dir)) {
    if ($dh = opendir($data_dir)) {
      while (($file = readdir($dh)) !== false) {
        $data_file = $data_dir . '/' . $file;
        if (is_file($data_file)
            && is_readable($data_file)
            && preg_match('/\.' . $__config['post_file_extension'] . '$/', $file)) {

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

  setup_list_links ();
}

function setup_list_links () {
  global $__blog_data_items, $__status;

  $first_key = reset ($__blog_data_items);
  if ($first_key == FALSE) {
    return;
  }

  $__status['debug'] .= "  /**/ Linking " . count($__blog_data_items) . " items\n";

  $data_keys = array_keys($__blog_data_items);
  for ($post_id = 0; $post_id < sizeof($data_keys); $post_id ++) {
    $prev_key = $data_keys[$post_id-1];
    $curr_key = $data_keys[$post_id];
    $next_key = $data_keys[$post_id+1];
    if ($post_id == 0) { // First
      $__blog_data_items[$curr_key]['key_prev_post'] = '';
      $__blog_data_items[$curr_key]['key_next_post'] = $__blog_data_items[$next_key]['time'];
    } else if ($post_id == (sizeof($data_keys)-1)) { // Last
      $__blog_data_items[$curr_key]['key_prev_post'] = $__blog_data_items[$prev_key]['time'];
      $__blog_data_items[$curr_key]['key_next_post'] = '';
    } else { // Middle
      $__blog_data_items[$curr_key]['key_prev_post'] = $__blog_data_items[$prev_key]['time'];
      $__blog_data_items[$curr_key]['key_next_post'] = $__blog_data_items[$next_key]['time'];
    }
  }
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


?>
