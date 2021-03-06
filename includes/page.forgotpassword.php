<?php
/*****************************************************************************************
** © 2013 POULAIN Nicolas – nico.public@ouvaton.org - http://tounoki.org **
** **
** Ce fichier est une partie du logiciel libre WeAreMuseomix, licencié **
** sous licence "CeCILL version 2". **
** La licence est décrite plus précisément dans le fichier : LICENSE.txt **
** **
** ATTENTION, CETTE LICENCE EST GRATUITE ET LE LOGICIEL EST **
** DISTRIBUÉ SANS GARANTIE D'AUCUNE SORTE **
** ** ** ** **
** This file is a part of the free software project We Are Museomix,
** licensed under the "CeCILL version 2". **
**The license is discribed more precisely in LICENSES.txt **
** **
**NOTICE : THIS LICENSE IS FREE OF CHARGE AND THE SOFTWARE IS DISTRIBUTED WITHOUT ANY **
** WARRANTIES OF ANY KIND **
*****************************************************************************************/
if (stristr($_SERVER['REQUEST_URI'], "page."))
	die(_('Vous vous engagez sur une voie risquée et votre IP est enregistrée :(')) ;

if ( isset($_POST['send']) ) {
	$mail = dataClean($_POST['user_email'],'mail') ;

	$sql = "SELECT COUNT(*) AS nb FROM ".TABLE_USERS." WHERE user_email LIKE '$mail' " ;
	$results = UPDO::getInstance()->query( $sql ) ;
	foreach ( $results as $result ) {
		$count = $result['nb'] ;
	}
	$result = NULL ; $sql = NULL ; $results = NULL ;
	if ( $count == 1 ) {
		// faire chargement des données
		$sql = "SELECT * FROM ".TABLE_USERS." WHERE user_email LIKE '$mail' LIMIT 1" ;
		$results = UPDO::getInstance()->query( $sql ) ;
		foreach ( $results as $result ) {
			$new_user = new user($result['ID']) ;
			$new_user->setData($result) ;
		}
		// do activation KEY
		$key = sha1( SITE_NAME.time().rand(1,99) ) ;
		if (DEBUG) echo "The activation key is : $key<br/>" ;
		$new_user->setData(
			array('user_activation_key'=>$key)
		) ;
		if ( DEBUG ) print_r($new_user) ;
		if ( $new_user->save() ) {
			echo '<p class="success">'._('Vous allez recevoir un message pour réactiver votre inscription.').'</p>' ;

			// send email with activation link
			$message = _("Bonjour\nVous venez de demander à redéfinir votre mot de passe") ;
			$message .= " : ".SITE_NAME."\n" ;
			$message .= _("Votre login est") ;
			$message .= " : ".$new_user->getData('user_login')."\n" ;
			$message .= _('Afin de valider cette demande, veuillez vous rendre à l\'adresse suivante') ;
			$message .= "\n".HTTP_BASE."/page-inscription2?user_login=".$new_user->getData('user_login')."&key=$key\n" ;
			$message .= _('Cordialement, l\'équipe du site') ;

			// création du header du message
			$headers = "From: ".MAIL_ADMIN."\n" ;
			$headers.= "Reply-To: ".MAIL_ADMIN."\n" ;
			$headers.= "X-Mailer: PHP/".phpversion()."\n" ;
			$to = $new_user->getData('user_email') ;
			$subject = _('Votre inscription sur')." ".SITE_NAME ;
				//$headers.= "Cc: $mail_webmaster\n" ;
			if (DEBUG) echo "<textarea style='width:80%'>$headers\n$subject\n$message</textarea>" ;

			// send the mail

			// temp for work on static station
			echo "<script>alert(\"Votre lien : ".HTTP_BASE."/page-inscription2?user_login=".$new_user->getData('user_login')."&key=$key\");</script>" ;

			$message = utf8_decode($message) ;
			if ( mail($to,$subject,stripslashes($message),$headers) ){
				// Si le mail a bien été envoyé, message de confirmation
				echo '<p class="success">'._('Votre mail a bien été envoyé.')."</p>";
			}
			else {
				// sinon, message d'erreur.
				echo '<p class="error">'._('Votre mail n\'a pas pu être envoyé')."</p>";
			}
		}
		else {
			echo '<p class="error">'._('Echec lors de votre demande de mot de passe perdu.').'</p>' ;
		}
		//return true ;
	}
	elseif ( $count == 0 ) {
		echo ('Pas de résultat') ;
		//return false ;
	}
	else {
		echo ('Problème') ;
		//return false ;
	}
}
else {
	?>
	<h2><?php echo _('Mot de passe oublié') ; ?></h2>

	<form id="formulaire" name="formulaire" method="post" action="page-forgotpassword" >
		<fieldset>
		<legend style="margin-bottom: 5px;"><?php echo _('Rappel de mot de passe') ; ?></legend>

		<label for="user_email"><?php echo _('Adresse de courriel fournie lors de votre inscription') ; ?></label>
		<br />
		<input type="text" name="user_email" value="" />
		<br />

		<input type="submit" name="send" value="<?php echo _('Valider') ; ?>" />
		</fieldset>
	</form>
<?php
}
?>