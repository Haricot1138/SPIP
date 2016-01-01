<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2016                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');

function instituer_auteur_ici($auteur=array()){
	$instituer_auteur = charger_fonction('instituer_auteur', 'inc');
	return $instituer_auteur($auteur);
}

// http://doc.spip.org/@inc_editer_mot_dist
function formulaires_editer_auteur_charger_dist($id_auteur='new', $retour='', $lier_id_article=0, $config_fonc='auteurs_edit_config', $row=array(), $hidden=''){
	$valeurs = formulaires_editer_objet_charger('auteur',$id_auteur,0,0,$retour,$config_fonc,$row,$hidden);
	if ($lier_id_article) $valeurs['lier_id_article'] = $lier_id_article;
	// forcer la prise en compte du post, sans verifier si c'est bien le meme formulaire,
	// c'est trop hasardeux selon le contenud de $row
	$valeurs['_forcer_request'] = true;
	if (empty($valeurs['source']))
		$valeurs['source'] = spip_connect_ldap() ? 'ldap' : 'spip';
	return $valeurs;
}

// Choix par defaut des options de presentation
// http://doc.spip.org/@articles_edit_config
function auteurs_edit_config($row)
{
	global $spip_ecran, $spip_lang, $spip_display;

	$config = $GLOBALS['meta'];
	$config['lignes'] = ($spip_ecran == "large")? 8 : 5;
	$config['afficher_barre'] = $spip_display != 4;
	$config['langue'] = $spip_lang;

	// pour instituer_auteur
	$config['auteur'] = $row;
	
	//$config['restreint'] = ($row['statut'] == 'publie');
	return $config;
}

function formulaires_editer_auteur_verifier_dist($id_auteur='new', $retour='', $lier_article=0, $config_fonc='auteurs_edit_config', $row=array(), $hidden=''){
	include_spip('inc/filtres');
	$erreurs = formulaires_editer_objet_verifier('auteur',$id_auteur,array('nom'));
	// tags présents dans le nom/signature
	if (!nom_acceptable(_request('nom'))) {
		$erreurs['nom'] = _T("info_nom_pas_conforme");
		$erreurs['message_erreur'] .= _T("info_nom_pas_conforme");
	}
	// on ne veut pas pas ce protocole là
	if (preg_match(",^\s*javascript,i", _request('url_site'))) {
		$erreurs['url_site'] = _T('info_url_site_pas_conforme');
		$erreurs['message_erreur'] .= _T("info_url_site_pas_conforme");
	}
	// login trop court ou existant
	if ($p = _request('new_login')) {
		if ((strlen($p) < _LOGIN_TROP_COURT)
			AND $p !== sql_getfetsel("login", "spip_auteurs", "id_auteur=" . sql_quote($id_auteur))) {
			$erreurs['login'] = _T('info_login_trop_court');
			$erreurs['message_erreur'] .= _T('info_login_trop_court');
		} elseif (sql_countsel('spip_auteurs', "login=" . sql_quote($p) . " AND id_auteur!=" . intval($id_auteur) . " AND statut!='5poubelle'")) {
			$erreurs['new_login'] .= _T('info_login_existant');
			$erreurs['message_erreur'] .= _T('info_login_existant');
		}
	}
	// pass trop court ou confirmation non identique
	if ($p = _request('new_pass')) {
		if (strlen($p) < 6) {
			$erreurs['new_pass'] = _T('info_passe_trop_court');
			$erreurs['message_erreur'] .= _T('info_passe_trop_court');
		} elseif ($p != _request('new_pass2')) {
			$erreurs['new_pass'] = _T('info_passes_identiques');
			$erreurs['message_erreur'] .= _T('info_passes_identiques');
		}
	}
	return $erreurs;
}

// http://doc.spip.org/@inc_editer_mot_dist
function formulaires_editer_auteur_traiter_dist($id_auteur='new', $retour='', $lier_article=0, $config_fonc='auteurs_edit_config', $row=array(), $hidden=''){
	return formulaires_editer_objet_traiter('auteur',$id_auteur,0,0,$retour,$config_fonc,$row,$hidden);
	//return $message;
}

?>
