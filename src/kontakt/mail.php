<?php

// Only process POST reqeusts.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	/*
	 *  CONFIGURATION
	 */

	// an email address that will be in the From field of the email.
	$from = 'Formularz kontaktowy <kontakt@annawysocka.pl>';

	// an email address that will receive the email with the output of the form
	$sendTo = 'Anna Wysocka <kontakt@annawysocka.pl>';

	// subject of the email
	$subject = 'Nowa wiadomość z formularza kontaktowego annawysocka.pl';

	// form field names and their translations.
	// array variable name => Text to appear in the email
	$fields = array('name' => 'Imię i nazwisko', 'email' => 'Email', 'message' => 'Wiadomość'); 

	// message that will be displayed when everything is OK :)
	$okMessage = 'Wiadomość została wysłana. Odezwę się do Ciebie najszybciej jak się da!';

	// If something goes wrong, we will display this message.
	$errorMessage = 'W trakcie wysyłania wiadomości wystąpił błąd. Spróbuj ponownie później.';

	/*
	 *  SENDING MAIL
	 */

	// if you are not debugging and don't need error reporting, turn this off by error_reporting(0);
	error_reporting(E_ALL & ~E_NOTICE);

	$replyTo;
	$validationMessage;

	try {

	    if (count($_POST) == 0) {
			$validationMessage = 'Formularz jest pusty';
			throw new \Exception($validationMessage);
		}
	            
	    $emailText = "Nowa wiadomość:\n";
	    $emailText .= "=============================\n";

	    foreach ($_POST as $key => $value) {
	        // If the field exists in the $fields array, include it in the email 
	        if (isset($fields[$key])) {
	        	// trim value
	        	$trimmed = trim($value);

	        	// check if value is null
	        	if (empty($trimmed)) {
        			$validationMessage = "Pole $fields[$key] nie może być puste";
        			throw new \Exception($validationMessage);
        		}

	        	// email validation
	        	if(strcmp($key, 'email') == 0) {
	        		$trimmed = filter_var($trimmed, FILTER_SANITIZE_EMAIL);
	        		$replyTo = $trimmed;
	        		// check if value is null
	        		if (empty($trimmed)) {
	        			$validationMessage = "Pole $fields[$key] musi zawierać poprawny adres email";
	        			throw new \Exception($validationMessage);
	        		}
	        	}

	            $emailText .= "$fields[$key]: $trimmed\n";
	        }
	    }

	    $emailText .= "=============================\n";
		$emailText .= 'IP: '.$_SERVER['REMOTE_ADDR']."\n";
		$emailText .= 'Przeglądarka: '.$_SERVER['HTTP_USER_AGENT']."\n";
		$emailText .= "=============================\n";

	    // All the neccessary headers for the email.
	    $headers = array('Content-Type: text/plain; charset="UTF-8";',
	        'From: ' . $from,
	        'Reply-To: ' . $replyTo,
	        'Return-Path: ' . $from,
	    );
	    
	    // Send email
	    $success = mail($sendTo, $subject, $emailText, implode("\n", $headers));

	    if(!$success) throw new \Exception('Problem z wysłaniem wiadomości email');

	    $responseArray = array('type' => 'success', 'message' => $okMessage);
	    http_response_code(200);

	} catch (\Exception $e) {

		if (empty($validationMessage)) {
			$responseArray = array('type' => 'error', 'message' => $errorMessage);
	    	http_response_code(500);
		} else {
			$responseArray = array('type' => 'error', 'message' => $validationMessage);
	    	http_response_code(400);
		}

	}

	
} else {
	// Not a POST request, set a 403 (forbidden) response code.
    $responseArray = array('type' => 'error', 'message' => "Dozwolone tylko zapytania typu POST");
    http_response_code(400);
}

// if requested by AJAX request return JSON response
if ($_SERVER['HTTP_ACCEPT'] === 'application/json') {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($responseArray);
} else {
	header('Content-Type: text/plain; charset=utf-8');

    echo $responseArray['message'];
}
