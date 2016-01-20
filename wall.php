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
    $ORDER = 'ASC';
    $_GET['tag'] = 'polynesie';
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



$query .= " ORDER BY bt_id $ORDER";

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

function afficher_wall($tableau) {
    $HTML = '';
	if (!($theme_page = file_get_contents($GLOBALS['theme_liste']))) die($GLOBALS['lang']['err_theme_introuvable']);

    $HTML_elmts = '';
    $HTML_elmts .= "<style type='text/css'>"."\n"
                .".midle {"."\n"
                ."margin-left: 0px;"."\n"
                ."}"."\n"
                ."</style>"."\n";
    $data = array();
    if (!empty($tableau)) {
        $HTML_article = conversions_theme($theme_page, $data, 'post');
        
        foreach ($tableau as $element) {
            if (empty($element['bt_notes'])) {
                //continue;
            } else if ($element['bt_notes'] == "skip") {
                continue;
            }

            $notes = $element['bt_notes'];
            if (endsWith($notes, ".jpg") && ! endsWith($notes, "-med.jpg")) {
                $notes = substr($notes, 0, strlen($notes) - 4)."-med.jpg";
            }
            
            $HTML_elmts .= '<article class="wall-post hentry">'."\n"
                        . '  <div class="entry-thumbnail" style="background-image: url('.$notes.')"></div>'."\n"
                        . '  <header class="entry-header">'."\n"
                        . '    <div class="entry-meta">'."\n"
                        . '      <span class="posted-on"><a href="'.$element['bt_link'].'" rel="bookmark">'.date_formate($element['bt_date'], '2').'</a></span>'."\n"
                        . '    </div>'."\n"
                        
                        . '    <!-- .entry-meta -->'."\n"
                        . '    <h1 class="entry-title"><a href="'.$element['bt_link'].'" rel="bookmark">'.$element['bt_title'].'</a></h1>'."\n"
                        . '  </header>'."\n"
                        
                        . '  <!-- .entry-header -->'."\n"
                        . '  <a href="'.$element['bt_link'].'" class="entry-link"><span class="screen-reader-text">Lire la suite <span class="meta-nav">→</span></span></a>'."\n"
                        . '</article>'."\n";
            
            //$HTML_elmts .=  "$notes <br/>";
        }

        $HTML_elmts .= "<script>"."\n"
                    . " var sheet = window.document.styleSheets[0];"."\n"
                    . "sheet.insertRule('#sidebar { display: none; }', sheet.cssRules.length);"."\n"
                    . "sheet.insertRule('#main { max-width: 100%; }', sheet.cssRules.length);"."\n"
                    . "sheet.insertRule('#midle { margin-left: 0px; }', sheet.cssRules.length);"."\n"
                    . "sheet.insertRule('body { overflow-x: hidden; }', sheet.cssRules.length);"."\n"
        ."</script>"."\n";
        
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
