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
// Include library of functions
//
include 'lib/config.inc';
include 'config.inc';
include 'lib/onlyblog.php';
include $__config['theme_dir'] . "/theme.php";

blog_init();

sanity_check();

if ($__status['blog_setup_ok'] == 1) {
  show_page();
} else {
  show_setup_help_page();
}
?>
