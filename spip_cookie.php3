<?php

include ("ecrire/inc_version.php3");
include_ecrire ("inc_connect.php3");
include_ecrire ("inc_meta.php3");
include_ecrire ("inc_session.php3");

if ($url)
	$cible = new Link(urldecode($url));
else
	$cible = new Link('ecrire/');

// rejoue le cookie pour renouveler spip_session
if ($change_session == 'oui') {
	if (verifier_session($spip_session)) {
		$cookie = creer_cookie_session($auteur_session);
		supprimer_session($spip_session);
//		setcookie ('spip_session', $spip_session, time() - 24 * 7 * 3600);
		setcookie('spip_session', $cookie);
		@header('Content-Type: image/gif');
		@header('Expires: 0');
		@header("Cache-Control: no-store, no-cache, must-revalidate");
		@header('Pragma: no-cache');
		@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		@readfile('ecrire/img_pack/rien.gif');
		exit;
	}
}


// tentative de login
if ($essai_login == "oui") {
	// recuperer le login passe en champ hidden
	if ($session_login_hidden AND !$session_login)
		$session_login = $session_login_hidden;

	// verifier l'auteur
	$login = addslashes($session_login);
	if ($session_password_md5) {
		$md5pass = $session_password_md5;
		$md5next = $next_session_password_md5;
	}
	else {
		$query = "SELECT * FROM spip_auteurs WHERE login='$login' AND statut!='5poubelle'";
		$result = spip_query($query);
		if ($row = mysql_fetch_array($result)) {
			$md5pass = md5($row['alea_actuel'] . $session_password);
			$md5next = md5($row['alea_futur'] . $session_password);
		}
	}

	$query = "SELECT * FROM spip_auteurs WHERE login='$login' AND pass='$md5pass' AND statut<>'5poubelle'";

	$result = spip_query($query);

	if ($row_auteur = mysql_fetch_array($result)) { // login reussi
		if ($row_auteur['statut'] == 'nouveau') { // nouvel inscrit
			spip_query ("UPDATE spip_auteurs SET statut='1comite' WHERE login='$login'");
			$row_auteur['statut'] = '1comite';
		}

		if ($row_auteur['statut'] == '0minirezo') // force le cookie pour les admins
			$cookie_admin = "@".$row_auteur['login'];

		$cookie_session = creer_cookie_session($row_auteur);
		setcookie('spip_session', $cookie_session);
	
		// fait tourner le codage du pass dans la base
		$nouvel_alea_futur = creer_uniqid();
		$query = "UPDATE spip_auteurs
			SET alea_actuel = alea_futur,
				pass = '$md5next',
				alea_futur = '$nouvel_alea_futur'
			WHERE login='$login'";
		@spip_query($query);
		$cible->addVar('bonjour','oui');
	}
	else {
		$url = urlencode($cible->getUrl());
		if ($session_password || $session_password_md5) 
			@header("Location: spip_login.php3?login=$login&erreur=pass&url=$url");
		else
			@header("Location: spip_login.php3?login=$login&url=$url");
		exit;
	}
}

// cookie d'admin ?
if ($cookie_admin == "non") {
	setcookie('spip_admin', $spip_admin, time() - 3600 * 24);
}
else if ($cookie_admin AND $spip_admin != $cookie_admin) {
	setcookie('spip_admin', $cookie_admin, time() + 3600 * 24 * 14);
}

// redirection
@header("Location: " . $cible->getUrl());

?>
