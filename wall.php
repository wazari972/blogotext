<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2014 Timo Van Neerden <timo@neerden.eu>
#
# BlogoText is free software.
# You can redistribute it under the terms of the MIT / X11 Licence.
#
# *** LICENSE ***

header('Content-Type:  text/html; charset=UTF-8');

$GLOBALS['BT_ROOT_PATH'] = '';
error_reporting(-1);
$begin = microtime(TRUE);

$GLOBALS['dossier_cache'] = 'cache';

require_once 'inc/conf.php';

require_once $GLOBALS['dossier_config'].'/user.php';
require_once $GLOBALS['dossier_config'].'/prefs.php';

date_default_timezone_set($GLOBALS['fuseau_horaire']);

function require_all() {
	require_once 'inc/lang.php';
	require_once 'inc/conf.php';
	require_once 'inc/fich.php';
	require_once 'inc/html.php';
	require_once 'inc/form.php';
	require_once 'inc/comm.php';
	require_once 'inc/conv.php';
	require_once 'inc/util.php';
	require_once 'inc/veri.php';
	require_once 'inc/sqli.php';

    require_once 'inc/them.php';
}
require_all();

$GLOBALS['db_handle'] = open_base($GLOBALS['db_location']);
$array = array();
$ORDER = 'DESC'; // may be overwritten

$query = "SELECT * FROM articles WHERE bt_statut=1";

if (strpos($_SERVER["SERVER_NAME"], "polynesie.0x972.info") !== false) {
    if (!isset($_GET['tag'])) {
        $_GET['tag'] = 'polynesie';
        $ORDER = 'ASC';
    }
}

// ordre chronologique ?
if ($GLOBALS['old_first'] || isset($_GET['old_first']) && $_GET['old_first'] !== 'n' ) {
      $ORDER = "ASC ";
} else {
      $ORDER = "DESC ";
}

// paramètre de tag "tag"
if (isset($_GET['tag'])) {
    $sql_tag = "( bt_categories LIKE ? OR bt_categories LIKE ? OR bt_categories LIKE ? OR bt_categories LIKE ? ) ";
    $array[] = $_GET['tag'];
    $array[] = $_GET['tag'].', %';
    $array[] = '%, '.$_GET['tag'].', %';
    $array[] = '%, '.$_GET['tag'];

    $query .= ' AND '.$sql_tag;

    if(in_array(strtolower($_GET['tag']), array("polynesie", "chypre"))) {
        $ORDER = 'ASC';   
    } 
}



$query .= " ORDER BY bt_date $ORDER";

// paramètre de page "p"
$sql_p = '';
if (isset($_GET['p']) and is_numeric($_GET['p']) and $_GET['p'] >= 1) {
    $sql_p = ' LIMIT '.$GLOBALS['max_bill_acceuil'] * $_GET['p'].', '.$GLOBALS['max_bill_acceuil'];
} elseif (!isset($_GET['d']) ) {
    //$sql_p = ' LIMIT '.$GLOBALS['max_bill_acceuil'];
}

$query .= $sql_p;

$tableau = liste_elements($query, $array, 'articles');

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function addQuotes($str){
    return "'$str'";
}

function afficher_map_data($tableau) {
  $PICTO_PREFIX = "/themes/martinique/picto/";
  $MAP_TYPES = array (
    "plage", 
    "nature",
    "rando",
    "fleurs",
    "point de vue",
    "culture", 
    "plongee"
  );


  $data = array();

  $HTML_article = conversions_theme($theme_page, $data, 'post');

  $JSON_struc = "var page_locations = [\n";
        
  foreach ($tableau as $element) {
    if (empty($element['bt_notes'])) {
      //continue;
    } else if ($element['bt_notes'] == "skip") {
      continue;
    }

    $notes = explode('#@', $element['bt_notes']);
    if (!isset($notes[1])) continue;
    $head_img = $notes[0];
    $location = explode(",",$notes[1]);
            
    $categories = $element['bt_categories'];
                        
    $last_type = -1;
    $main_type = "default";
    $all_types = array();
                       
    foreach ($MAP_TYPES as $type_name) {
      $found = strrpos($categories, "#".$type_name);

      if ($found === false) continue;
      $type_name = str_replace(" ", "_", $type_name);
      
      if ($found > $last_type) {
        $last_type = $found;
        $main_type = $type_name;
      }
              
      array_push($all_types, $type_name);
    }

    $JSON_struc .= "      {"
                ."uid: '".$element['bt_id']."', "
                ."name: \"".$element['bt_title']."\", "
                ."lon: ".$location[0].", "
                ."lat: ".$location[1].", "
                ."main_type: '$main_type', "
                ."header: '$head_img', "
                ."types: [".implode(', ', array_map("addQuotes", $all_types))."]"
                ."},\n";
                        
        
            
  }
  $JSON_struc .= "];\n";
  
    
  return "<script> $JSON_struc </script> <div id='page_map'></div><div id='popup'></div>\n";
}

function afficher_map_imports() {
  $HTML_elmts = "<script src='//plongee.0x972.info/divebook.html_files/jquery.min.js'></script>"
              .'<script src="https://cdnjs.cloudflare.com/ajax/libs/ol3/3.17.1/ol.js" type="text/javascript"></script>'
              .'<link rel="stylesheet" href="//plongee.0x972.info/divebook.html_files/bootstrap.min.css" type="text/css">'
              .'<link rel="stylesheet" href="//plongee.0x972.info/divebook.html_files/bootstrap-theme.min.css" type="text/css">'
              .'<script src="//plongee.0x972.info/divebook.html_files/bootstrap.min.js"></script>'
              .'<link rel="stylesheet" href="//plongee.0x972.info/divebook.html_files/ol.css" type="text/css">'
              .'<link rel="stylesheet" href="//plongee.0x972.info//divebook.html_files/ol3-layerswitcher.css" />'."\n"
              .'<script src="//plongee.0x972.info/divebook.html_files/ol3-layerswitcher.js"></script>'
              ."<script src='themes/martinique/map.js'></script>\n";
        
  $HTML_elmts .='<!--Icons made by <a href="http://www.flaticon.com/authors/simpleicon" title="SimpleIcon">SimpleIcon</a> and <a href="http://www.freepik.com" title="Freepik">Freepik</a> and <a href="http://www.flaticon.com/authors/oleksandr-yershov" title="Oleksandr Yershov">Oleksandr Yershov</a> from <a href="http://www.flaticon.com" title="Flaticon">www.flaticon.com</a> is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank">CC 3.0 BY</a>-->';

  return $HTML_elmts;
}

function afficher_tags() {
  $tag_type_div = "";
  $tag_where_div = "";
  foreach (list_all_tags('articles', FALSE) as $tag => $nb) {
    if ($tag[0] != "#" && $tag[0] != "@") continue;
    $tagname = substr($tag, 1);
    $tagname_ = str_replace(" ", "_", $tagname);
    
    $htag = "<img width='25px' class='cat tag_selector cat_$tagname_' id='sel_$tagname_' alt='$tagname_' title='$tagname ($nb articles)' src='/themes/martinique/picto/$tagname_.png'/>";
    
    if ($tag[0] == "#")  $tag_type_div .= $htag;
    else $tag_where_div .= $htag;
  }
  
  return "<p>$tag_type_div</p>"; # ."<p>$tag_where_div</p>";
}

function afficher_wall($tableau) {
    $blog_sohann = false;
    if (strpos($_SERVER["SERVER_NAME"], "sohann.pouget.me") !== false) {
        $blog_sohann = true;
    }
    $blog_martinique = false;
    if (strpos($_SERVER["SERVER_NAME"], "martinique.0x972.info") !== false) {
      $blog_martinique = true;
    }
    
    $HTML = '';
	if (!($theme_page = file_get_contents($GLOBALS['theme_liste']))) die($GLOBALS['lang']['err_theme_introuvable']);

    $HTML_elmts = '';
    $HTML_elmts .= "<style type='text/css'>"."\n"
                .".midle {margin-left: 0px;}\n"
                ."#main, #contenu {padding: 0px;max-width: 100%;}\n"
                ."</style>"."\n";
    if ($blog_martinique) {
      $HTML_elmts.= '<article class="wall-post hentry head_map">'."\n"
                 . afficher_tags()
                 . '</article>'."\n";
      
      $HTML_elmts.= '<article class="wall-post hentry head_map">'."\n"
                 . afficher_map_data($tableau)
                 . '</article>'."\n";
    }
    
    $data = array();
    if (!empty($tableau)) {
        $HTML_article = conversions_theme($theme_page, $data, 'post');
        
        foreach ($tableau as $element) {
            if (empty($element['bt_notes'])) {
                //continue;
            } else if ($element['bt_notes'] == "skip") {
                continue;
            }

            $notes = explode('#', $element['bt_notes'])[0];
            
            if (endsWith($notes, ".jpg") && ! endsWith($notes, "-med.jpg")) {
                $notes = substr($notes, 0, strlen($notes) - 4)."-med.jpg";
            }
            
            $HTML_elmts .= '<article class="wall-post hentry " id="'.$element['bt_id'].'">'."\n"
                        . '<a href="'.$element['bt_link'].'" class="entry-link">'."\n"
                        . ($blog_martinique ? ' <img class="entry-thumbnail" src="'.$notes.'">'."\n" : '  <div class="entry-thumbnail" style="background-image: url('.$notes.')"></div>'."\n")
                        . '</a>'
                        . '  <header class="entry-header">'."\n"
                        . '    <div class="entry-meta">'."\n";
            if ($blog_martinique) {
              $HTML_elmts .= '    <span class="posted-on">';
              foreach (explode(", ", $element['bt_categories']) as $id => $tag) {
                if ($tag[0] !== '#') continue;
                $tag = substr($tag, 1);
                $tagname = str_replace(" ", "_", $tag);
                $HTML_elmts .=  "<img class='cat cat_$tagname' width='25px' title='$tag' src='/themes/martinique/picto/$tagname.png' alt='$tag'/>";
              }
              
              $HTML_elmts .= '</span>'."\n";
            } else {
              $HTML_elmts .= '      <span class="posted-on"><a href="'.$element['bt_link'].'" rel="bookmark">'.date_formate($element['bt_date'], '2').($blog_sohann ? " (".sohann_age($element).")" : "").'</a></span>'."\n";
            }
            $HTML_elmts .= '    </div>'."\n"
                        
                        . '    <!-- .entry-meta -->'."\n"
                        . '    <h1 class="entry-title"><a href="'.$element['bt_link'].'" rel="bookmark">'.$element['bt_title'].'</a></h1>'."\n"
                        . '  </header>'."\n"
                        
                        . '  <!-- .entry-header -->'."\n"
                        . '  <a href="'.$element['bt_link'].'" class="entry-link"><span class="screen-reader-text">Lire la suite <span class="meta-nav">→</span></span></a>'."\n"
                        . '</article>'."\n";
            
        }

        $HTML_elmts .= "<script>"."\n"
                    . " var sheet = window.document.styleSheets[0];"."\n"
                    . "sheet.insertRule('#sidebar { display: none; }', sheet.cssRules.length);"."\n"
                    . "sheet.insertRule('#main { max-width: 100%; }', sheet.cssRules.length);"."\n"
                    . "sheet.insertRule('#midle { margin-left: 0px; }', sheet.cssRules.length);"."\n"
                    . "sheet.insertRule('body { overflow-x: hidden; }', sheet.cssRules.length);"."\n"
                    ."</script>"."\n";
        
        $HTML_elmts .= afficher_map_imports();
        
        $HTML_elmts = "<div class='wall-main'> $HTML_elmts </div>";
        
        $HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $HTML_elmts, $HTML_article);
    }

    else {
        $HTML_article = conversions_theme($theme_page, $data, 'list');
        $HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $GLOBALS['lang']['note_no_article'], $HTML_article);
    }
    echo $HTML;
}

afficher_wall($tableau);

?>
