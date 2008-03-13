<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2008                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;	#securite

// Pas besoin de contexte de compilation

// http://doc.spip.org/@balise_FORMULAIRE_RECHERCHE_stat
function balise_FORMULAIRE_RECHERCHE_stat($args, $filtres) {
	// filtres[0] doit etre un script (a revoir)
	return array($filtres[0], $args ? $args[0] : '');
}

?>