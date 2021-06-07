<?php 

header("Access-Control-Allow-Origin: *");

header("Content-Type: application/json; charset=utf-8");


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require_once('./phpmailer/vendor/autoload.php');

$mail = new PHPMailer(true);

// $d = json_decode(base64_decode(file_get_contents("php://input")));
// $d = json_decode(file_get_contents("php://input"));


 function sendmail($recipient, $password, $name){
	try {

		$mail = new PHPMailer(true);
		$mail->isSMTP();                                            // Send using SMTP
		$mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
		  // $mail->SMTPDebug  = 2;
		$mail->SMTPAuth   = true;                               // Enable SMTP authentication
		$mail->Username   = 'lms_developers@gordoncollege.edu.ph';                     // SMTP username
		$mail->Password   = 'Aa1234567';                               // SMTP password
		$mail->SMTPSecure = 'tls';   
		$mail->SMTPAutoTLS = false;
		//$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
		$mail->Port       = 587;          
		// $mail->setFrom('lms_developers@gordoncollege.edu.ph', 'Gordon College');
		// $mail->isHTML(true);     
	
		//Recipients
		$mail->setFrom('lms_developers@gordoncollege.edu.ph', 'Gordon College');
		$mail->addAddress($recipient);     // Add a recipient

		// Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'Request Password';
		$mail->Body    = '
						 <h3 style="text-align: center">Hello ,'.$name.'</h3>
        				 <p style="text-align: center">You password here!</p>
        				 <h1 style="text-align: center">'.$password.'</h1>';

		

		if($mail->send()) {
			
			$code = 200;
			// http_response_code(200);

			$msg = array(
				"status" => [
					"remarks" => "success",
					"message" => "Email has been sent."
				],
				"prepared_by" => "Melner Balce, Gordon College-CCS",
				"timestamp"=>date_create()
			);
		} else {
			
			// http_response_code(500);
			$code = 500;

			$msg = array(
				"status" => [
					"remarks" => "failed",
					"message" => "Sending failed."
				],
				"prepared_by" => "Melner Balce, Gordon College-CCS",
				"timestamp"=>date_create()
			);
		}

} catch (Exception $e) {

	// http_response_code(500);
	$code = 500;

	$msg = array(
		"status" => [
			"remarks" => "failed",
			"message" => "Sending email failed. Error: $mail->ErrorInfo"
		],
		"prepared_by" => "Melner Balce, Gordon College-CCS",
		"timestamp"=>date_create()
	);
}

	return array("code"=>$code, "phpmailmsg"=>$msg);

}



?>