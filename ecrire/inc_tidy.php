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


//
// Ce fichier ne sera execute qu'une fois
if (defined("_ECRIRE_INC_TIDY")) return;
define("_ECRIRE_INC_TIDY", "1");


function version_tidy() {
	static $version = -1;
	if ($version == -1) {
		$version = 0;
		if (function_exists('tidy_parse_string')) {
			$version = 1;
			if (function_exists('tidy_get_body')) {
				$version = 2;
			}
		}
	}
	return $version;
}


function echappe_xhtml ($letexte) { // oui, c'est dingue... on echappe le mathml
	$regexp_echap_math = "/<math((.*?))<\/math>/si";
	$source = "xhtml";

	while (preg_match($regexp_echap_math, $letexte, $regs)) {
		$num_echap++;
		
		$les_echap[$num_echap] = $regs[0];

		$pos = strpos($letexte, $regs[0]);
		$letexte = substr($letexte,0,$pos)."@@SPIP_$source$num_echap@@"
			.substr($letexte,$pos+strlen($regs[0]));
	}
	
	// et les textarea
	$regexp_echap_cadre = "/<textarea((.*?))<\/textarea>/si";
	$source = "xhtml";

	while (preg_match($regexp_echap_cadre, $letexte, $regs)) {
		$num_echap++;
		
		$les_echap[$num_echap] = $regs[0];

		$pos = strpos($letexte, $regs[0]);
		$letexte = substr($letexte,0,$pos)."@@SPIP_$source$num_echap@@"
			.substr($letexte,$pos+strlen($regs[0]));
	}

	return array($letexte, $les_echap);
}

function xhtml ($buffer) {
	global $tidy_command;


	if (strlen($tidy_command) > 0) {
		// Si l'on a defini un chemin pour tidyHTML 
		// en ligne de commande 
		// (pour les sites qui n'ont pas tidyPHP)

		include_ecrire("inc_texte.php3");

		$retour_echap = echappe_xhtml ($buffer);
		$buffer = $retour_echap[0];
		$les_echap = $retour_echap[1];
		$charset = lire_meta('charset');
		if ($charset == "iso-8859-1") $enc_char = "latin1";
		else if ($charset == "utf-8") $enc_char = "utf8";
		else return echappe_retour($buffer, $les_echap, "xhtml");

		$nomfich = _DIR_CACHE."bidouille".rand();
		$f = fopen($nomfich, 'wb');
		fputs($f, $buffer);
		fclose($f);
		
		exec("$tidy_command --char-encoding $enc_char --quote-nbsp false --show-body-only false --clean true --indent true --wrap false --output-xhtml true --add-xml-decl false -m $nomfich");
		
		$tidy = join(file($nomfich),"");
		@unlink($nomfich);
		
		$tidy = echappe_retour($tidy, $les_echap, "xhtml");
		$tidy = ereg_replace ("\<\?xml([^\>]*)\>", "", $tidy);
		return $tidy;

	}
	else if (version_tidy() == "1") {
		include_ecrire("inc_texte.php3");

		$retour_echap = echappe_xhtml ($buffer);
		$buffer = $retour_echap[0];
		$les_echap = $retour_echap[1];

		// Options selon: http://tidy.sourceforge.net/docs/quickref.html
		$charset = lire_meta('charset');
		if ($charset == "iso-8859-1") $enc_char = "latin1";
		else if ($charset == "utf-8") $enc_char = "utf8";
		else return echappe_retour($buffer, $les_echap, "xhtml");

		tidy_set_encoding ($enc_char);
		tidy_setopt('wrap', 0);
		tidy_setopt('indent-spaces', 4);
		tidy_setopt('output-xhtml', true);
		tidy_setopt('add-xml-decl', false);
		tidy_setopt('indent', 5);
		tidy_setopt('show-body-only', false);
		tidy_setopt('quote-nbsp', false);

		tidy_parse_string($buffer);
		tidy_clean_repair();
		$tidy = tidy_get_output();
		$tidy = echappe_retour($tidy, $les_echap, "xhtml");
		// En Latin1, tidy ajoute une declaration XML
		// (malgre add-xml-decl a false) ; il faut le supprimer
		// pour eviter interpretation PHP provoquant une erreur
		$tidy = ereg_replace ("\<\?xml([^\>]*)\>", "", $tidy);
		return $tidy;
	}
	else if (version_tidy() == "2") {
		include_ecrire("inc_texte.php3");
	
		$retour_echap = echappe_xhtml ($buffer);
		$buffer = $retour_echap[0];
		$les_echap = $retour_echap[1];

		// Options selon: http://tidy.sourceforge.net/docs/quickref.html
		$charset = lire_meta('charset');
		if ($charset == "iso-8859-1") $enc_char = "latin1";
		else if ($charset == "utf-8") $enc_char = "utf8";
		else return echappe_retour($buffer, $les_echap, "xhtml");

		$config = array(
			'wrap' => 0,
			'indent-spaces' => 4,
			'output-xhtml' => true,
			'add-xml-decl' => false,
			'indent' => 5,
			'show-body-only' => false,
			'quote-nbsp' => false
			);
		$tidy = tidy_parse_string($buffer, $config, $enc_char);
		tidy_clean_repair($tidy);
		$tidy = ereg_replace ("\<\?xml([^\>]*)\>", "", $tidy);
		return $tidy;
	}
	else
		return $buffer;
}

?>