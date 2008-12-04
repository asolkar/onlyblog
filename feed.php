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

function blog_feed () {
  global $__status, $__config, $__status, $__blog_data_items;

  //
  // Populate the $__blog_data_items array with qualifying blog data
  // file names
  //
  find_blog_data_files ();

  $now = date("D, d M Y H:i:s T");

  header("Content-Type: application/rss+xml");

  echo <<<END
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	>

<channel>
  <title>{$__config['blog_name']}</title>
  <atom:link href="{$__config['blog_url']}" rel="self" type="application/rss+xml" />
  <link>{$__config['blog_url']}</link>
  <description>{$__config['blog_tag_line']}</description>
  <pubDate>$now</pubDate>

  <generator>http://onlyblog.googlecode.com/</generator>
  <language>en</language>
END;

  foreach ($__blog_data_items as $data_item) {
    $pub_date = date("D, d M Y H:i:s T", $data_item['time']);
    $excerpt = substr (strip_tags($data_item['entry']), 0, 200);
    $excerpt = preg_replace ('/(\w+)$/', '', $excerpt);

    echo <<<END
  <item>
    <title>{$data_item['header_title']}</title>
    <link>{$__config['blog_url']}?post={$data_item['data_file']}</link>
    <pubDate>$pub_date</pubDate>
    <description><![CDATA[$excerpt [...] ]]></description>
    <content:encoded><![CDATA[{$data_item['entry']}]]></content:encoded>
  </item>
END;
   }

  echo <<<END
</channel>
</xml>
END;
}

blog_feed();

?>
