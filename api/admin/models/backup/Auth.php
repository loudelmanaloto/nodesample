<?php
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
				"dev"=>"GC Developers"
			];
			return str_replace(['+','/','='],['-','_',''], base64_encode(json_encode($h)));
		}

		protected function generatePayload($uc, $ue, $ito) {
			$p = [   
				'uc'=>$uc,
				'ue'=>$ue,
				'ito'=>$ito,
				'iby'=>'GC Developers',
				'ie'=>'gcdevelopers@gordoncollege.edu.ph',
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
			return true;
			// $hdrs = apache_request_headers();
			// $authHeader = '';
			// $authUser = '';
			// foreach ($hdrs as $header => $value) {
			// 	// echo "$header: $value";
		 //    if($header == "Authorization") {
		 //    	$authHeader = $value;
		 //    }
		 //    if($header == "X-Auth-User") { 
		 //    	$authUser = $value;
		 //    }
			// }
			// $sql = "SELECT token_fld FROM student_tbl WHERE studnum_fld='$authUser'";
			// $res = $this->gm->execute_query($sql, "Incorrect username or password");
			// if ($res['code'] == 200) {
			// 	if($res['data'][0]['token_fld']==$authHeader){
			// 		return true;
			// 	} else {
			// 		return false;
			// 	}
			// } 
			// return false;
		}

		########################################
		# 	USER AUTHENTICATION RELATED METHODS
		########################################
		public function encrypt_password($pword) {
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

		public function student_login($param) {
			$un = $param->param1;
			$pw = $param->param2;
			$payload = "";
			$remarks = "";
			$message = "";
			$code = 403;

			$sql = "SELECT students_tbl.studnum_fld, students_tbl.fname_fld, students_tbl.lname_fld, students_tbl.emailadd_fld, accounts_tbl.pword_fld FROM students_tbl INNER JOIN accounts_tbl USING(studnum_fld) WHERE studnum_fld = '$un' AND accounts_tbl.isdeleted_fld=0 LIMIT 1";
			$res = $this->gm->execute_query($sql, "Incorrect username or password");

			if($res['code'] == 200) {
				if($this->pword_check($pw, $res['data'][0]['pword_fld'])) {
					$uc = $res['data'][0]['studnum_fld'];
					$ue = $res['data'][0]['emailadd_fld'];
					$fn = $res['data'][0]['fname_fld'].' '.$res['data'][0]['lname_fld'];
					$tk = $this->generateToken($uc, $ue, $fn);

					$sql = "UPDATE accounts_tbl SET token_fld='$tk' WHERE studnum_fld='$uc'";
					$this->gm->execute_query($sql, "");

					try {
						file_put_contents("students.log", date("Y-m-d H:i:s").', Logged In: '.$res['data'][0]['studnum_fld'].PHP_EOL, FILE_APPEND | LOCK_EX);
					} catch (Exception $e) {
						throw $e;
					}

					$code = 200;
					$remarks = "success";
					$message = "Logged in successfully";
					$payload = array("id"=>$uc, "fullname"=>$fn, "key"=>$tk, "role"=>"0");
				} else {
					$payload = null; 
					$remarks = "failed"; 
					$message = "Incorrect username or password";
					try {
						file_put_contents("students.log", date("Y-m-d H:i:s").', Attempted to Log In: '.$un.PHP_EOL, FILE_APPEND | LOCK_EX);
					} catch (Exception $e) {
						throw $e;
					}
				}
			}	else {
				$payload = null; 
				$remarks = "failed"; 
				$message = $res['errmsg'];
				try {
					file_put_contents("students.log", date("Y-m-d H:i:s").', Attempted to Log In: '.$un.PHP_EOL, FILE_APPEND | LOCK_EX);
				} catch (Exception $e) {
					throw $e;
				}
			}
			return $this->gm->api_result($payload, $remarks, $message, $code);
		}

		public function faculty_login($param) {
			$un = $param->param1;
			$pw = $param->param2;
			$module = $param->module;
			$payload = "";
			$remarks = "";
			$message = "";
			$code = 403;

			# /Edited August 1, 2020
			$sql = "SELECT empnum_fld, fname_fld, lname_fld, picture_fld, role_fld, dept_fld, program_fld, emailadd_fld, pword_fld FROM adminaccounts_tbl WHERE empnum_fld = '$un'";
			# ./Edited August 1, 2020

			# /Added August 1, 2020
			if ($module=="Admin Panel") {
				$sql .= " AND role_fld=0 OR role_fld=6 AND isdeleted_fld=0 LIMIT 1";
			} else if ($module=="Dean Panel") {
				$sql .= " AND (role_fld=1 OR role_fld=2) AND isdeleted_fld=0 LIMIT 1";
			} else if ($module=="Registrar Panel") {
				$sql .= " AND role_fld=3 AND isdeleted_fld=0 LIMIT 1";
			} else if ($module=="Coordinator Panel") {
				$sql .= " AND (role_fld=4 OR role_fld=5) AND isdeleted_fld=0 LIMIT 1";
			}
			# ./ Added August 1, 2020

			$res = $this->gm->execute_query($sql, "Incorrect username or password");

			if($res['code'] == 200) {
				if($this->pword_check($pw, $res['data'][0]['pword_fld'])) {
					$uc = $res['data'][0]['empnum_fld'];
					$ue = $res['data'][0]['emailadd_fld'];
					$fn = $res['data'][0]['fname_fld'].' '.$res['data'][0]['lname_fld'];
					$ur = $res['data'][0]['role_fld'];
					$dept = $res['data'][0]['dept_fld'];
					$picture = $res['data'][0]['picture_fld'];
					$program = $res['data'][0]['program_fld'];
					$tk = $this->generateToken($uc, $ue, $fn);

					$sql = "UPDATE adminaccounts_tbl SET token_fld='$tk' WHERE emailadd_fld='$ue'";
					$this->gm->execute_query($sql, "");

					$code = 200;
					$remarks = "success";
					$message = "Logged in successfully";
					$payload = array("id"=>$uc, "fullname"=>$fn, "key"=>$tk, "role"=>$ur, "emailadd"=>$ue, "dept"=>$dept, "program"=>$program, "picture"=>$picture);

					file_put_contents(
						"admin.log",
						date("Y-m-d H:i:s").','.$un.','.$fn.','.$dept.','.$program.', Logged In to '.$module.PHP_EOL, FILE_APPEND | LOCK_EX);
				} else {
					$payload = null; 
					$remarks = "failed"; 
					$message = "Incorrect username or password";

					file_put_contents(
						"admin.log",
						date("Y-m-d H:i:s").','.$un.',,,, Attempted to Log In to '.$module.PHP_EOL, FILE_APPEND | LOCK_EX);
				}
			}	else {
				$payload = null; 
				$remarks = "failed"; 
				$message = $res['errmsg'];
				file_put_contents(
					"admin.log",
					date("Y-m-d H:i:s").','.$un.',,,, Attempted to Log In to '.$module.PHP_EOL, FILE_APPEND | LOCK_EX);
			}
			return $this->gm->api_result($payload, $remarks, $message, $code);
		}

		# Added on August 3, 2020
		public function change_password($param) {
			$empnum_fld = $param->param1;
			$oldpass = $param->param2;
			$newpass = $param->param3;
			$mode = $param->mode;
			$name = $param->name;
			$prog = $param->prog;
			$dept = $param->dept;

			$code = 401;
			$remarks = "failed";
			$message = "Failed to change password";
			$payload = null;

			$sql = "SELECT * FROM adminaccounts_tbl WHERE empnum_fld = '$empnum_fld' AND isdeleted_fld=0 LIMIT 1";
			$res = $this->gm->execute_query($sql, "Incorrect username or password");

			if ($res['code']==200) {
				if ($mode==0) {
					if ($this->pword_check($oldpass, $res['data'][0]['pword_fld'])) {
						# Usermode here - change password
						$newpass = $this->encrypt_password($newpass);
						$sql = "UPDATE adminaccounts_tbl SET pword_fld='$newpass' WHERE empnum_fld='$empnum_fld'";
						$res = $this->gm->execute_query($sql, '');

						if ($res['code']==200) { 
							$code = 200;
							$remarks = "success";
							$message = "Password changed successfully";
							$payload = null;
						}
						file_put_contents(
							"admin.log",
							date("Y-m-d H:i:s").','.$empnum_fld.','.$name.','.$dept.','.$prog.', Changed Password'.PHP_EOL, FILE_APPEND | LOCK_EX);
					} else {
						$code = 401;
						$payload = null; 
						$remarks = "failed"; 
						$message = "Incorrect password or failed to update";
						file_put_contents(
							"admin.log",
							date("Y-m-d H:i:s").','.$empnum_fld.','.$name.','.$dept.','.$prog.', Attempt to Change Password'.PHP_EOL, FILE_APPEND | LOCK_EX);
					}
				} else {
					# Admin mode here - Reset password
					$newpass = $this->encrypt_password($newpass);
					$sql = "UPDATE adminaccounts_tbl SET pword_fld='$newpass' WHERE empnum_fld=$empnum_fld";
					$this->gm->execute_query($sql, '');
					if ($res['code']==200) { 
						$code = 200;
						$remarks = "success";
						$message = "Password changed successfully";
						$payload = null;
						file_put_contents(
							"admin.log",
							date("Y-m-d H:i:s").','.$oldpass.','.$name.','.$dept.','.$prog.', Changed Password of '.$empnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
					}
				}
			} else {
				$code = 401;
				$payload = null; 
				$remarks = "failed"; 
				$message = "Incorrect username or password";
				file_put_contents(
					"admin.log",
					date("Y-m-d H:i:s").','.$empnum_fld.','.$name.','.$dept.','.$prog.', Attempt to Change Password'.PHP_EOL, FILE_APPEND | LOCK_EX);
			}

			return $this->gm->api_result($payload, $remarks, $message, $code);
		}

		public function studentPasswordChange($param) {
			$studnum_fld = $param->param1;
			$oldpass = $param->param2;
			$newpass = $param->param3;
			$mode = $param->mode;
			$name = $param->name;
			$prog = $param->prog;
			$dept = $param->dept;

			$code = 401;
			$remarks = "failed";
			$message = "Failed to change password";
			$payload = null;

			$sql = "SELECT * FROM students_tbl INNER JOIN accounts_tbl USING(studnum_fld) WHERE studnum_fld='$studnum_fld' and isdeleted_fld=0 LIMIT 1";
			$res = $this->gm->execute_query($sql, "Incorrect username or password");

			if($res['code']==200) {
				
				if($mode==0){
					#Student mode here - change password 
					if ($this->pword_check($oldpass, $res['data'][0]['pword_fld'])) {
						
						$newpass = $this->encrypt_password($newpass);

						$res = $this->gm->update('accounts_tbl', array("pword_fld"=>$newpass), "studnum_fld='$studnum_fld'");

						if ($res['code']==200) { 
							$code = 200;
							$remarks = "success";
							$message = "Password changed successfully";
							$payload = null;
							file_put_contents("students.log", date("Y-m-d H:i:s").', Password changed: '.$studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
						}
					}
				}	else {
					# Admin mode here - Reset password
					$newpass = $this->encrypt_password($newpass);
					$res = $this->gm->update('accounts_tbl', array("pword_fld"=>$newpass), "studnum_fld='$studnum_fld'");

					if ($res['code']==200) { 
						$code = 200;
						$remarks = "success";
						$message = "Password changed successfully";
						$payload = null;
						file_put_contents(
							"admin.log",
							date("Y-m-d H:i:s").','.$oldpass.','.$name.','.$dept.','.$prog.', Reset password of '.$studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
					}
				}
			} else {
				$code = 401;
				$payload = null; 
				$remarks = "failed"; 
				$message = "Incorrect username or password";
				file_put_contents("students.log", date("Y-m-d H:i:s").', Attempted to change password: '.$studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
			}

			return $this->gm->api_result($payload, $remarks, $message, $code);
		}
	}
?>