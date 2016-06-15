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

/*****************************************************************************
 some misc routines
******************************************************************************/
// gzip compression
if (extension_loaded('zlib') and ob_get_length() > 0) {
	ob_end_clean();
	ob_start("ob_gzhandler");
}
else {
	ob_start("ob_gzhandler");
}

if (strpos($_SERVER["SERVER_NAME"], "phd.kevin.pouget.me") !== false 
 && strpos($_SERVER["REQUEST_URI"], $_SERVER["SCRIPT_NAME"]) === false
 && strpos($_SERVER["REQUEST_URI"], "?") === false ) 
{
	header("Location: index.php?tag=work");
	die(); 
}

if ((strpos($_SERVER["SERVER_NAME"], "autour.de.grenoble.0x972.info") !== false || strpos($_SERVER["SERVER_NAME"], "polynesie.0x972.info") !== false)
 && strpos($_SERVER["REQUEST_URI"], $_SERVER["SCRIPT_NAME"]) === false
 && strpos($_SERVER["REQUEST_URI"], "?") === false ) 
{
	header("Location: wall.php");
	die(); 
}

if (strpos($_SERVER["SERVER_NAME"], "autour.de.grenoble.0x972.info") !== false
 && strpos($_SERVER["REQUEST_URI"], $_SERVER["SCRIPT_NAME"]) === false
 && strpos($_SERVER["REQUEST_URI"], "?") === false )
{
        header("Location: wall.php");
        die();
}

$CAPOEIRA_WEBSITE = strpos($_SERVER["SERVER_NAME"], "capoeira.0x972.info") !== false;

if ($CAPOEIRA_WEBSITE
 && strpos($_SERVER["REQUEST_URI"], $_SERVER["SCRIPT_NAME"]) === false
 && strpos($_SERVER["REQUEST_URI"], "?") === false )
{
        header("Location: ?alpha");
        die();
}


header('Content-Type: text/html; charset=UTF-8');

$begin = microtime(TRUE);
error_reporting(-1);
$GLOBALS['BT_ROOT_PATH'] = '';

session_start();

require_once 'inc/conf.php';

if (isset($_POST['allowcookie'])) { // si cookies autorisés, conserve les champs remplis
	if (isset($_POST['auteur'])) {  setcookie('auteur_c', $_POST['auteur'], time() + 365*24*3600, null, null, false, true); }
	if (isset($_POST['email'])) {   setcookie('email_c', $_POST['email'], time() + 365*24*3600, null, null, false, true); }
	if (isset($_POST['webpage'])) { setcookie('webpage_c', $_POST['webpage'], time() + 365*24*3600, null, null, false, true); }
	setcookie('subscribe_c', (isset($_POST['subscribe']) and $_POST['subscribe'] == 'on' ) ? 1 : 0, time() + 365*24*3600, null, null, false, true);
	setcookie('cookie_c', 1, time() + 365*24*3600, null, null, false, true);
}

if ( !file_exists($GLOBALS['dossier_config'].'/user.php') or !file_exists($GLOBALS['dossier_config'].'/prefs.php') ) {
	header('Location: '.$GLOBALS['dossier_admin'].'/install.php');
}

require_once $GLOBALS['dossier_config'].'/user.php';
require_once $GLOBALS['dossier_config'].'/prefs.php';
require_once 'inc/lang.php';
require_once 'inc/them.php';
require_once 'inc/fich.php';
require_once 'inc/html.php';
require_once 'inc/form.php';
require_once 'inc/comm.php';
require_once 'inc/conv.php';
require_once 'inc/util.php';
require_once 'inc/veri.php';
require_once 'inc/jasc.php';
require_once 'inc/sqli.php';

$GLOBALS['db_handle'] = open_base($GLOBALS['db_location']);
/*****************************************************************************
 some misc requests
******************************************************************************/

// anti XSS : /index.php/%22onmouseover=prompt(971741)%3E or /index.php/ redirects all on index.php
// if there is a slash after the "index.php", the file is considered as a folder, but the code inside it still executed…
if ($_SERVER['PHP_SELF'] !== $_SERVER['SCRIPT_NAME']) {
	header('Location: '.$_SERVER['SCRIPT_NAME']);
}

// Random article :-)
if (isset($_GET['random'])) {
	try {
		// getting nb articles, gen random num, then select one article is much faster than "sql(order by rand limit 1)"
		$result = $GLOBALS['db_handle']->query("SELECT count(ID) FROM articles WHERE bt_statut=1")->fetch();
		if ($result[0] == 0) {
			header('Location: '.$_SERVER['SCRIPT_NAME']);
		}
		$rand = mt_rand(0, $result[0] - 1);
		$tableau = liste_elements("SELECT * FROM articles WHERE bt_statut=1 LIMIT $rand, 1", array(), 'articles');
	} catch (Exception $e) {
		die('Erreur rand: '.$e->getMessage());
	}

	header('Location: '.$tableau[0]['bt_link']);
	exit;
}

// unsubscribe from comments-newsletter and redirect on main page
if ((isset($_GET['unsub']) and $_GET['unsub'] == 1) and (isset($_GET['comment']) and preg_match('#\d{14}#',($_GET['comment']))) and isset($_GET['mail']) ) {
	if (isset($_GET['all'])) {
		$res = unsubscribe(htmlspecialchars($_GET['comment']), $_GET['mail'], 1);
	} else {
		$res = unsubscribe(htmlspecialchars($_GET['comment']), $_GET['mail'], 0);
	}
	if ($res == TRUE) {
		header('Location: '.basename($_SERVER['PHP_SELF']).'?unsubsribe=yes');
	} else {
		header('Location: '.basename($_SERVER['PHP_SELF']).'?unsubsribe=no');
	}
}


/*****************************************************************************
 Show one post : 1 blogpost (with comments)
******************************************************************************/
// Single Blog Post
if ( isset($_GET['d']) and preg_match('#^\d{4}/\d{2}/\d{2}/\d{2}/\d{2}/\d{2}#', $_GET['d']) ) {
	$tab = explode('/', $_GET['d']);
	$id = substr($tab['0'].$tab['1'].$tab['2'].$tab['3'].$tab['4'].$tab['5'], '0', '14');
	// 'admin' connected is allowed to see draft articles, but not 'public'. Same for article posted with a date in the future.
	if (empty($_SESSION['user_id'])) {
		$query = "SELECT * FROM articles WHERE bt_id=? AND bt_date <=? AND bt_statut=1 LIMIT 1";
		$billets = liste_elements($query, array($id, date('YmdHis')), 'articles');
	} else {
		$query = "SELECT * FROM articles WHERE bt_id=? LIMIT 1";
		$billets = liste_elements($query, array($id), 'articles');
	}
	if ( !empty($billets[0]) ) {
		// TRAITEMENT new commentaire
		$erreurs_form = array();
		if (isset($_POST['_verif_envoi'], $_POST['commentaire'], $_POST['captcha'], $_POST['_token'], $_POST['auteur'], $_POST['email'], $_POST['webpage']) and ($billets[0]['bt_allow_comments'] == '1' )) {
			// COMMENT POST INIT
			$comment = init_post_comment($id, 'public');
			if (isset($_POST['enregistrer'])) {
				$erreurs_form = valider_form_commentaire($comment, 'public');
			}
		} else { unset($_POST['enregistrer']); }

		afficher_form_commentaire($id, 'public', $erreurs_form);
		if (empty($erreurs_form) and isset($_POST['enregistrer'])) {
			traiter_form_commentaire($comment, 'public');
		}

		afficher_index($billets[0], 'post');
	}
	else { afficher_index(NULL, 'list'); }

}

// single link post
elseif ( isset($_GET['id']) and preg_match('#\d{14}#', $_GET['id']) ) {
	$tableau = liste_elements("SELECT * FROM links WHERE bt_id=? AND bt_statut=1", array($_GET['id']), 'links');
	afficher_index($tableau, 'list');
}

// List of all articles ordered by tags
elseif (isset($_GET['tag'])) {
  $query = "SELECT * FROM articles WHERE bt_categories LIKE ? OR bt_categories LIKE ? OR bt_categories LIKE ? OR bt_categories LIKE ? ORDER BY bt_date DESC";

  $multi_tableau = array();
  foreach(list_all_tags("articles", "1") as $tag => $count) {
    $search = $tag;

    $tableau = liste_elements($query, array($search, $search.',%', '%, '.$search, '%, '.$search.', %'), 'articles');

    $multi_tableau[$tag] = $tableau;
  }
  afficher_liste($multi_tableau, $alpha=0, $multi_tab=1);
}
// List of all articles
elseif (isset($_GET['liste']) or isset($_GET['alpha'])) {
    $array = array();
           
  	// paramètre de tag "tag"
	if (isset($_GET['tag'])) {
      $sql_tag = "AND ( bt_categories LIKE ? OR bt_categories LIKE ? OR bt_categories LIKE ? OR bt_categories LIKE ? ) ";
      $array[] = $_GET['tag'];
      $array[] = $_GET['tag'].', %';
      $array[] = '%, '.$_GET['tag'].', %';
      $array[] = '%, '.$_GET['tag'];
      
	} else {
      
      $sql_tag = "";
    }
    $what = "bt_date,bt_id,bt_title,bt_type,bt_content,bt_categories,bt_abstract,bt_notes,bt_nb_comments,bt_link";
    
	$query = "SELECT $what FROM articles WHERE bt_date <= ".date('YmdHis')." AND bt_statut=1 $sql_tag ORDER BY bt_date DESC";
    
	$tableau = liste_elements($query, $array, 'articles');
	afficher_liste($tableau, isset($_GET['alpha']));
    
}

/*****************************************************************************
 show by lists of more than one post
******************************************************************************/
else {
	$annee = date('Y'); $mois = date('m'); $jour = '';
	$array = array();
	$query = "SELECT * FROM ";

	// paramètre mode : quelle table "mode" ?
	if (isset($_GET['mode'])) {
		switch($_GET['mode']) {
			case 'blog':
				$where = 'articles';
				break;
			case 'comments':
				$where = 'commentaires';
				break;
			case 'links':
				$where = 'links';
				break;
			default:
				$where = 'articles';
				break;
		}
	} else {
		$where = 'articles';
	}
	$query .= $where.' ';

	// paramètre de recherche uniquement dans les articles publiés :
	$query .= 'WHERE bt_statut=1 ';


	// paramètre de date "d"
	if (isset($_GET['d']) and preg_match('#^\d{4}(/\d{2})?(/\d{2})?#', $_GET['d'])) {
		$date = '';
		$dates = array();
		$tab = explode('/', $_GET['d']);
		if ( isset($tab['0']) and preg_match('#\d{4}#', ($tab['0'])) ) { $date .= $tab['0']; $annee = $tab['0']; }
		if ( isset($tab['1']) and preg_match('#\d{2}#', ($tab['1'])) ) { $date .= $tab['1']; $mois = $tab['1']; }
		if ( isset($tab['2']) and preg_match('#\d{2}#', ($tab['2'])) ) { $date .= $tab['2']; $jour = $tab['2']; }

		if (!empty($date)) {
			switch ($where) {
				case 'articles':
					$sql_date = "bt_date LIKE ? ";
					break;
				default:
					$sql_date = "bt_id LIKE ? ";
					break;
			}
			$array[] = $date.'%';
		} else {
			$sql_date = "";
		}
	}

	// paramètre de recherche "q"
	if (isset($_GET['q'])) {
		$arr = parse_search($_GET['q']);
		$array = array_merge($array, $arr);
		switch ($where) {
			case 'articles' :
				$sql_q = implode(array_fill(0, count($arr), '( bt_content || bt_title ) LIKE ? '), 'AND ');
				break;
			case 'links' :
				$sql_q = implode(array_fill(0, count($arr), '( bt_content || bt_title || bt_link ) LIKE ? '), 'AND ');
				break;
			case 'commentaires' :
				$sql_q = implode(array_fill(0, count($arr), 'bt_content LIKE ? '), 'AND ');
				break;
			default:
				$sql_q = "";
				break;
		}
	}

	// paramètre de tag "tag"
	if (isset($_GET['tag'])) {
		switch ($where) {
			case 'articles' :
				$sql_tag = "( bt_categories LIKE ? OR bt_categories LIKE ? OR bt_categories LIKE ? OR bt_categories LIKE ? ) ";
				$array[] = $_GET['tag'];
				$array[] = $_GET['tag'].', %';
				$array[] = '%, '.$_GET['tag'].', %';
				$array[] = '%, '.$_GET['tag'];
				break;
			case 'links' :
				$sql_tag = "( bt_tags LIKE ? OR bt_tags LIKE ? OR bt_tags LIKE ? OR bt_tags LIKE ? )";
				$array[] = $_GET['tag'];
				$array[] = $_GET['tag'].', %';
				$array[] = '%, '.$_GET['tag'].', %';
				$array[] = '%, '.$_GET['tag'];
				break;
			default:
				$sql_tag = "";
				break;
		}
	}

	// paramètre d’auteur "author" FIXME !

	// paramètre ORDER BY (pas un paramètre, mais ajouté à la $query quand même)
	switch ($where) {
		case 'articles' :
			$sql_order = "ORDER BY bt_date ";
			break;
		default:
			$sql_order = "ORDER BY bt_id ";
			break;
	}

        // ordre chronologique ?
	if ($GLOBALS['old_first'] || isset($_GET['old_first']) && $_GET['old_first'] !== 'n' ) {
          $sql_order .= "ASC ";
        } else {
          $sql_order .= "DESC ";
        }

	// paramètre de filtrage admin/public (pas un paramètre, mais ajouté quand même)
	switch ($where) {
		case 'articles' :
			$sql_a_p = "bt_date <= ".date('YmdHis')." AND bt_statut=1 ";
			break;
		default:
			$sql_a_p = "bt_id <= ".date('YmdHis')." AND bt_statut=1 ";
			break;
	}

	// paramètre de page "p"
	if (isset($_GET['p']) and is_numeric($_GET['p']) and $_GET['p'] >= 1) {
		$sql_p = 'LIMIT '.$GLOBALS['max_bill_acceuil'] * $_GET['p'].', '.$GLOBALS['max_bill_acceuil'];
	} elseif (!isset($_GET['d']) ) {
		$sql_p = 'LIMIT '.$GLOBALS['max_bill_acceuil'];
	} else {
		$sql_p = '';
	}
        
	// Concaténation de tout ça.
	$glue = 'AND ';
	if (!empty($sql_date)) {
		$query .= $glue.$sql_date;
	}
	if (!empty($sql_q)) {
		$query .= $glue.$sql_q;
	}
	if (!empty($sql_tag)) {
		$query .= $glue.$sql_tag;
	}

	$query .= $glue.$sql_a_p.$sql_order.$sql_p;

	$tableau = liste_elements($query, $array, $where);
	$GLOBALS['param_pagination'] = array('nb' => count($tableau), 'nb_par_page' => $GLOBALS['max_bill_acceuil']);

        if (isset($_GET['q'])) {
          $want_alpha = $CAPOEIRA_WEBSITE;
          afficher_liste($tableau, $want_alpha);
        } else {
          afficher_index($tableau, 'list');
        }
}

 $end = microtime(TRUE);
 echo ' <!-- Rendered in '.round(($end - $begin),6).' seconds -->';

?>
