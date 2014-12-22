<?php
/*
Plugin Name: Enigma
Plugin URI: https://www.leonax.net/
Description: Enigma encrypts any text (if you want) on server and decrypts it on client (using javascript) to avoid your email and any other sensitive content being understood by robots and net filters. Simply add [enigma]...[/enigma] shortcode to encypt your blog.
Author: Shuhai Shen
Version: 2.1
Author URI: https://www.leonax.net/
*/

/* LICENSE (MIT)
Copyright (c) 2009 - 2015 Shuhai Shen

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
*/

$enigma_script_handle = 'engima_script';
$enigma_js_name = 'enigma.js';

add_action('init', 'enigma_init');

add_shortcode('enigma', 'enigma_process');

function enigma_init(){
  global $enigma_js_name;
  global $enigma_script_handle;

  wp_enqueue_script(
    $enigma_script_handle,
    plugins_url( $enigma_js_name , __FILE__ ),
    array( 'jquery' ),
    false,
    true);
}

function enigma_ord($str, $len = -1, $idx = 0, &$bytes = 0){
  if ($len == -1){
    $len = strlen(str);
  }
  $h = ord($str{$idx});

  if ($h <= 0x7F) {
    $bytes = 1;
    return $h;
  } else if ($h < 0xC2) {
    return false;
  } else if ($h <= 0xDF && $idx < $len - 1) {
    $bytes = 2;
    return ($h & 0x1F) <<  6 | (ord($str{$idx + 1}) & 0x3F);
  } else if ($h <= 0xEF && $idx < $len - 2) {
    $bytes = 3;
    return ($h & 0x0F) << 12 | (ord($str{$idx + 1}) & 0x3F) << 6
                             | (ord($str{$idx + 2}) & 0x3F);
  } else if ($h <= 0xF4 && $idx < $len - 3) {
    $bytes = 4;
    return ($h & 0x0F) << 18 | (ord($str{$idx + 1}) & 0x3F) << 12
                             | (ord($str{$idx + 2}) & 0x3F) << 6
                             | (ord($str{$idx + 3}) & 0x3F);
  }
  return false;
}

function enigma_unicode($dec) {
  $hex = dechex($dec);
  if ($dec < 16) {
	return '\\x0' . $hex;
  }
  if ($dec < 256) {
    return '\\x' . $hex;
  }
  if ($dec < 4096) {
	return '\\u0' . $hex;
  }

  return '\\u' . $hex;
}

function enigma_encode($content, $text = "", $ondemand = 'n'){
  if ($content == NULL || is_feed()){
    return $text;
  }

  $len = strlen($content);

  if ($len == 0) {
    return "";
  }

  $idx = 0;

  $ord = enigma_ord($content, $len, $idx, $idx);
  $script = enigma_unicode($ord);
  while ( $idx < $len){
    $bytes = 0;
    $script .= enigma_unicode(enigma_ord($content, $len, $idx, $bytes));
    $idx += $bytes;
  }

  $divid = uniqid("engimadiv");
  $js = "<span id='$divid' data-enigmav='$script' data-enigmad='$ondemand'>$text</span>";

  return $js;
}

function enigma_process($attr, $content = NULL) {
  extract(
    shortcode_atts(
      array(
        'text' => '',
        'ondemand' => 'n'
      ),
    $attr)
  );
  return enigma_encode($content, $text, $ondemand);
}

?>