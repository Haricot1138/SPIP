<?php

include ("ecrire/inc_version.php3");

include_ecrire("inc_index.php3");
include_ecrire("inc_meta.php3");
include_ecrire("inc_admin.php3");
include_local("inc-cache.php3");


// verifier les formats acceptes par GD

if (($test_formats == "oui") AND $flag_function_exists) {
	$gd_formats = Array();
	if (function_exists('ImageCreateFromJPEG')) {
		$srcImage = @ImageCreateFromJPEG("IMG/test.jpg");
		if ($srcImage) {
			$gd_formats[] = "jpg";
			ImageDestroy( $srcImage );
		}
	}
	if (function_exists('ImageCreateFromGIF')) {
		$srcImage = @ImageCreateFromGIF("IMG/test.gif");
		if ($srcImage) {
			$gd_formats[] = "gif";
			ImageDestroy( $srcImage );
		}
	}
	if (function_exists('ImageCreateFromPNG')) {
		$srcImage = @ImageCreateFromPNG("IMG/test.png");
		if ($srcImage) {
			$gd_formats[] = "png";
			ImageDestroy( $srcImage );
		}
	}

	if ($gd_formats) $gd_formats = join($gd_formats, ",");
	ecrire_meta("gd_formats", $gd_formats);
	ecrire_metas();
}


//
// Creation automatique d'une vignette
//

function creer_vignette($image, $newWidth, $newHeight, $format) {
	// Recuperer l'image d'origine
	if ($format == "jpg") {
		$srcImage = @ImageCreateFromJPEG($image);
	}
	else if ($format == "gif"){
		$srcImage = @ImageCreateFromGIF($image);
	}
	else if ($format == "png"){
		$srcImage = @ImageCreateFromPNG($image);
	}
	if (!$srcImage) return;

	// Calculer le ratio
	$srcWidth = ImageSX($srcImage);
	$srcHeight = ImageSY($srcImage);

	$ratioWidth = $srcWidth/$newWidth;
	$ratioHeight = $srcHeight/$newHeight;

	if ($ratioWidth < $ratioHeight) {
		$destWidth = $srcWidth/$ratioHeight;
		$destHeight = $newHeight;
	}
	else {
		$destWidth = $newWidth;
		$destHeight = $srcHeight/$ratioWidth;
	}

	// Choisir le format destination
	// - on sauve de preference en JPEG (meilleure compression)
	// - pour le GIF : les GD recentes peuvent le lire mais pas l'ecrire
	$gd_formats = lire_meta("gd_formats");
	if (ereg("jpg", $gd_formats))
		$destFormat = "jpg";
	else if ($format == "gif" AND ereg("gif", $gd_formats) AND $GLOBALS['flag_ImageGif'])
		$destFormat = "gif";
	else if (ereg("png", $gd_formats))
		$destFormat = "png";
	if (!$destFormat) return;

	// Initialisation de l'image destination
	if ($GLOBALS['flag_ImageCreateTrueColor'] AND $destFormat != "gif")
		$destImage = ImageCreateTrueColor($destWidth, $destHeight);
	if (!$destImage)
		$destImage = ImageCreate($destWidth, $destHeight);

	// Recopie de l'image d'origine avec adaptation de la taille
	$ok = false;
	if ($GLOBALS['flag_ImageCopyResampled'])
		$ok = @ImageCopyResampled($destImage, $srcImage, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);
	if (!$ok)
		$ok = ImageCopyResized($destImage, $srcImage, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);

	// Sauvegarde de l'image destination
	$destination = ereg_replace('\.(.*)$','-s',$image);
	if ($destFormat == "jpg") {
		ImageJPEG($destImage, "$destination.jpg", 60);
	}
	else if ($destFormat == "gif") {
		ImageGIF($destImage, "$destination.gif");
	}
	else if ($destFormat == "png") {
		ImagePNG($destImage, "$destination.png");
	}
	ImageDestroy($srcImage);
	ImageDestroy($destImage);

	//exit;
	$retour['width'] = $destWidth;
	$retour['height'] = $destHeight;
	$retour['fichier'] = $destination;
	$retour['format'] = $format;
	return $retour;
}


//
// Deplacer un fichier uploade
//

function deplacer_fichier_upload($source, $dest) {
	// Securite
	if (strstr($dest, "..")) {
		exit;
	}

	$ok = @copy($source, $dest);
	if (!$ok) $ok = @move_uploaded_file($source, $dest);
	if ($ok)
		@chmod($dest, 0666);
	else {
		$f = @fopen($dest,'w');
		if ($f)
			fclose ($f);
		else {
			@header ("Location: spip_test_dirs.php3?test_dir=".dirname($dest));
			exit;
		}
	}

	return $ok;
}


//
// Convertit le type numerique retourne par getimagesize() en extension fichier
//

function decoder_type_image($type, $strict = false) {
	switch ($type) {
	case 1:
		return "gif";
	case 2:
		return "jpg";
	case 3:
		return "png";
	case 4:
		return $strict ? "" : "swf";
	case 5:
		return "psd";
	case 6:
		return "bmp";
	case 7:
	case 8:
		return "tif";
	default:
		return "";
	}
}


//
// Corrige l'extension du fichier dans quelques cas particuliers
//

function corriger_extension($ext) {
	switch ($ext) {
	case 'htm':
		return 'html';
	case 'jpeg':
		return 'jpg';
	case 'tiff':
		return 'tif';
	default:
		return $ext;
	}
}


//
// Ajouter une image (logo)
//

function ajout_image($source, $dest) {
	global $redirect_url, $hash_id_auteur, $hash, $num_img;

	// Securite
	if (!verifier_action_auteur("ajout_image $dest", $hash, $hash_id_auteur)) {
		exit;
	}

	$loc = "IMG/$dest";
	if (!deplacer_fichier_upload($source, $loc)) return;

	// analyse le type de l'image (on ne fait pas confiance au nom de
	// fichier envoye par le browser : pour les Macs c'est plus sur)
	$size = @getimagesize($loc);
	$type = decoder_type_image($size[2], true);

	if ($type) {
		rename($loc, "$loc.$type");
		$dest = "$dest.$type";
		$loc = "$loc.$type";
	}
	else {
		unlink($loc);
	}
}


//
// Ajouter un document
//

function ajout_doc($orig, $source, $dest, $mode, $id_document, $doc_vignette='', $titre_vignette='', $descriptif_vignette='', $titre_automatique=true) {
	global $hash_id_auteur, $hash, $id_article, $type;

	//
	// Securite
	//
	if (!verifier_action_auteur("ajout_doc", $hash, $hash_id_auteur)) {
		exit;
	}
	

	if (ereg("\.([^.]+)$", $orig, $match)) {
		$ext = addslashes(strtolower($match[1]));
		$ext = corriger_extension($ext);
	}
	$query = "SELECT * FROM spip_types_documents WHERE extension='$ext' AND upload='oui'";

	if ($mode == 'vignette')
		$query .= " AND inclus='image'";

	$result = spip_query($query);
	if ($row = @spip_fetch_array($result)) {
		$id_type = $row['id_type'];
		$type_inclus = $row['inclus'];
	}
	else return false;

	//
	// Preparation
	//

	if ($mode == 'vignette') {
		$id_document_lie = $id_document;
		$query = "UPDATE spip_documents SET mode='document' where id_document=$id_document_lie";
		spip_query($query); // requete inutile a mon avis (Fil)...
		$id_document = 0;
	}
	if (!$id_document) {
		$query = "INSERT INTO spip_documents (id_type, titre, date) VALUES ($id_type, '', NOW())";
		spip_query($query);
		$id_document = spip_insert_id();
		$nouveau = true;
		if ($id_article) {
			$query = "INSERT INTO spip_documents_".$type."s (id_document, id_".$type.") VALUES ($id_document, $id_article)";
			spip_query($query);
		}
	}

	$dest = 'IMG/';
	if (creer_repertoire('IMG', $ext))
		$dest .= $ext.'/';
	$dest .= ereg_replace("[^.a-zA-Z0-9_=-]+", "_",
	nettoyer_chaine_indexation(ereg_replace("\.([^.]+)$", "", supprimer_tags(basename($orig)))));
	$n = 0;
	while (file_exists($newFile = $dest.($n++ ? '-'.$n : '').'.'.$ext));
	$dest_path = $newFile;

	if (!deplacer_fichier_upload($source, $dest_path)) return false;

	// Creer une vignette automatiquement
	$creer_preview=lire_meta("creer_preview");
	$taille_preview=lire_meta("taille_preview");
	$gd_formats = lire_meta("gd_formats");
	$format_img = strtolower(substr($dest_path, strrpos($dest_path,".")+1, strlen($dest_path)));
	if ($format_img == "jpeg") $format_img == "jpg";

	if ($taille_preview < 10) $taille_preview = 120;

	if ($mode == 'document' AND $format_img AND ereg($format_img, $gd_formats) AND $creer_preview == 'oui') {
		$preview = creer_vignette($dest_path, $taille_preview, $taille_preview, $format_img);
		$hauteur_prev = $preview['height'];
		$largeur_prev = $preview['width'];
		$fichier_prev = $preview['fichier'];
		$format_prev = $preview['format'];
		if ($format_prev == "jpg") $format_prev = 1;
		else if ($format_prev == "png") $format_prev = 2;
		else if ($format_prev == "gif") $format_prev = 3;

		$query = "INSERT INTO spip_documents (id_type, titre, largeur, hauteur, fichier, date) VALUES ('$format_prev', '', '$largeur_prev', '$hauteur_prev', '$fichier_prev', NOW())";
		spip_query($query);
		$id_preview = spip_insert_id();
		$query = "UPDATE spip_documents SET id_vignette = '$id_preview' WHERE id_document = $id_document";
		spip_query($query);
	}

	//
	// Recopier le fichier
	//

	$size_image = getimagesize($dest_path);
	$type_image = decoder_type_image($size_image[2]);
	if ($type_image) {
		$largeur = $size_image[0];
		$hauteur = $size_image[1];
	}
	$taille = filesize($dest_path);

	if ($nouveau) {
		if (!$mode) $mode = ($type_image AND $type_inclus == 'image') ? 'vignette' : 'document';
		$titre = ereg_replace("\..*$", "", $orig);
		$titre = ereg_replace("ecrire/|upload/", "", $titre);
		$titre = strtr($titre, "_", " ");
		if (!$titre_automatique) $titre = "";
		//$update = "mode='$mode', titre='".addslashes($titre)."', ";
		$update = "mode='$mode', ";
	}

	$query = "UPDATE spip_documents SET $update taille='$taille', largeur='$largeur', hauteur='$hauteur', fichier='$dest_path' ".
		"WHERE id_document=$id_document";
	spip_query($query);

	if ($id_document_lie) {
		$query = "UPDATE spip_documents SET id_vignette=$id_document WHERE id_document=$id_document_lie";
		spip_query($query);
		$id_document = $id_document_lie; // pour que le 'return' active le bon doc.
	}

	if ($doc_vignette){
		$query = "UPDATE spip_documents SET id_vignette=$doc_vignette, titre='', descriptif='' WHERE id_document=$id_document";
		spip_query($query);

	}


	return $id_document;
}



// image_name n'est valide que par POST http, mais pas par la methode ftp/upload
// par ailleurs, pour un fichier ftp/upload, il faut effacer l'original nous-memes
if (!$image_name AND $image2) {
	$image = "ecrire/upload/".$image2;
	$image_name = $image;
	$supprimer_ecrire_upload = $image;
} else {
	$supprimer_ecrire_upload = '';
}

//
// ajouter un document
//
if ($ajout_doc == 'oui') {
	if ($dossier_complet){
		$myDir = opendir('ecrire/upload');
		while($entryName = readdir($myDir)) {
			if (is_file("ecrire/upload/".$entryName) AND !($entryName=='remove.txt')) {
			if (ereg("\.([^.]+)$", $entryName, $match)) {
					$ext = strtolower($match[1]);
					if ($ext == 'jpeg')
						$ext = 'jpg';
					$req = "SELECT extension FROM spip_types_documents WHERE extension='$ext'";
					if ($inclus)
						$req .= " AND inclus='$inclus'";
					if (@spip_fetch_array(spip_query($req)))
						$id_document = ajout_doc('ecrire/upload/'.$entryName, 'ecrire/upload/'.$entryName, '', 'document', '','','','',false);
				}
			}
		}
		closedir($myDir);
	
	} 
	else {
		if ($forcer_document == 'oui')
			$id_document = ajout_doc($image_name, $image, $fichier, "document", $id_document);
		else
			$id_document = ajout_doc($image_name, $image, $fichier, $mode, $id_document);
	}
}


// joindre un document
if ($joindre_doc == 'oui'){
	$id_document = ajout_doc($image_name, $image, $fichier, "document", $id_document, $doc_vignette, $titre_vignette, $descriptif_vignette);
}


//
// ajouter un logo
//
if ($ajout_logo == "oui") {
	ajout_image($image, $logo);
}

//
// supprimer un logo
//
if ($image_supp) {
	// Securite
	if (strstr($image_supp, "..")) {
		exit;
	}
	if (!verifier_action_auteur("supp_image $image_supp", $hash, $hash_id_auteur)) {
		exit;
	}
	@unlink("IMG/$image_supp");
}

//
// supprimer un doc
//
if ($doc_supp) {
	// Securite
	if (!verifier_action_auteur("supp_doc $doc_supp", $hash, $hash_id_auteur)) {
		exit;
	}
	$query = "SELECT id_vignette, fichier FROM spip_documents WHERE id_document=$doc_supp";
	$result = spip_query($query);
	if ($row = spip_fetch_array($result)) {
		$fichier = $row['fichier'];
		$id_vignette = $row['id_vignette'];
		spip_query("DELETE FROM spip_documents WHERE id_document=$doc_supp");
		spip_query("UPDATE spip_documents SET id_vignette=0 WHERE id_vignette=$doc_supp");
		spip_query("DELETE FROM spip_documents_articles WHERE id_document=$doc_supp");
		spip_query("DELETE FROM spip_documents_rubriques WHERE id_document=$doc_supp");
		spip_query("DELETE FROM spip_documents_breves WHERE id_document=$doc_supp");
		@unlink($fichier);
	}

	if ($id_vignette > 0) {
		$query = "SELECT id_vignette, fichier FROM spip_documents WHERE id_document=$doc_supp";
		$result = spip_query($query);
		if ($row = spip_fetch_array($result)) {
			$fichier = $row['fichier'];
			@unlink($fichier);

		}	
		spip_query("DELETE FROM spip_documents WHERE id_document=$id_vignette");
		spip_query("DELETE FROM spip_documents_articles WHERE id_document=$id_vignette");
		spip_query("DELETE FROM spip_documents_rubriques WHERE id_document=$doc_supp");
		spip_query("DELETE FROM spip_documents_breves WHERE id_document=$doc_supp");
	}


}


// supprimer le fichier original si pris dans ecrire/upload
/* en debat.... peser securite vs conformite upload http
if ($supprimer_ecrire_upload)
	@unlink ($supprimer_ecrire_upload);
*/


//
// redirection
//
if ($HTTP_POST_VARS) $vars = $HTTP_POST_VARS;
else $vars = $HTTP_GET_VARS;
$redirect_url = "ecrire/" . $vars["redirect"];
$link = new Link($redirect_url);
reset($vars);
while (list ($key, $val) = each ($vars)) {
	if (!ereg("^(redirect|image.*|hash.*|ajout.*|doc.*|transformer.*|modifier_.*|ok|type|forcer_.*)$", $key)) {
		$link->addVar($key, $val);
	}
}
if ($id_document)
	$link->addVar('id_document',$id_document);
if ($type == 'rubrique')
	$link->delVar('id_article');

@header ("Location: ".$link->getUrl());

exit;
?>
