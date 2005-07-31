<?php

if (!defined("_ECRIRE_INC_VERSION")) return;	#securite

// Ce "menu_lang" collecte dans le contexte permet de forcer la langue
// par defaut proposee dans le menu ; mais a quoi ca sert concretement ?
global $balise_MENU_LANG_ECRIRE_collecte;
$balise_MENU_LANG_ECRIRE_collecte = array('menu_lang');

// s'il n'y a qu'une langue proposee eviter definitivement la balise ?php 
function balise_MENU_LANG_ECRIRE_stat ($args, $filtres) {
	global $all_langs;
	include_ecrire('inc_lang.php3');
	if (strpos($all_langs,',') === false) return '';
	return $args;
}


// normalement $opt sera toujours non vide suite au test ci-dessus
function balise_MENU_LANG_ECRIRE_dyn($default) {
	return menu_lang_pour_tous('var_lang_ecrire', $opt);
}

function menu_lang_pour_tous($nom, $opt) {
	include_ecrire("inc_lang.php3");

	// Voir s'il y a une langue demandee par _request,
	// ou une langue par defaut dans le contexte {menu_lang=xx}
	$default = _request('lang');
	lang_select($default);
	if ($GLOBALS['spip_lang'] <> $default) {
		$default = $menu_lang;
		lang_select($default);	# et remplace
		if ($GLOBALS['spip_lang'] <> $default)
			unset ($default);	# annule tout choix par defaut
		lang_dselect();	#annule la selection
	}
	lang_dselect();

	$opt = liste_options_langues($nom, $default);
	if (!$opt)
		return '';

	$site = lire_meta("adresse_site");
	$post = ($site ? $site : '..') . "/spip_cookie.php3";
	$cible = new Link('', /* racine */ true); # lien a partir de /
	$cible->delVar('lang');
	$cible = $cible->getUrl();
	$postcomplet = new Link($post);
	$postcomplet->addvar('url', $cible);

	return array('formulaire_menu_lang',
		3600,
		array('nom' => $nom,
			'url' => $post,
			'cible' => $cible,
			'retour' => $postcomplet->getUrl(),
			'langues' => $opt
		)
	);
}

?>
