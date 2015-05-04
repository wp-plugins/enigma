<?php
/*
Plugin Name: Enigma
Plugin URI: https://leonax.net/
Description: Enigma encrypts any text on demand on server and decrypts in browser to avoid censorship. 
Author: Shuhai Shen
Version: 2.3
Author URI: https://leonax.net/
License: MIT
*/

$enigma_script_handle = 'engima_script';
$enigma_js_name = 'enigma.js';

add_action('init', 'enigma_init');

add_shortcode('enigma', 'enigma_process');

function enigma_init(){
  global $enigma_js_name;
  global $enigma_script_handle;

  if (!is_admin()) {
    wp_enqueue_script(
      $enigma_script_handle,
      plugins_url( $enigma_js_name , __FILE__ ),
      array( 'jquery' ),
      false,
      true);
  }
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
  return '\\u' . str_pad(dechex($dec), 4, '0', STR_PAD_LEFT);
}

function enigma_encode($content, $text, $ondemand) {
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
  while ($idx < $len) {
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