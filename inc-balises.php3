<?php

//
// Ce fichier regroupe la quasi totalite des definitions de #BALISES de spip
// Pour chaque balise, il est possible de surcharger, dans mes_fonctions.php3,
// la fonction balise_TOTO_dist par une fonction balise_TOTO() respectant la
// meme API : 
// elle recoit en entree un objet de classe CHAMP, le modifie et le retourne.
// Cette classe est definie dans inc-compilo-index.php3
//

// Ce fichier ne sera execute qu'une fois
if (defined("_INC_BALISES")) return;
define("_INC_BALISES", "1");


//
// Traitements standard de divers champs
//
function champs_traitements ($p) {
	static $traitements = array (
		'BIO' => 'traiter_raccourcis(%s)',
		'CHAPO' => 'traiter_raccourcis(nettoyer_chapo(%s))',
		'DATE' => 'vider_date(%s)',
		'DATE_MODIF' => 'vider_date(%s)',
		'DATE_NOUVEAUTES' => 'vider_date(%s)',
		'DATE_REDAC' => 'vider_date(%s)',
		'DESCRIPTIF' => 'traiter_raccourcis(%s)',
		'LIEN_TITRE' => 'typo(%s)',
		'LIEN_URL' => 'htmlspecialchars(vider_url(%s))',
		'MESSAGE' => 'traiter_raccourcis(%s)',
		'NOM_SITE_SPIP' => 'typo(%s)',
		'NOM' => 'typo(%s)',
		'PARAMETRES_FORUM' => 'htmlspecialchars(%s)',
		'PS' => 'traiter_raccourcis(%s)',
		'SOUSTITRE' => 'typo(%s)',
		'SURTITRE' => 'typo(%s)',
		'TEXTE' => 'traiter_raccourcis(%s)',
		'TITRE' => 'typo(%s)',
		'TYPE' => 'typo(%s)',
		'URL_ARTICLE' => 'htmlspecialchars(vider_url(%s))',
		'URL_BREVE' => 'htmlspecialchars(vider_url(%s))',
		'URL_DOCUMENT' => 'htmlspecialchars(vider_url(%s))',
		'URL_FORUM' => 'htmlspecialchars(vider_url(%s))',
		'URL_MOT' => 'htmlspecialchars(vider_url(%s))',
		'URL_RUBRIQUE' => 'htmlspecialchars(vider_url(%s))',
		'URL_SITE_SPIP' => 'htmlspecialchars(vider_url(%s))',
		'URL_SITE' => 'htmlspecialchars(vider_url(%s))',
		'URL_SYNDIC' => 'htmlspecialchars(vider_url(%s))',
		'HTTP_VARS' => 'htmlspecialchars(%s)'
	);
	$ps = $traitements[$p->nom_champ];
	if (!$ps) return $p->code;
	if ($p->documents)
	  {$ps = str_replace('traiter_raccourcis(', 
			     'traiter_raccourcis_doublon($doublons,',
			     str_replace('typo(', 
					 'typo_doublon($doublons,',
					 $ps));
	  }
	// on supprime les <IMGnnn> tant qu'on ne rapatrie pas
	// les documents distants joints..
	// il faudrait aussi corriger les raccourcis d'URL locales
	return str_replace('%s',
			   (!$p->boucles[$p->id_boucle]->sql_serveur ?
			    $p->code :
			    ('supprime_img(' . $p->code . ')')),
			   $ps);				
}

// il faudrait savoir traiter les formulaires en local 
// tout en appelant le serveur SQL distant.
// En attendant, cette fonction permet de refuser une authentification 
// sur qqch qui n'a rien � voir.

function balise_distante_interdite($p) {
	$nom = $p->id_boucle;
	if ($p->boucles[$nom]->sql_serveur) {
		erreur_squelette($p->nom_champ ._L(" distant interdit"), $nom);
	}
}

//
// Definition des balises
//
function balise_NOM_SITE_SPIP_dist($p) {
	$p->code = "lire_meta('nom_site')";
	$p->statut = 'php';
	return $p;
}

function balise_EMAIL_WEBMASTER_dist($p) {
	$p->code = "lire_meta('email_webmaster')";
	$p->statut = 'php';
	return $p;
}

function balise_CHARSET_dist($p) {
	$p->code = "lire_meta('charset')";
	$p->statut = 'php';
	return $p;
}

function balise_LANG_LEFT_dist($p) {
	$_lang = champ_sql('lang', $p);
	$p->code = "lang_dir(($_lang ? $_lang : \$GLOBALS['spip_lang']),'left','right')";
	$p->statut = 'php';
	return $p;
}

function balise_LANG_RIGHT_dist($p) {
	$_lang = champ_sql('lang', $p);
	$p->code = "lang_dir(($_lang ? $_lang : \$GLOBALS['spip_lang']),'right','left')";
	$p->statut = 'php';
	return $p;
}

function balise_LANG_DIR_dist($p) {
	$_lang = champ_sql('lang', $p);
	$p->code = "lang_dir(($_lang ? $_lang : \$GLOBALS['spip_lang']),'ltr','rtl')";
	$p->statut = 'php';
	return $p;
}

function balise_PUCE_dist($p) {
	$_lang = champ_sql('lang', $p);
	$p->code = "((lang_dir(($_lang ? $_lang : \$GLOBALS['spip_lang']),false,true) && \$GLOBALS['puce_rtl']) ? \$GLOBALS['puce_rtl'] : \$GLOBALS['puce'])";
	$p->statut = 'php';
	return $p;
}

// #DATE
// Cette fonction sait aller chercher dans le contexte general
// quand #DATE est en dehors des boucles
// http://www.spip.net/fr_article1971.html
function balise_DATE_dist ($p) {
	$_date = champ_sql('date', $p);
	$p->code = "$_date";
	$p->statut = 'php';
	return $p;
}

// #DATE_REDAC
// http://www.spip.net/fr_article1971.html
function balise_DATE_REDAC_dist ($p) {
	$_date = champ_sql('date_redac', $p);
	$p->code = "$_date";
	$p->statut = 'php';
	return $p;
}

// #DATE_MODIF
// http://www.spip.net/fr_article1971.html
function balise_DATE_MODIF_dist ($p) {
	$_date = champ_sql('date_modif', $p);
	$p->code = "$_date";
	$p->statut = 'php';
	return $p;
}

// #DATE_NOUVEAUTES
// http://www.spip.net/fr_article1971.html
function balise_DATE_NOUVEAUTES_dist($p) {
	$p->code = "((lire_meta('quoi_de_neuf') == 'oui' AND lire_meta('majnouv')) ? normaliser_date(lire_meta('majnouv')) : \"'0000-00-00'\")";
	$p->statut = 'php';
	return $p;
}

function balise_URL_SITE_SPIP_dist($p) {
	$p->code = "lire_meta('adresse_site')";
	$p->statut = 'php';
	return $p;
}


function balise_URL_ARTICLE_dist($p) {
	$_type = $p->type_requete;

	// Cas particulier des boucles (SYNDIC_ARTICLES)
	if ($_type == 'syndic_articles') {
		$p->code = champ_sql('url', $p);
	}

	// Cas general : chercher un id_article dans la pile
	else {
		$_id_article = champ_sql('id_article', $p);
		$p->code = "generer_url_article($_id_article)";

		if ($p->boucles[$p->nom_boucle ? $p->nom_boucle : $p->id_boucle]->hash)
			$p->code = "url_var_recherche(" . $p->code . ")";
	}

	$p->statut = 'html';
	return $p;
}

function balise_URL_RUBRIQUE_dist($p) {
	$p->code = "generer_url_rubrique(" . 
	champ_sql('id_rubrique',$p) . 
	")" ;
	if ($p->boucles[$p->nom_boucle ? $p->nom_boucle : $p->id_boucle]->hash)
	$p->code = "url_var_recherche(" . $p->code . ")";

	$p->statut = 'html';
	return $p;
}

function balise_URL_BREVE_dist($p) {
	$p->code = "generer_url_breve(" .
	champ_sql('id_breve',$p) . 
	")";
	if ($p->boucles[$p->nom_boucle ? $p->nom_boucle : $p->id_boucle]->hash)
	$p->code = "url_var_recherche(" . $p->code . ")";

	$p->statut = 'html';
	return $p;
}

function balise_URL_MOT_dist($p) {
	$p->code = "generer_url_mot(" .
	champ_sql('id_mot',$p) .
	")";
	$p->code = "url_var_recherche(" . $p->code . ")";

	$p->statut = 'html';
	return $p;
}

function balise_URL_FORUM_dist($p) {
	$p->code = "generer_url_forum(" .
	champ_sql('id_forum',$p) .")";

	$p->statut = 'html';
	return $p;
}

function balise_URL_DOCUMENT_dist($p) {
	$p->code = "generer_url_document(" .
	champ_sql('id_document',$p) . ")";

	$p->statut = 'html';
	return $p;
}

function balise_URL_AUTEUR_dist($p) {
	$p->code = "generer_url_auteur(" .
	champ_sql('id_auteur',$p) .")";
	if ($p->boucles[$p->nom_boucle ? $p->nom_boucle : $p->id_boucle]->hash)
	$p->code = "url_var_recherche(" . $p->code . ")";

	$p->statut = 'html';
	return $p;
}

function balise_NOTES_dist($p) {
	// Recuperer les notes
	$p->code = 'calculer_notes()';
	$p->statut = 'html';
	return $p;
}

function balise_RECHERCHE_dist($p) {
	$p->code = 'htmlspecialchars($GLOBALS["recherche"])';
	$p->statut = 'php';
	return $p;
}

function balise_COMPTEUR_BOUCLE_dist($p) {
	if ($p->id_mere === '') {
		erreur_squelette(_L("Champ #COMPTEUR_BOUCLE hors boucle"), $p->id_boucle);
		$p->code = "''";
	} else {
		$p->code = '$compteur_boucle';
		$p->statut = 'php';
		return $p;
	}
}

function balise_TOTAL_BOUCLE_dist($p) {
	if ($p->id_mere === '') {
		erreur_squelette(_L("Champ #TOTAL_BOUCLE hors boucle"), $p->id_boucle);
		$p->code = "''";
	} else {
		$p->code = "\$Numrows['$p->id_mere']";
		$p->boucles[$p->id_mere]->numrows = true;
		$p->statut = 'php';
	}
	return $p;
}

function balise_POINTS_dist($p) {
	return rindex_pile($p, 'points', 'recherche');
}

function balise_POPULARITE_ABSOLUE_dist($p) {
	$p->code = 'ceil(' .
	champ_sql('popularite', $p) .
	')';
	$p->statut = 'php';
	return $p;
}

function balise_POPULARITE_SITE_dist($p) {
	$p->code = 'ceil(lire_meta(\'popularite_total\'))';
	$p->statut = 'php';
	return $p;
}

function balise_POPULARITE_MAX_dist($p) {
	$p->code = 'ceil(lire_meta(\'popularite_max\'))';
	$p->statut = 'php';
	return $p;
}

function balise_EXPOSER_dist($p) {
	global  $table_primary;
	$type_boucle = $p->type_requete;
	$primary_key = $table_primary[$type_boucle];
	if (!$primary_key) {
		erreur_squelette(_L("Champ #EXPOSER hors boucle"), $p->id_boucle);
	}
	$on = 'on';
	$off= '';
	if ($p->fonctions) {
		// Gerer la notation [(#EXPOSER|on,off)]
		reset($p->fonctions);
		list(, $onoff) = each($p->fonctions);
		ereg("([^,]*)(,(.*))?", $onoff, $regs);
		$on = addslashes($regs[1]);
		$off = addslashes($regs[3]);
		
		// autres filtres
		$filtres=Array();
		while (list(, $nom) = each($p->fonctions))
		  $filtres[] = $nom;
		$p->fonctions = $filtres;
	}


	$p->code = '(calcul_exposer('
	.champ_sql($primary_key, $p)
	.', "'.$primary_key.'", $Pile[0]) ?'." '$on': '$off')";
	$p->statut = 'php';
	return $p;
}


//
// Inserer directement un document dans le squelette
//
function balise_EMBED_DOCUMENT_dist($p) {
	balise_distante_interdite($p);
	$_id_document = champ_sql('id_document',$p);
	$p->code = "calcule_embed_document(intval($_id_document), '" .
	texte_script($p->fonctions ? join($p->fonctions, "|") : "") .
	  "', \$doublons, '" . $p->documents . "')";
	unset ($p->fonctions);
	$p->statut = 'html';
	return $p;
}

// Debut et fin de surlignage auto des mots de la recherche
// on insere une balise Span avec une classe sans spec:
// c'est transparent s'il n'y a pas de recherche,
// sinon elles seront remplacees par les fontions de inc_surligne
// flag_pcre est juste une flag signalant que preg_match est dispo.

function balise_DEBUT_SURLIGNE_dist($p) {
	global $flag_pcre;
	$p->code = ($flag_pcre ? ('\'<span class="spip_surligneconditionnel">\'') : "''");
	return $p;
}
function balise_FIN_SURLIGNE_dist($p) {
	global $flag_pcre;
	$p->code = ($flag_pcre ? ('\'</span class="spip_surligneconditionnel">\'') : "''");
	return $p;
}

// Formulaire de changement de langue
function balise_MENU_LANG_dist($p) {
	$p->code = '("<"."?php
include_ecrire(\"inc_lang.php3\");
echo menu_langues(\"var_lang\", \$menu_lang);
?".">")';
	$p->statut = 'php';
	return $p;
}

// Formulaire de changement de langue / page de login
function balise_MENU_LANG_ECRIRE_dist($p) {
	$p->code = '("<"."?php
include_ecrire(\"inc_lang.php3\");
echo menu_langues(\"var_lang_ecrire\", \$menu_lang);
?".">")';
	$p->statut = 'php';
	return $p;
}

function balise_URL_LOGOUT_dist($p) {
	if ($p->fonctions) {
	$url = "'" . $p->fonctions[0] . "'";
	$p->fonctions = array();
	} else {
	$url = '\$clean_link->getUrl()';
	}
	$p->code = '("<"."?php if (\$GLOBALS[\'auteur_session\'][\'login\'])
    { echo \'spip_cookie.php3?logout_public=\'.\$GLOBALS[\'auteur_session\'][\'login\'].\'&amp;url=\' .urlencode(' . $url . '); } ?".">")';
	$p->statut = 'php';
	return $p;
}

function balise_INTRODUCTION_dist ($p) {
	$_type = $p->type_requete;
	$_texte = champ_sql('texte', $p);
	$_chapo = champ_sql('chapo', $p);
	$_descriptif = champ_sql('descriptif', $p);
	$p->code = "calcul_introduction('$_type', $_texte, $_chapo, $_descriptif)";

	$p->statut = 'html';
	return $p;
}


// #LANG
// non documente ?
function balise_LANG_dist ($p) {
	$_lang = champ_sql('lang', $p);
	$p->code = "($_lang ? $_lang : \$GLOBALS['spip_lang'])";
	$p->statut = 'php';
	return $p;
}


// #LESAUTEURS
// les auteurs d'un article (ou d'un article syndique)
// http://www.spip.net/fr_article902.html
// http://www.spip.net/fr_article911.html
function balise_LESAUTEURS_dist ($p) {
	// Cherche le champ 'lesauteurs' dans la pile
	$_lesauteurs = champ_sql('lesauteurs', $p); 

	// Si le champ n'existe pas (cas de spip_articles), on donne la
	// construction speciale sql_auteurs(id_article) ;
	// dans le cas contraire on prend le champ 'les_auteurs' (cas de
	// spip_syndic_articles)
	if ($_lesauteurs AND $_lesauteurs != '$Pile[0][\'lesauteurs\']') {
		$p->code = $_lesauteurs;
	} else {
		$nom = $p->id_boucle;
	# On pourrait mieux faire qu'utiliser cette fonction assistante ?
		$p->code = "sql_auteurs(" .
			champ_sql('id_article', $p) .
			",'" .
			$nom .
			"','" .
			$p->boucles[$nom]->type_requete .
			"','" .
			$p->boucles[$nom]->sql_serveur .
			"')";
	}

	$p->statut = 'html';
	return $p;
}


// #PETITION
// Champ testant la presence d'une petition
// non documente mais indispensable a FORMULAIRE_PETITION

function balise_PETITION_dist ($p) {
	$nom = $p->id_boucle;
	$p->code = "sql_petitions(" .
			champ_sql('id_article', $p) .
			",'" .
			$p->boucles[$nom]->type_requete .
			"','" .
			$nom .
			"','" .
			$p->boucles[$nom]->sql_serveur .
			"', \$Cache)";
	$p->statut = 'php';
	return $p;
}


// #POPULARITE
// http://www.spip.net/fr_article1846.html
function balise_POPULARITE_dist ($p) {
	$_popularite = champ_sql('popularite', $p);
	$p->code = "(ceil(min(100, 100 * $_popularite
	/ max(1 , 0 + lire_meta('popularite_max')))))";
	$p->statut = 'php';
	return $p;
}


//
// Fonction commune aux balises #LOGO_XXXX
// (les balises portant ce type de nom sont traitees en bloc ici)
//
function calculer_balise_logo ($p) {

	eregi("^LOGO_([A-Z]+)(_.*)?$", $p->nom_champ, $regs);
	$type_objet = $regs[1];
	$suite_logo = $regs[2];	
	if (ereg("^_SPIP(.*)$", $suite_logo, $regs)) {
		$type_objet = 'RUBRIQUE';
		$suite_logo = $regs[1];
		$_id_objet = "\"'0'\"";
	} else {

		if ($type_objet == 'SITE')
			$_id_objet = champ_sql("id_syndic", $p);
		else
			$_id_objet = champ_sql("id_".strtolower($type_objet), $p);
	}
	// analyser les filtres
	$flag_fichier = false;
	$filtres = '';
	if (is_array($p->fonctions)) {
		foreach($p->fonctions as $nom) {
			if (ereg('^(left|right|center|top|bottom)$', $nom))
				$align = $nom;
			else if ($nom == 'lien') {
				$flag_lien_auto = 'oui';
				$flag_stop = true;
			}
			else if ($nom == 'fichier') {
				$flag_fichier = 'true';
				$flag_stop = true;
			}
			// double || signifie "on passe aux filtres"
			else if ($nom == '')
				$flag_stop = true;
			else if (!$flag_stop) {
				$lien = $nom;
				$flag_stop = true;
			}
			// apres un URL ou || ou |fichier ce sont
			// des filtres (sauf left...lien...fichier)
			else
				$filtres[] = $nom;
		}
		// recuperer les autres filtres s'il y en a
		$p->fonctions = $filtres;
	}

	//
	// Preparer le code du lien
	//
	// 1. filtre |lien
	if ($flag_lien_auto AND !$lien)
		$code_lien = '($lien = generer_url_'.$type_objet.'('.$_id_objet.')) ? $lien : ""';
	// 2. lien indique en clair (avec des balises : imprimer#ID_ARTICLE.html)
	else if ($lien) {
		$code_lien = "'".texte_script(trim($lien))."'";
		while (ereg("^([^#]*)#([A-Za-z_]+)(.*)$", $code_lien, $match)) {
			$c = new Champ();
			$c->nom_champ = $match[2];
			$c->id_boucle = $p->id_boucle;
			$c->boucles = &$p->boucles;
			$c->id_mere = $p->id_mere;
			$c = calculer_champ($c);
			$code_lien = str_replace('#'.$match[2], "'.".$c.".'", $code_lien);
		}
		// supprimer les '' disgracieux
		$code_lien = ereg_replace("^''\.|\.''$", "", $code_lien);
	}

	if ($flag_fichier)
	  $code_lien = "'',''" ; 
	else {
		if (!$code_lien)
			$code_lien = "''";
		$code_lien .= ", '". addslashes($align) . "'";
	}

	// cas des documents
	if ($type_objet == 'DOCUMENT') {
		$code_logo = "calcule_document($_id_objet, '" .
			$p->documents .
		  '\', $doublons)';
		if ($flag_fichier)
		  $p->code = "calcule_fichier_logo($code_logo)";
		else
		  $p->code = "affiche_logos($code_logo, '', $code_lien)";
	}
	else {
	  $p->code = "calcule_logo('$type_objet', '" .
	    (($suite_logo == '_SURVOL') ? 'off' : 
	     (($suite_logo == '_NORMAL') ? 'on' : 'ON')) .
	    "', $_id_objet," .
	    (($suite_logo == '_RUBRIQUE') ? 
	     champ_sql("id_rubrique", $p) :
	     (($type_objet == 'RUBRIQUE') ? "sql_parent($_id_objet)" : "''")) .
	    ", $code_lien, '$flag_fichier')";
	}
	$p->statut = 'php';
	return $p;
}

// #EXTRA [(#EXTRA|isbn)]
// Champs extra
// Non documentes, en voie d'obsolescence, cf. ecrire/inc_extra.php3
function balise_EXTRA_dist ($p) {
	$_extra = champ_sql('extra', $p);
	$p->code = $_extra;

	// Gerer la notation [(#EXTRA|isbn)]
	if ($p->fonctions) {
		include_ecrire("inc_extra.php3");
		list ($key, $champ_extra) = each($p->fonctions);	// le premier filtre
		$type_extra = $p->type_requete;
			// ci-dessus est sans doute un peu buggue : si on invoque #EXTRA
			// depuis un sous-objet sans champ extra d'un objet a champ extra,
			// on aura le type_extra du sous-objet (!)
		if (extra_champ_valide($type_extra, $champ_extra)) {
			unset($p->fonctions[$key]);
			$p->code = "extra($p->code, '".addslashes($champ_extra)."')";

			// Appliquer les filtres definis par le webmestre
			$filtres = extra_filtres($type_extra, $champ_extra);
			if ($filtres) foreach ($filtres as $f)
				$p->code = "$f($p->code)";
		}
	}

	$p->statut = 'html';
	return $p;
}

//
// Parametres de reponse a un forum
//

function balise_PARAMETRES_FORUM_dist($p) {
	include_local('inc-formulaire_forum.php3');
	$_accepter_forum = champ_sql('accepter_forum', $p);
	$p->code = '
	// refus des forums ?
	('.$_accepter_forum.'=="non" OR
	(lire_meta("forums_publics") == "non" AND !ereg("^(pos|pri|abo)", '.$_accepter_forum.')))
	? "" : // sinon:
	';

	switch ($p->type_requete) {
		case 'articles':
			$c = '"id_article=".' . champ_sql('id_article', $p);
			break;
		case 'breves':
			$c = '"id_breve=".' . champ_sql('id_breve', $p);
			break;
		case 'rubriques':
			$c = '"id_rubrique=".' . champ_sql('id_rubrique', $p);
			break;
		case 'syndication':
			$c = '"id_syndic=".' . champ_sql('id_syndic', $p);
			break;
		case 'forums':
		default:
			$liste_champs = array ("id_article","id_breve","id_rubrique","id_syndic","id_forum");
			foreach ($liste_champs as $champ) {
				$x = champ_sql( $champ, $p);
				$c .= (($c) ? ".\n" : "") . "((!$x) ? '' : ('&$champ='.$x))";
			}
			$c = "substr($c,1)";
			break;
	}

	$c .= '.
	"&retour=".rawurlencode($lien=$GLOBALS["HTTP_GET_VARS"]["retour"] ? $lien : nettoyer_uri())';

	$p->code .= code_invalideur_forums($p, "(".$c.")");

	$p->statut = 'html';
	return $p;
}


// Noter l'invalideur de la page contenant ces parametres,
// en cas de premier post sur le forum
function code_invalideur_forums($p, $code) {
	return '
	// invalideur forums
	(!($Cache[\'id_forum\'][calcul_index_forum(' . 
				// Retournera 4 [$SP] mais force la demande du champ a MySQL
				champ_sql('id_article', $p) . ',' .
				champ_sql('id_breve', $p) .  ',' .
				champ_sql('id_rubrique', $p) .',' .
				champ_sql('id_syndic', $p) .  ")]=1)".
				"?'':\n" . $code .")";
}

// reference a l'URL de la page courante

function balise_SELF_dist($p) {
	$p->code = 'quote_amp($GLOBALS["clean_link"]->getUrl())';
	$p->statut = 'php';
	return $p;
}

// reference aux parametres GET & POST

function balise_HTTP_VARS_dist($p) {
	$nom = param_balise($p);
	if (!$nom)
		erreur_squelette(_L("Champ #HTTP_VARS argument manquant"),
				$p->id_boucle);
	else {
		$p->code = '$Pile[0]["' . addslashes($nom) . '"]';
		$p->statut = 'php';
	}
	return $p;
}
?>
