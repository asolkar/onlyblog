<?php
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

  find_blog_data_files ();

  foreach ($__blog_data_files as $data_file) {
    $entry_txt = file_get_contents ($__config['blog_data_dir'] . '/' . $data_file);

    $post = preg_split ('/--/', $entry_txt, 2);
    $header = $post[0];
    $entry = $post[1];

    if ($__config['transform_text'] == 1) {
      $entry = transform_text ($entry);
    }

    echo "<div class='entry'>";
    echo "<pre>" . $header . "</pre>";
    echo "<hr>\n";
    echo $entry . "\n";
    echo "</div>\n";
    echo "<hr>\n";
  }
}

function transform_text ($entry) {
  $entry = preg_replace ('/\n\n/', "\n<p>", $entry);

  return $entry;
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
