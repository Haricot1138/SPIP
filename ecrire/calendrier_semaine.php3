<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2005                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/


include ("inc.php3");

// sans arguments => mois courant
if (!$mois){
  $today=getdate(time());
  $jour=$today["mday"];
  $mois=$today["mon"];
  $annee=$today["year"];
}

$date = date("Y-m-d", mktime(0,0,0,$mois, $jour, $annee));
$jour = journum($date);
$mois = mois($date);
$annee = annee($date);

$afficher_bandeau_calendrier = true;
$afficher_bandeau_calendrier_semaine = true;

debut_page(_T('titre_page_calendrier',
	      array('nom_mois' => nom_mois($date), 'annee' => $annee)),
	   "redacteurs", 
	   "calendrier");

echo http_calendrier_semaine($jour,$mois,$annee);

// fin_page();
?>
