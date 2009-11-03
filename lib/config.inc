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
// Default OnlyBlog Configuration
//
$__config['blog_name']          = 'A OnlyBlog Blog';
$__config['blog_base_dir']      = getcwd();
$__config['blog_data_dir']      = 'ob_data';
$__config['blog_tag_line']      = 'Just another OnlyBlog Blog';

$__config['version']            = '0.1';
$__config['css_file']           = 'style.css';
$__config['transform_text']     = 1;

//
// Following files are used to store caching/indexing related
// information. Files are relative to the blog_data_dir. They
// are placed in a 'meta' directory so that the directory can
// be given write permissions for the world. That is the only
// place when the webserver user would ever write to.
//
$__config['sha1_file']          = 'meta/.data_sha1';
$__config['cache_file']         = 'meta/.data_cache';

?>
