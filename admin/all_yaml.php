<?php
# *** LICENSE ***
# This file is part of BlogoText.
# http://lehollandaisvolant.net/blogotext/
#
# 2006      Frederic Nassar.
# 2010-2015 Timo Van Neerden <timo@neerden.eu>
#
# BlogoText is free software.
# You can redistribute it under the terms of the MIT / X11 Licence.
#
# *** LICENSE ***

$begin = microtime(TRUE);

$GLOBALS['BT_ROOT_PATH'] = '../';

require_once '../inc/inc.php';

error_reporting($GLOBALS['show_errors']);

operate_session();

$GLOBALS['db_handle'] = open_base($GLOBALS['db_location']);

if (isset($_POST['_verif_envoi'])) {
	echo "Sent to the wrong page ...";
}

$blog_sohann = false;
if (strpos($_SERVER["SERVER_NAME"], "sohann.pouget.me") !== false) {
  $blog_sohann = true;
}

$tableau = array();
if (!empty($_GET['q'])) {
	$arr = parse_search($_GET['q']);
	$sql_where = implode(array_fill(0, count($arr), '( bt_content || bt_title ) LIKE ? '), 'AND '); // AND operator between words
	$query = "SELECT * FROM articles WHERE ".$sql_where."ORDER BY bt_date DESC";
	$tableau = liste_elements($query, $arr, 'articles');
}

elseif ( !empty($_GET['filtre']) ) {
	// for "tags" the requests is "tag.$search" : here we split the type of search and what we search.
	$type = substr($_GET['filtre'], 0, -strlen(strstr($_GET['filtre'], '.')));
	$search = htmlspecialchars(ltrim(strstr($_GET['filtre'], '.'), '.'));

	if ( preg_match('#^\d{6}(\d{1,8})?$#', $_GET['filtre']) ) {
		$query = "SELECT * FROM articles WHERE bt_date LIKE ? ORDER BY bt_date DESC";
		$tableau = liste_elements($query, array($_GET['filtre'].'%'), 'articles');
	}
	elseif ($_GET['filtre'] == 'draft' or $_GET['filtre'] == 'pub') {
		$query = "SELECT * FROM articles WHERE bt_statut=? ORDER BY bt_date DESC";
		$tableau = liste_elements($query, array((($_GET['filtre'] == 'draft') ? 0 : 1)), 'articles');
	}
	elseif ($type == 'tag' and $search != '') {
		$query = "SELECT * FROM articles WHERE bt_categories LIKE ? OR bt_categories LIKE ? OR bt_categories LIKE ? OR bt_categories LIKE ? ORDER BY bt_date DESC";

		$tableau = liste_elements($query, array($search, $search.',%', '%, '.$search, '%, '.$search.', %'), 'articles');
	}
	else {
		$query = "SELECT * FROM articles ORDER BY bt_date DESC";
		$tableau = liste_elements($query, array(), 'articles');
	}
}
else {
		$query = "SELECT * FROM articles ORDER BY bt_date DESC";
		$tableau = liste_elements($query, array(), 'articles');
}


function afficher_yaml_article($article, $cpt) {
  echo "cpt:    ".$cpt."\n";
  echo "uid:    ".$article["bt_id"]."\n";
  echo "date:  ".$article["bt_date"]."\n";
  echo "title: \"".str_replace('"', "\"", $article["bt_title"])."\"\n";
  echo "notes: ".$article["bt_notes"]."\n";
  echo "link:  ".$article["bt_link"]."\n";
  echo "tags:  ".$article["bt_categories"]."\n";
  global $blog_sohann;
  if ($blog_sohann) {
    echo "age:  ".sohann_age($article)."\n";
  }
  $abstract = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $article["bt_abstract"]);
  $abstract = trim($abstract);
  $abstract = str_replace("…", "", $abstract);
  echo "abstract: |\n";
  echo "    ".str_replace("\n", "\n    ", $abstract)."\n\n";
  echo "content: |\n";
  echo "    ".str_replace("\n", "\n    ", $article["bt_wiki_content"])."\n\n";
  
}

function afficher_yaml_articles($tableau) {
	if (!empty($tableau)) {
		$i = 1;
		mb_internal_encoding('UTF-8');
		foreach ($tableau as $article) {
                        afficher_yaml_article($article, $i);
			echo "---\n";
			$i += 1;
		}
	}
}

afficher_yaml_articles($tableau);

?>
