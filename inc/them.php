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

// TEMPLATE VARS
/*
 * Vars used in them files, aimed to get
 * replaced with some specific data
 *
 */
$GLOBALS['boucles'] = array(
	'posts' => 'BOUCLE_posts',
	'commentaires' => 'BOUCLE_commentaires',
);

$GLOBALS['balises'] = array(
	'version' => '{version}',
	'app_name' => '{app_name}',
	'style' => '{style}',
	'racine_du_site' => '{racine_du_site}',
	'rss' => '{rss}',
	'rss_comments' => '{rss_comments}',

	//perso
	'list_polynesie' => '{list_polynesie}',
	'sohann_age' => '{sohann_age}',

	// Navigation
	'pagination' => '{pagination}',
	// Blog
	'blog_nom' => '{blog_nom}',
	'blog_description' => '{blog_description}',
	'blog_auteur' => '{blog_auteur}',
	'blog_email' => '{blog_email}',
	'blog_motscles' => '{keywords}',
	// Formulaires
	'form_recherche' => '{recherche}',
	'form_calendrier' => '{calendrier}',
	'form_commentaire' => '{formulaire_commentaire}',
	// Encarts
	'comm_encart' => '{commentaires_encart}',
	'cat_encart' => '{categories_encart}',
        'last_post_encart' => '{last_post_encart}',

	// Article
	'article_titre' => '{article_titre}',
	'article_titre_page' => '{article_titre_page}',
	'article_titre_echape' => '{article_titre_echape}',
	'article_chapo' => '{article_chapo}',
        'article_notes' => '{article_notes}',
	'article_contenu' => '{article_contenu}',
	'article_heure' => '{article_heure}',
	'article_date' => '{article_date}',
	'article_date_iso' => '{article_date_iso}',
	'article_lien' => '{article_lien}',
	'article_tags' => '{article_tags}',
	'article_tags_plain' => '{article_tags_plain}',
	'nb_commentaires' => '{nombre_commentaires}',

	// Commentaire
	'commentaire_auteur' => '{commentaire_auteur}',
	'commentaire_auteur_lien' => '{commentaire_auteur_lien}',
	'commentaire_contenu' => '{commentaire_contenu}',
	'commentaire_heure' => '{commentaire_heure}',
	'commentaire_date' => '{commentaire_date}',
	'commentaire_date_iso' => '{commentaire_date_iso}',
	'commentaire_email' => '{commentaire_email}',
	'commentaire_webpage' => '{commentaire_webpage}',
	'commentaire_anchor' => '{commentaire_ancre}', // the id="" content
	'commentaire_lien' => '{commentaire_lien}',
	'commentaire_gravatar' => '{commentaire_gravatar_link}', // http://2.gravatar.com/avatar/md5($email)

	// Liens
	'lien_auteur' => '{lien_auteur}',
	'lien_titre' => '{lien_titre}',
	'lien_url' => '{lien_url}',
	'lien_date' => '{lien_date}',
	'lien_date_iso' => '{lien_date_iso}',
	'lien_heure' => '{lien_heure}',
	'lien_description' => '{lien_description}',
	'lien_permalink' => '{lien_permalink}',
	'lien_id' => '{lien_id}',
	'lien_tags' => '{lien_tags}',
);



function conversions_theme($texte, $solo_art, $cnt_mode) {
	$texte = str_replace($GLOBALS['balises']['version'], $GLOBALS['version'], $texte);
	$texte = str_replace($GLOBALS['balises']['app_name'], $GLOBALS['nom_application'], $texte);
	$texte = str_replace($GLOBALS['balises']['style'], $GLOBALS['theme_style'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_description'], $GLOBALS['description'], $texte);
	$texte = str_replace($GLOBALS['balises']['racine_du_site'], $GLOBALS['racine'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_auteur'], $GLOBALS['auteur'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_email'], $GLOBALS['email'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_nom'], $GLOBALS['nom_du_site'], $texte);

	if ($cnt_mode == 'post' and !empty($solo_art)) {
		$hidden = $solo_art['bt_statut'] == 0 ? " (privé)" : "";
		$texte = str_replace($GLOBALS['balises']['article_titre_page'], $solo_art['bt_title'].$hidden.' - ', $texte);
		$texte = str_replace($GLOBALS['balises']['article_titre'], $solo_art['bt_title'].$hidden, $texte);
		$texte = str_replace($GLOBALS['balises']['article_titre_echape'], urlencode($solo_art['bt_title']), $texte);
		$texte = str_replace($GLOBALS['balises']['article_lien'], $solo_art['bt_link'], $texte);
		if ($solo_art['bt_type'] == 'article') {
			$texte = str_replace($GLOBALS['balises']['article_chapo'], htmlspecialchars($solo_art['bt_abstract'], ENT_QUOTES), $texte);
			$texte = str_replace($GLOBALS['balises']['blog_motscles'], $solo_art['bt_keywords'], $texte);
                        $texte = str_replace($GLOBALS['balises']['article_notes'], $solo_art['bt_notes'], $texte);
		}
		if ($solo_art['bt_type'] == 'link' or $solo_art['bt_type'] == 'note') {
			$texte = str_replace($GLOBALS['balises']['article_titre_page'], $solo_art['bt_title'].' - ', $texte);
		}
	}

	// si remplacé, ceci ne sert à rien. Si pas remplacé, ça sert.
	$texte = str_replace($GLOBALS['balises']['article_titre_page'], '', $texte);
	$texte = str_replace($GLOBALS['balises']['blog_motscles'], $GLOBALS['keywords'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_titre_echape'], '', $texte);
	$texte = str_replace($GLOBALS['balises']['article_lien'], $GLOBALS['racine'], $texte);

	$texte = str_replace($GLOBALS['balises']['pagination'], lien_pagination(), $texte);

	if (strpos($_SERVER["SERVER_NAME"], "polynesie") !== false) {
		$texte = str_replace($GLOBALS['balises']['list_polynesie'], ""/*list_tagged_articles("polynesie", true)*/, $texte);
	} else {
		$texte = str_replace($GLOBALS['balises']['list_polynesie'], "", $texte);
	}

	if (strpos($texte, $GLOBALS['balises']['form_recherche']) !== FALSE) {
		$texte = str_replace($GLOBALS['balises']['form_recherche'], moteur_recherche(''), $texte) ;
	}
	if (strpos($texte, $GLOBALS['balises']['form_calendrier']) !== FALSE) {
		$texte = str_replace($GLOBALS['balises']['form_calendrier'], afficher_calendrier(), $texte) ;
	}

	// Formulaires
	$texte = str_replace($GLOBALS['balises']['rss'], $GLOBALS['rss'], $texte);
        if (strpos($texte, $GLOBALS['balises']['last_post_encart']) !== FALSE) {
          $texte = str_replace($GLOBALS['balises']['last_post_encart'], encart_last_posts(), $texte);
        }
	$texte = str_replace($GLOBALS['balises']['comm_encart'], encart_commentaires(), $texte);
	$texte = str_replace($GLOBALS['balises']['cat_encart'], encart_categories((isset($_GET['mode']))?$_GET['mode']:''), $texte);
	if (isset($GLOBALS['rss_comments'])) { $texte = str_replace($GLOBALS['balises']['rss_comments'], $GLOBALS['rss_comments'], $texte);}

	return $texte;
}


// Commentaire
function conversions_theme_commentaire($texte, $commentaire) {
	$texte = str_replace($GLOBALS['balises']['commentaire_contenu'], $commentaire['bt_content'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_date'], date_formate($commentaire['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_date_iso'], date_formate_iso($commentaire['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_heure'], heure_formate($commentaire['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_email'], $commentaire['bt_email'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_gravatar'], $GLOBALS['gravatar_link'].md5($commentaire['bt_email']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_auteur_lien'], $commentaire['auteur_lien'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_auteur'], str_replace("'", "\\'", $commentaire['bt_author']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_webpage'], $commentaire['bt_webpage'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_anchor'], $commentaire['anchor'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_lien'], $commentaire['bt_link'], $texte);
	return $texte;
}

// Article
function conversions_theme_article($texte, $billet) {
	// Formulaire commentaires
	if (isset($GLOBALS['form_commentaire'])) { $texte = str_replace($GLOBALS['balises']['form_commentaire'], $GLOBALS['form_commentaire'], $texte); }
		else { $texte = str_replace($GLOBALS['balises']['form_commentaire'], '', $texte); }

	// FIXME : remplacer uniquement dans le texte, pas les liens.
	//	$billet['bt_content'] = str_replace($q, '<mark>'.$q.'</mark>', $billet['bt_content']);

	$texte = str_replace($GLOBALS['balises']['blog_auteur'], $GLOBALS['auteur'], $texte);
	$texte = str_replace($GLOBALS['balises']['style'], $GLOBALS['theme_style'], $texte);
	$texte = str_replace($GLOBALS['balises']['rss_comments'], 'rss.php?id='.$billet['bt_id'], $texte);
	$hidden = $billet['bt_statut'] == 0 ? " (article privé)" : "";
	$texte = str_replace($GLOBALS['balises']['article_titre'], $billet['bt_title'].$hidden, $texte);
	$texte = str_replace($GLOBALS['balises']['article_chapo'], $billet['bt_abstract'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_contenu'], $billet['bt_content'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_date'], date_formate($billet['bt_date']), $texte);
	$texte = str_replace($GLOBALS['balises']['article_date_iso'], date_formate_iso($billet['bt_date']), $texte);
	$texte = str_replace($GLOBALS['balises']['article_heure'], heure_formate($billet['bt_date']), $texte);
	// comments closed (globally or only for this article) and no comments => say « comments closed »
	if ( ($billet['bt_allow_comments'] == 0 or $GLOBALS['global_com_rule'] == 1 ) and $billet['bt_nb_comments'] == 0 ) { $texte = str_replace($GLOBALS['balises']['nb_commentaires'], $GLOBALS['lang']['note_comment_closed'], $texte); }
	// comments open OR ( comments closed AND comments exists ) => say « nb comments ».
	if ( !($billet['bt_allow_comments'] == 0 or $GLOBALS['global_com_rule'] == 1 ) or $billet['bt_nb_comments'] != 0 ) { $texte = str_replace($GLOBALS['balises']['nb_commentaires'], nombre_objets($billet['bt_nb_comments'], 'commentaire'), $texte); }
	$texte = str_replace($GLOBALS['balises']['article_lien'], $billet['bt_link'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_tags'], liste_tags($billet, '1'), $texte);
	$texte = str_replace($GLOBALS['balises']['article_tags_plain'], liste_tags($billet, '0'), $texte);
	$texte = str_replace($GLOBALS['balises']['sohann_age'], sohann_age($billet), $texte);
	return $texte;
}

// Liens
function conversions_theme_lien($texte, $lien) {
	$texte = str_replace($GLOBALS['balises']['article_titre'], $lien['bt_title'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_auteur'], $lien['bt_author'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_titre'], $lien['bt_title'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_url'], $lien['bt_link'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_date'], date_formate($lien['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['lien_date_iso'], date_formate_iso($lien['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['lien_heure'], heure_formate($lien['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['lien_permalink'], $lien['bt_id'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_description'], $lien['bt_content'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_id'], $lien['ID'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_tags'], liste_tags($lien, '1'), $texte);
	return $texte;
}

function list_tagged_articles($tag, $first_only) {
  if ($first_only && strpos($_SERVER["REQUEST_URI"], "?") !== false) {
    return "";
  }
  
  $query = "SELECT bt_date,bt_id,bt_title,bt_nb_comments,bt_link FROM articles "
    . "WHERE bt_date <= ".date('YmdHis')." AND bt_statut=1 "
    . " AND `bt_categories` LIKE '%$tag%'"
    . "ORDER BY bt_date ASC";
  $tableau = liste_elements($query, array(), 'articles');
  return get_article_list($tableau);
}

// récupère le bout du fichier thème contenant une boucle comme {BOUCLE_commentaires}
//  soit le morceau de HTML retourné est parsé à son tour pour crée le HTML de chaque commentaire ou chaque article.
//  soit le morceau de HTML retourné sert à se faire remplacer par l’ensemble des commentaires constitués
function extract_boucles($texte, $balise, $incl) {
	$len_balise_d = 0 ; $len_balise_f = 0;
	if ($incl == 'excl') { // la $balise est exclue : bli{p}blabla{/p}blo => blabla
		$len_balise_d = strlen('{'.$balise.'}');
	}
	else {// la $balise est inclue : bli{p}blabla{/p}blo => {p}blabla{/p}
		$len_balise_f = strlen('{/'.$balise.'}');
	}

	$debut = strpos($texte, '{'.$balise.'}');
	$fin = strpos($texte, '{/'.$balise.'}');

	if ($debut !== FALSE and $fin !== FALSE) {
		$debut += $len_balise_d;
		$fin += $len_balise_f;

		$length = $fin - $debut;
		$return = substr($texte, $debut, $length);
		return $return;
	} else { // $balises n’est pas dans le texte : retourne le texte sans changements.
		return $texte;
	}
}

// only used by the main page of the blog (not on admin) : shows main blog page.
function afficher_index($tableau, $type) {
	$HTML = '';
	if (!($theme_page = file_get_contents($GLOBALS['theme_liste']))) die($GLOBALS['lang']['err_theme_introuvable']);
	if (!($theme_post = file_get_contents($GLOBALS['theme_post_post']))) die($GLOBALS['lang']['err_theme_introuvable']);

	if ($type == 'list') {
		$HTML_elmts = '';
		$data = array();
		if (!empty($tableau)) {
			if (count($tableau)==1 and !empty($tableau[0]['bt_title']) and $tableau[0]['bt_type'] == 'article') {
				redirection($tableau[0]['bt_link']);
				exit;
			} else {
				if (count($tableau)==1 and ($tableau[0]['bt_type'] == 'link' or $tableau[0]['bt_type'] == 'note') ) $data = $tableau[0]; 
			$HTML_article = conversions_theme($theme_page, $data, 'post');
			if ($tableau[0]['bt_type'] == 'article') {
				if (!($theme_article = file_get_contents($GLOBALS['theme_post_artc']))) die($GLOBALS['lang']['err_theme_introuvable']);
				$conversion_theme_fonction = 'conversions_theme_article';
			}
			if ($tableau[0]['bt_type'] == 'comment') {
				if (!($theme_article = file_get_contents($GLOBALS['theme_post_comm']))) die($GLOBALS['lang']['err_theme_introuvable']);
				$conversion_theme_fonction = 'conversions_theme_commentaire';
			}
			if ($tableau[0]['bt_type'] == 'link' or $tableau[0]['bt_type'] == 'note') {
				if (!($theme_article = file_get_contents($GLOBALS['theme_post_link']))) die($GLOBALS['lang']['err_theme_introuvable']);
				$conversion_theme_fonction = 'conversions_theme_lien';
			}
			foreach ($tableau as $element) {
				$HTML_elmts .=  $conversion_theme_fonction($theme_article, $element);
			}
			$HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $HTML_elmts, $HTML_article);
			}
		}
		else {
			$HTML_article = conversions_theme($theme_page, $data, 'list');
			$HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $GLOBALS['lang']['note_no_article'], $HTML_article);
		}
	}

	elseif ($type == 'post') {
		$billet = $tableau;

		$HTML_comms = '';
		// get list comments
		if ($billet['bt_nb_comments'] != 0) {
			$query = "SELECT * FROM commentaires WHERE bt_article_id=? AND bt_statut=1 ORDER BY bt_id LIMIT ? ";
			$commentaires = liste_elements($query, array($billet['bt_id'], $billet['bt_nb_comments']), 'commentaires');

			// parse & apply template comments
			$template_comments = extract_boucles($theme_post, $GLOBALS['boucles']['commentaires'], 'excl');

			foreach ($commentaires as $element) {
				$HTML_comms .=  conversions_theme_commentaire($template_comments, $element);
			}
		}

		// replace {boucle commentaires} in $theme_post
		$v = extract_boucles($theme_post, $GLOBALS['boucles']['commentaires'], 'incl');
		$HTML_article = conversions_theme_article($theme_post, $billet);
		$HTML = str_replace($v, $HTML_comms, $HTML_article);

		// replace article+comment data in page
		$HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $HTML, $theme_page);

		// replace tags.
		$HTML = conversions_theme($HTML, $billet, 'post');
	}
	echo $HTML;
}

// Affiche la liste des articles, avec le &liste dans l’url
function get_article_list($tableau, $alpha=0, $multi_tab=0) {
	$HTML_elmts = '';


	if (!($theme_page = file_get_contents($GLOBALS['theme_liste']))) die($GLOBALS['lang']['err_theme_introuvable']);
           
	$HTML_article = conversions_theme($theme_page, array(), 'list');

    $blog_martinique = false;
    if (strpos($_SERVER["SERVER_NAME"], "martinique.0x972.info") !== false) {
      $blog_martinique = true;
    }
    
	if (!empty($tableau)) {
		$HTML_elmts .= '<ul id="liste-all-articles">'."\n";

        if ($alpha) {
          function sortByOrder($a, $b) {
            return $a['bt_title'][0] > $b['bt_title'][0];
          }
        
          usort($tableau, "sortByOrder");
        } elseif ($multi_tab) {
          $multi_tableau = $tableau;
        }
        
tableau_printing:
        if ($multi_tab) {
          $tableau = reset($multi_tableau);
          $tag = key($multi_tableau);
          
          unset($multi_tableau[$tag]); 
        }

        $img = "";
        $prev_letter = "";
		foreach ($tableau as $e) {
            if ($alpha) {
              $first_letter = $e['bt_title'][0];
              if ($first_letter != $prev_letter) {
                $HTML_elmts .= "\t".'<li class="list-letter">'.$first_letter.'</li>'."\n";
                $prev_letter = $first_letter;
              }
            } elseif ($multi_tab and empty($prev_letter)) {
              if ($blog_martinique) {
                if ($tag[0] == "#") {
                  $tag = substr($tag, 1);
                  $tagname = str_replace(" ", "_", $tag);
                  $img = "<img class='cat cat_$tagname' width='25px' height='25px' title='$tag' src='/themes/martinique/picto/$tagname.png' alt='$tag'/>&nbsp;";
                  $tag = ucfirst($tag);
                } elseif ($tag[0] == "@") {
                  $tag = substr($tag, 1);
                  $tag = "@".str_replace(" ", "-", ucwords(str_replace("-", " ", $tag)));
                } else {
                  $img = "";
                }
              }
              $HTML_elmts .= "\t".'<li class="list-letter">'.$img.$tag.'</li>'."\n";
              $prev_letter = "x"; // just set it
            }
			$short_date = substr($e['bt_date'], 0, 4).'/'.substr($e['bt_date'], 4, 2).'/'.substr($e['bt_date'], 6, 2);
            $title = liste_tags($e)." | ".htmlspecialchars(trim(mb_substr(strip_tags($e['bt_content']), 0, 249)), ENT_QUOTES).'...';
			$HTML_elmts .= "\t".'<li>';
            if (!$alpha and !$multi_tab) {
              $HTML_elmts .= '<time datetime="'.date_formate_iso($e['bt_id']).'">'.$short_date.'</time> ';
            }
	    $hidden = $e['bt_statut'] == 0 ? "&nbsp;(article privé)" : "";
            $HTML_elmts .= '<a href="'.$e['bt_link'].'" title="'.$title.'">'
                        .$e['bt_title']."</a>$hidden</li>"."\n";
		}

        if ($multi_tab and !empty($multi_tableau)) {
          
          goto tableau_printing; // loop until multi_tableau is empty
        }
		$HTML_elmts .= '</ul>'."\n";
		$HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $HTML_elmts, $HTML_article);
	}
	else {
		$HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $GLOBALS['lang']['note_no_article'], $HTML_article);
	}
	return $HTML_elmts;
}

// Affiche la liste des articles, avec le &liste dans l’url
function afficher_liste($tableau, $alpha=0,  $multi_tab=0) {
        $HTML_elmts = '';
        if (!($theme_page = file_get_contents($GLOBALS['theme_liste']))) {
          die($GLOBALS['lang']['err_theme_introuvable']);
        }

        $HTML_article = conversions_theme($theme_page, array(), 'list');
        if (!empty($tableau)) {
                $HTML_elmts = get_article_list($tableau, $alpha, $multi_tab);
                $HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $HTML_elmts, $HTML_article);
        }
        else {
                $HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $GLOBALS['lang']['note_no_article'], $HTML_article);
        }

        echo $HTML;
}



function get_liste($tableau) {
	if (!($theme_page = file_get_contents($GLOBALS['theme_liste']))) {
          die($GLOBALS['lang']['err_theme_introuvable']);
        }
        
	return conversions_theme($theme_page, array(), 'list');
}
