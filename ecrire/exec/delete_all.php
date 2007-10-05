<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2007                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/abstract_sql');

// http://doc.spip.org/@exec_delete_all_dist
function exec_delete_all_dist()
{
	include_spip('inc/autoriser');
	if (!autoriser('detruire')) {
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}
	$q = sql_showbase();
	$res = '';
	while ($r = sql_fetch($q)) {
		$t = array_shift($r);
		$res .= "<li>"
		.  "<input type='checkbox' checked='checked' name='delete[]' id='delete_$t' value='$t'/>\n"
		. $t
		. "\n</li>";
	}
	  
	if (!$res) {
	  	include_spip('inc/minipres');
		spip_log("Erreur base de donnees");
		echo minipres(_T('info_travaux_titre'), _T('titre_probleme_technique'). "<p><tt>".sql_errno()." ".sql_error()."</tt></p>");
		exit;
	} else spip_log($res);

	$res = "<ol style='text-align:left'>$res</ol>";
	$r = generer_url_ecrire('install','',true);
	$admin = charger_fonction('admin', 'inc');
	$admin('delete_all', _T('titre_page_delete_all'), $res, $r);
}
?>
