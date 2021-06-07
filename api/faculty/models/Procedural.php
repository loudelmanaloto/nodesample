<?php
function errMsg($errcode) {
	switch ($errcode) {
	case 400:
		$msg = "Bad Request. Please contact the systems administrator.";
		break;

	case 401:
		$msg = "Unauthorized user.";
		break;

	case 403:
		$msg = "Forbidden. Please contact the systems administrator.";
		break;

	default:
		$msg = "Request Not Found.";
		break;
	}

	http_response_code($errcode);
	return je(array("status" => array("remarks" => "failed", "message" => $msg), "prepared_by" => "Melner Balce, Gordon College-CCS", "timestamp" => date_create()));
}

function je($param) {
	return json_encode($param);
}

function jd($param) {
	return json_decode($param);
}

function be($param) {
	return base64_encode($param);
}
function bd($param) {
	return base64_decode($param);
}

function load($param) {
	return je(array("a" => str_replace(['+', '/', '='], ['-', '_', ''], be(je($param)))));
}

function response($param) {
	$string = je($param);
	$key = 'fsociety2020';
	$number = filter_var('AES-256-CBC', FILTER_SANITIZE_NUMBER_INT);
	$number = intval(abs($number));
	$ivLength = openssl_cipher_iv_length('AES-256-CBC');
	$iv = openssl_random_pseudo_bytes($ivLength);

	$salt = openssl_random_pseudo_bytes(256);
	$iterations = 999;
	$hashKey = hash_pbkdf2('sha512', $key, $salt, $iterations, ($number / 4));

	$encryptedString = openssl_encrypt($string, 'AES-256-CBC', hex2bin($hashKey), OPENSSL_RAW_DATA, $iv);

	$encryptedString = base64_encode($encryptedString);
	unset($hashKey);

	$output = ['ciphertext' => $encryptedString, 'iv' => bin2hex($iv), 'salt' => bin2hex($salt), 'iterations' => $iterations];
	unset($encryptedString, $iterations, $iv, $ivLength, $salt);

	return je(array("a" => base64_encode(json_encode($output))));
	// return je($param);
}
?>