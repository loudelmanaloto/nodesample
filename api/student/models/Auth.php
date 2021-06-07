<?php
    require_once('./phpmailer/mailer.php');
	class Auth {
		protected $gm;
		protected $pdo;
		

		public function __construct(\PDO $pdo) {
			$this->gm = new GlobalMethods($pdo);
			$this->pdo = $pdo;
		}
		
		########################################
		# 	USER AUTHORIZATION RELATED METHODS
		########################################
		protected function generateHeader() {
			$h=[
				"typ"=>"JWT",
				"alg"=>'HS256',
				"app"=>"GCECC POS",
				"dev"=>"Melner Balce, Loudel Manaloto, Owen Vargas"
			];
			return str_replace(['+','/','='],['-','_',''], base64_encode(json_encode($h)));
		}

		protected function generatePayload($uc, $ue, $ito) {
			$p = [   
				'uc'=>$uc,
				'ue'=>$ue,
				'ito'=>$ito,
				'iby'=>'Melner Balce',
				'ie'=>'melnerbalce@techmatesph.com',
				'idate'=>date_create()
			];
			return str_replace(['+','/','='],['-','_',''], base64_encode(json_encode($p)));
		}

		protected function generateToken($code, $course, $fullname) {
			$header = $this->generateHeader();
			$payload = $this->generatePayload($code, $course, $fullname);
			$signature = hash_hmac('sha256', "$header.$payload", "www.gordoncollege.edu.ph");
			return str_replace(['+','/','='],['-','_',''], base64_encode($signature));
		}

	
		public function authorized() {

		$headers = apache_request_headers();
	
        return true;
		}


		public function validate_token($token, $user){
		
		$sql = "SELECT * FROM `students_tbl` WHERE `s_studnum` = $user";	
		$dt = $this->gm->execute_query($sql, "Unauthorized User");
		
		if ($dt['data'][0]['s_token'] === $token){
			return true;
		}
		return false;		
		}

		########################################
		# 	USER AUTHENTICATION RELATED METHODS
		########################################
		protected function encrypt_password($pword) {
		$hashFormat="$2y$10$";
	    $saltLength=22;
	    $salt=$this->generate_salt($saltLength);
	    return crypt($pword,$hashFormat.$salt);
		}

		protected function generate_salt($len) {
			$urs=md5(uniqid(mt_rand(), true));
	    $b64String=base64_encode($urs);
	    $mb64String=str_replace('+','.', $b64String);
	    return substr($mb64String,0,$len);
		}

		public function pword_check($pword, $existingHash) {
			$hash=crypt($pword, $existingHash);
			if($hash===$existingHash){
				return true;
			}
			return false;
		}

		private function generatepass($size){
			$base = str_split('abcdefghijklmnopqrstuvwxyz'
                 .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                 .'0123456789!@#$%^&*()'); 
			shuffle($base); 
			$rand = '';
			foreach (array_rand($base, $size) as $k) $rand .= $base[$k];

			return $rand;
		}


		public function requestpassword($param){
			$un = $param->param1;
			$bd = $param->param2;
			$em = $param->param3;

			$sql = "SELECT * FROM students_tbl WHERE studnum_fld='$un' AND bday_fld ='$bd' AND email_fld='$em' AND isdel=0 LIMIT 1";
			$res = $this->gm->execute_query($sql, "Invalid studentid, birthday or email");

			if($res['code'] == 200) {
					$pw = $this->generatepass(8);
					$name = $res['data'][0]['fname_fld'].' '.$res['data'][0]['lname_fld'];
					$stat = sendmail($em, $pw, $name);
					if($stat['code']==200){
					$npw = $this->encrypt_password($pw);				
					$sql = "UPDATE accounts_tbl SET isreset_fld = '1', pword_fld='$npw' WHERE uname_fld='$un'";
					$this->gm->execute_query($sql, '');

					$code = 200;
					$remarks = "success";
					$message = "Success Request Password";
					$payload = null;	
				}			
			}
			else {
				$payload = null; 
				$remarks = "Failed"; 
				$message = $res['errmsg'];
			}
			
			return $this->gm->api_result($payload, $remarks, $message, $res['code']);
		}

		public function changepass($param){

			$un = $param->param1;
			$pw = $param->param2;
			$npw = $param->param3;
			$payload = "";
			$remarks = "";
			$message = "";
			$code = 403;

			$sql = "SELECT * FROM accounts_tbl WHERE uname_fld='$un' AND isdel=0 LIMIT 1";
			$res = $this->gm->execute_query($sql, "Incorrect username or password");	


			if($res['code'] == 200) {
				if($this->pword_check($pw, $res['data'][0]['pword_fld'])) {
					
					$npw = $this->encrypt_password($npw);
					$sql = "UPDATE accounts_tbl SET pword_fld='$npw' WHERE uname_fld=$un";
					$this->gm->execute_query($sql, '');
								
					$code = 200;
					$remarks = "success";
					$message = "Changed Password Successfully";
					$payload = null;
					
				}
				else{
					$payload = null; 
					$remarks = "failed"; 
					$message = "Incorrect password or failed to update";
				}
			}
			else {
				$payload = null; 
				$remarks = "failed"; 
				$message = $res['errmsg'];
			}
			
			return $this->gm->api_result($payload, $remarks, $message, $code);
		}
		



		public function student_login($param){
			// return $param;
			$un = $param->param1;
			$pw = $param->param2;

			$payload = "";
			$remarks = "";
			$message = "";
			$code = 403;

			$sql = "SELECT * FROM accounts_tbl INNER JOIN students_tbl ON accounts_tbl.uname_fld=students_tbl.studnum_fld WHERE accounts_tbl.uname_fld='$un' AND students_tbl.isdel=0 LIMIT 1";
			$res = $this->gm->execute_query($sql, "Incorrect username or password");

			if($res['code'] == 200) {
				if($this->pword_check($pw, $res['data'][0]['pword_fld'])) {
					$uc = $res['data'][0]['studnum_fld'];
					$ue = $res['data'][0]['deptcode_fld'];
					$fn = $res['data'][0]['fname_fld'].' '.$res['data'][0]['lname_fld'];
					$dir = $res['data'][0]['img_fld'];	
					$ro = $res['data'][0]['role_fld'];


					$tk = $this->generateToken($uc, $ue, $fn);

					$sql = "UPDATE accounts_tbl SET token_fld='$tk' WHERE uname_fld='$uc'";

					$this->gm->execute_query($sql, "failed to update");
					
						$code = 200;
						$remarks = "success";
						$message = "Logged in successfully";
						$payload = array("id"=>$uc, "fullname"=>$fn,"dept"=>$ue,"key"=>$tk, "img"=>$dir, "role"=>$ro);
					
				} else {
					$payload = null; 
					$remarks = "failed"; 
					$message = "Incorrect username or password";
				}
			}	else {
				$payload = null; 
				$remarks = "failed"; 
				$message = $res['errmsg'];
			}
			return $this->gm->api_result($payload, $remarks, $message, $code);
		}
	}
?>