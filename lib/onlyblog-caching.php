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
// OnlyBlog caching (indexing) related functions
//

//
// Calculate and return the SHA1 of blog entries
//
function get_data_sha1 () {
  global $__status, $__config;

  $context = hash_init('sha1');
  chdir($__config['blog_data_dir']);
  foreach (glob("*.blog") as $filename) {
    hash_update_file($context, $filename);
  }
  chdir($__config['blog_base_dir']);
  return hash_final($context);
}

//
// Read the sha1_file and return the stored SHA1 of blog entries
//
function get_stored_sha1 () {
  global $__status, $__config;

  $sha1_file = $__config['blog_data_dir'] . '/' . $__config['sha1_file'];

  if (file_exists ($sha1_file)) {
    return trim(file_get_contents ($sha1_file));
  } else {
    return '';
  }
}

//
// Check if blog data (.blog files in blog_data_dir) has changed
// in any way - or in other words, is cache stale?
//
function is_blog_data_changed () {
  global $__status, $__config;

  return (get_data_sha1() != get_stored_sha1());
}
function is_cache_stale () {
  return is_blog_data_changed();
}
?>
