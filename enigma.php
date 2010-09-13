<?php
/*
Plugin Name: Enigma
Plugin URI: http://www.leonax.net/
Description: Enigma encrypts any text (if you want) on server and decrypts it on client (using javascript) to avoid your email and any other sensitive content being understood by robots and net filters. Simply add [enigma]...[/enigma] shortcode to encypt your blog.
Author: Shuhai Shen
Version: 1.0
Author URI: http://www.leonax.net/
*/

/* LICENSE (MIT)
Copyright (c) 2009 - 2010 Shuhai Shen

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

/* 
Use short code [enigma]...[/enigma] to encrypt any of your blog.

Samples:
1. [enigma]content I don't want search engine to catch.[/enigma]
    The content inside engima tag will be encrypted and search engines
    get nothing.
2. [enigma text="Hello World"]Actual Text[/enigma]
    Search engine will only see "Hello World" but normal user will see
    "Actual Text".
*/

$enigma_path = NULL;
$enigma_script_handle = 'engima_script';
$enigma_js_name = 'enigma.js';

add_action('init', 'enigma_init');

add_shortcode('enigma', 'enigma_process');

function enigma_init(){
    global $enigma_path;
    global $enigma_js_name;
    global $enigma_script_handle;
    
    $enigma_path = WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__));
    wp_register_script($enigma_script_handle, $enigma_path . $enigma_js_name);
    wp_enqueue_script($enigma_script_handle);
    
}

function enigma_ord($str, $len = -1, $idx = 0, &$bytes = 0){
    if ($len == -1){
        $len = strlen(str);
    }
    $h = ord($str{$idx});

    if ($h <= 0x7F) {
        $bytes = 1;
        return $h;
    }
    else if ($h < 0xC2) {
        return false;
    }
    else if ($h <= 0xDF && $idx < $len - 1) {
        $bytes = 2;
        return ($h & 0x1F) <<  6 | (ord($str{$idx + 1}) & 0x3F);
    }
    else if ($h <= 0xEF && $idx < $len - 2) {
        $bytes = 3;
        return ($h & 0x0F) << 12 | (ord($str{$idx + 1}) & 0x3F) << 6
                                 | (ord($str{$idx + 2}) & 0x3F);
    }           
    else if ($h <= 0xF4 && $idx < $len - 3) {
        $bytes = 4;
        return ($h & 0x0F) << 18 | (ord($str{$idx + 1}) & 0x3F) << 12
                                 | (ord($str{$idx + 2}) & 0x3F) << 6
                                 | (ord($str{$idx + 3}) & 0x3F);
    }    
    return false;
}

function enigma_encode($content, $text = NULL){
    if ($content == NULL){
        return "";
    }
    
    $len = strlen($content);
    
    if ($len == 0){
        return "";
    }
    
    $idx = 0;
    $result = enigma_ord($content, $len, $idx, $idx);
    while ( $idx < $len){
        $bytes = 0;
        $result .= ' ';
        $result .= enigma_ord($content, $len, $idx, $bytes);
        $idx += $bytes;
    }
    
    $divid = "engimadiv";
    
    for ($i = 0; $i < 10; ++$i) {
        $divid .= rand(0, 10);
    }

    $js = <<<EOT
<span id="$divid">$text</span>
<script type="text/javascript">
enigma.decode("$result", "$divid");
</script>
EOT;
    
    return $js;
}

function enigma_process($attr, $content = NULL){
    extract(
        shortcode_atts(
            array(
                'text' => ''
            ),
            $attr
        )
    );
    return enigma_encode($content, $text);
}

?>