<?php

require_once("class.phpmailer.php");

/**
* Function to create a mail object for futher use (uses phpMailer)
* @param string From e-mail address
* @param string From name
* @param string E-mail subject
* @param string Message body
* @return object Mail object
*/
function mosCreateMail( $from='', $fromname='', $subject, $body ) {
	global $mosConfig_absolute_path, $mosConfig_sendmail;
	global $mosConfig_smtpauth, $mosConfig_smtpuser;
	global $mosConfig_smtppass, $mosConfig_smtphost, $mosConfig_charset;
	global $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_mailer;

	$mail = new mosPHPMailer();

	//$mail->PluginDir = $mosConfig_absolute_path .'/includes/phpmailer/';
	$mail->SetLanguage( 'en', './language/' );
	if (isset($mosConfig_charset))
    	    $mail->CharSet 	= $mosConfig_charset;
	$mail->IsSMTP();
	$mail->From 	= $from ? $from : $mosConfig_mailfrom;
	$mail->FromName = $fromname ? $fromname : $mosConfig_fromname;
	//$mail->Mailer 	= $mosConfig_mailer;

	// Add smtp values if needed
	if ( $mosConfig_mailer == 'smtp' ) {
		$mail->SMTPAuth = $mosConfig_smtpauth;
		$mail->Username = $mosConfig_smtpuser;
		$mail->Password = $mosConfig_smtppass;
		$mail->Host 	= $mosConfig_smtphost;
	} else

	// Set sendmail path
	if ( $mosConfig_mailer == 'sendmail' ) {
		if (isset($mosConfig_sendmail))
			$mail->Sendmail = $mosConfig_sendmail;
	} // if

	$mail->Subject 	= $subject;
	$mail->Body 	= $body;

	return $mail;
}

/**
* Mail function (uses phpMailer)
* @param string From e-mail address
* @param string From name
* @param string/array Recipient e-mail address(es)
* @param string E-mail subject
* @param string Message body
* @param boolean false = plain text, true = HTML
* @param string/array CC e-mail address(es)
* @param string/array BCC e-mail address(es)
* @param string/array Attachment file name(s)
*/
function mosMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL ) {
	global $mosConfig_debug;
	$mail = mosCreateMail( $from, $fromname, $subject, $body );

	// activate HTML formatted emails
	if ( $mode ) {
		$mail->IsHTML(true);
	}

	if( is_array($recipient) ) {
		foreach ($recipient as $to) {
			if ($to!="") $mail->AddAddress($to);
		}
	} else {
		$mail->AddAddress($recipient);
	}
	if (isset($cc)) {
	    if( is_array($cc) )
	        foreach ($cc as $to) $mail->AddCC($to);
	    else
	        $mail->AddCC($cc);
	}
	if (isset($bcc)) {
	    if( is_array($bcc) )
	        foreach ($bcc as $to) $mail->AddCC($to);
	    else
	        $mail->AddCC($bcc);
	}
    if ($attachment) {
        if ( is_array($attachment) )
            foreach ($attachment as $fname) $mail->AddAttachment($fname);
        else
            $mail->AddAttachment($attachment);
    } // if
	$mailssend = $mail->Send();

	if( $mosConfig_debug ) {
	//	$mosDebug->message( "Mails send: $mailssend");
	}
	if( $mail->error_count > 0 ) {
#		echo "<P>Mailer Error: " . $mail->ErrorInfo;
		addmsg("Error:".$mail->ErrorInfo,"red");
	}
	return $mailssend;
} // mosMail
	
?>

