<?php
	class Post {
		protected $gm;
		protected $pdo;
		protected $get;
		protected $auth;

		public function __construct(\PDO $pdo) {
			$this->pdo = $pdo;
			$this->gm = new GlobalMethods($pdo);
			$this->get = new Get($pdo);
			$this->auth = new Auth($pdo);
		}

		public function update_studentinfo($d){
			$student = $d->ans->student;
			$account = $d->ans->account;

			$res = $this->gm->update('students_tbl', $student, "studnum_fld='".$d->ans->studnum_fld."'");
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				try {
					file_put_contents("students.log", date("Y-m-d H:i:s").', Failed to Update profile: '.$d->ans->studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
				} catch (Exception $e) {
					throw $e;
				}
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}

			$res = $this->gm->update('accounts_tbl', $account, "studnum_fld='".$d->ans->studnum_fld."'");
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				try {
					file_put_contents("students.log", date("Y-m-d H:i:s").', Failed to Update account: '.$d->ans->studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
				} catch (Exception $e) {
					throw $e;
				}
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}
			
			$sql = $this->pdo->prepare("UPDATE accounts_tbl SET accept_fld=1 WHERE studnum_fld=?");
			$sql->execute([$d->ans->studnum_fld]);
			$sql = null;
			
			try {
				file_put_contents("students.log", date("Y-m-d H:i:s").', Updated profile: '.$d->ans->studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
			} catch (Exception $e) {
				throw $e;
			}
			
			return $this->gm->api_result(null, "success", "Successfully save responses",200);
		}

		public function update_accounts($d) {
			$account = $d;

			$res = $this->gm->update('accounts_tbl', $account, "studnum_fld = '$d->studnum_fld'");
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				try {
					file_put_contents("students.log", date("Y-m-d H:i:s").', Failed to Update account: '.$d->studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
				} catch (Exception $e) {
					throw $e;
				}
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}
			try {
				file_put_contents("students.log", date("Y-m-d H:i:s").', Updated account: '.$d->studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
			} catch (Exception $e) {
				throw $e;
			}

			return $this->gm->api_result(null, "success", "Successfully save responses",200);
		}

		public function unschedule($ay, $sem, $studnum, $d) {
			$subjects=[];
			$sql = "SELECT * FROM enrolledsubj_tbl WHERE studnum_fld='$studnum' AND ay_fld='$ay' AND sem_fld=$sem";
			$res = $this->gm->execute_query($sql, "No records found");
			// print_r($res['data']);
			
			if ($res['code'] == 200) {
				$subjects=$res['data'];
				$sql = "SELECT block_fld FROM accounts_tbl WHERE studnum_fld='$studnum' AND acadyear_fld='$ay' AND sem_fld=$sem";
				$res = $this->gm->execute_query($sql, "No records found");
				$block = $res['data'][0]['block_fld'];
				$sql = "DELETE FROM enrolledsubj_tbl WHERE studnum_fld=? AND ay_fld=? AND sem_fld=?";
				$sql = $this->pdo->prepare($sql);
				$sql->execute([$studnum, $ay, $sem]);
				$sql = null;

				$res = $this->gm->update('accounts_tbl', array("block_fld"=>null, "isenrolled_fld"=>0, "enrolleddate_fld"=>date("Y-m-d H:i:s")), "studnum_fld = '$studnum'");
				if ($res['code'] != 200) {
					$payload = null;
					$remarks = "failed";
					$message = $res['errmsg'];
					return $this->gm->api_result($payload, $remarks, $message, $res['code']);
				}

				foreach ($subjects as $value) {
					$sql = $this->pdo->prepare("UPDATE classes_tbl SET slots_fld=slots_fld+1 WHERE classcode_fld=? AND ay_fld='$ay' AND sem_fld=$sem");
					$sql->execute([$value['classcode_fld']]);
					$sql = null;
				}

				$sql = $this->pdo->prepare("UPDATE slots_tbl SET taken_fld=taken_fld-1 WHERE block_fld=? AND ay_fld='$ay' AND sem_fld=$sem");
				$sql->execute([$block]);
				$sql = null;
			}

			file_put_contents(
				"admin.log",
					date("Y-m-d H:i:s").','
					.$d->ans->userid.','
					.$d->ans->userfullname.','
					.$d->ans->userdept.','
					.$d->ans->userprog
					.', Schedule removed: '.$studnum.' - '.$d->ans->studfullname
					.PHP_EOL, FILE_APPEND | LOCK_EX);

			return $this->gm->api_result(null, "success", "Record removed", 200);
		}

		public function process_classes($d) {
			$studnum = $d->subjects[0]->studnum_fld;
			$ay = $d->subjects[0]->ay_fld;
			$sem = $d->subjects[0]->sem_fld;
			$block = $d->block_fld;
			$subjects = $d->subjects;
			
			$sql = "SELECT * FROM enrolledsubj_tbl WHERE studnum_fld='$studnum' AND ay_fld='$ay' AND sem_fld=$sem";
			$res = $this->gm->execute_query($sql, "No records found");
			if ($res['code'] == 200) {
				$sql = "DELETE FROM enrolledsubj_tbl WHERE studnum_fld=? AND ay_fld=? AND sem_fld=?";
				$sql = $this->pdo->prepare($sql);
				$sql->execute([$studnum, $ay, $sem]);

				foreach ($subjects as $value) {
					$sql = $this->pdo->prepare("UPDATE classes_tbl SET slots_fld=slots_fld+1 WHERE classcode_fld=? AND ay_fld='$ay' AND sem_fld=$sem");
					$sql->execute([$value->classcode_fld]);
					$sql = null;
				}

				$sql = $this->pdo->prepare("UPDATE slots_tbl SET taken_fld=taken_fld-1 WHERE block_fld=? AND ay_fld='$ay' AND sem_fld=$sem");
				$sql->execute([$block]);
				$sql = null;
			}

			foreach ($subjects as $value) {
				$res = $this->gm->insert('enrolledsubj_tbl', $value);
				if ($res['code'] != 200) {
					$payload = null;
					$remarks = "failed";
					$message = $res['errmsg'];
					return $this->gm->api_result($payload, $remarks, $message, $res['code']);
				}

				$res = $this->gm->update('accounts_tbl', array("block_fld"=>$block, "isenrolled_fld"=>1, "enrolleddate_fld"=>date("Y-m-d H:i:s")), "studnum_fld = '$studnum'");
				if ($res['code'] != 200) {
					$payload = null;
					$remarks = "failed";
					$message = $res['errmsg'];
					return $this->gm->api_result($payload, $remarks, $message, $res['code']);
				}

				$sql = $this->pdo->prepare("UPDATE classes_tbl SET slots_fld=slots_fld-1 WHERE classcode_fld=? AND ay_fld='$ay' AND sem_fld=$sem");
				$sql->execute([$value->classcode_fld]);
				$sql = null;
			}

			$sql = $this->pdo->prepare("UPDATE slots_tbl SET taken_fld=taken_fld+1 WHERE block_fld=? AND ay_fld='$ay' AND sem_fld=$sem");
			$sql->execute([$block]);
			$sql = null;

			file_put_contents(
				"admin.log",
					date("Y-m-d H:i:s").','
					.$d->userid.','
					.$d->userfullname.','
					.$d->userdept.','
					.$d->userprog
					.', Enrolled '.$studnum.' - '.$d->studfullname.' in '.$block
					.PHP_EOL, FILE_APPEND | LOCK_EX);
			return $this->gm->api_result(null, "success", "Successfully save responses",200);
		}

		public function process_upload($studnum, $filename, $filetype) {
			$folder="";
			if ($filetype==1) {
				$folder="Prospectus";
			} else if ($filetype==2) {
				$folder="Grades";
			} else if ($filetype==3) {
				$folder="COR";
			} else if ($filetype==4) {
				$folder="Profile";
			} else if ($filetype==5) {
				$folder="ResidencyCert";
			} else if ($filetype==6) {
				$folder="HonorableDismissal";
			} else if ($filetype==7) {
				$folder="GoodMoralCert";
			} else if ($filetype==8) {
				if ($filename=="TranscriptOfRecords") {
					$folder="TranscriptOfRecords";
				} else if ($filename=="Form138") {
					$folder="Form138";
				}
			} else if ($filetype==9) {
				$folder="IDCard";
			} else if ($filetype==10) {
				$folder="HepaScreening";
			} else if ($filetype==11) {
				$folder="BirthCert";
			}
			
			$fileArray = array();
			$success = 0;
			$error = 0;
			$count = 1;

			foreach($_FILES['file']['tmp_name'] as $key => $tmpname){

				$fileName = $_FILES['file']["name"][$key];
				$fileTmp = $_FILES['file']["tmp_name"][$key];
				$fileExtension = pathinfo($fileName,PATHINFO_EXTENSION);
				
				$target_path = "uploads/$studnum/$folder/";
				if (!is_dir("../".$target_path)) {
					mkdir("../".$target_path, 0755, true);
				}
			
				$target_path = $target_path . $filename . $count . '.' .$fileExtension;
				$data=""; $field="";
				if (move_uploaded_file($fileTmp, "../".$target_path)) {
					array_push($fileArray, $target_path);
					$success++;
				} else {
					$error++;
				}
				$count++;
			}

			if($error == 0){
				$files = implode(",", $fileArray);
				if ($filetype==1) {
					$field="imgprospectus_fld";
				} else if ($filetype==2) {
					$field="imggrades_fld";
				} else if ($filetype==3) {
					$field="imgcor_fld";
				} else if ($filetype==5) {
					$field="imgresidencycert_fld";
				} else if ($filetype==6) {
					$field="imghonordismiss_fld";
				} else if ($filetype==7) {
					$field="imggoodmoral_fld";
				} else if ($filetype==8) {
					$field="imgf138ytor_fld";
				} else if ($filetype==9) {
					$field="imgidcard_fld";
				} else if ($filetype==10) {
					$field="imghepascreen_fld";
				} else if ($filetype==11) {
					$field="imgbirthcert_fld";
				}
				
				try {
					if ($filetype==4) {
						$sql = "UPDATE students_tbl SET profilepic_fld=? WHERE studnum_fld=?";
					}else{
						$sql = "UPDATE accounts_tbl SET ".$field."=? WHERE studnum_fld=?";
					}
					$sql = $this->pdo->prepare($sql);
					$sql->execute([$files, $studnum]);
					$code = 200;
					$payload = null;
					$remarks = "success";
					$message = "Successfully uploaded image";
					
					file_put_contents("students.log", date("Y-m-d H:i:s").', Uploaded '.$folder.' image: '.$studnum.PHP_EOL, FILE_APPEND | LOCK_EX);
					
					return $this->gm->api_result($payload, $remarks, $message, $code);
					
				} catch (\PDOException $e) {
					$code = 403;
					$payload=null;
					$remarks = "failed";
					$message = "There was an error uploading the file, please try again!";
					file_put_contents("students.log", date("Y-m-d H:i:s").', Failed to insert to database '.$folder.' image: '.$studnum.PHP_EOL, FILE_APPEND | LOCK_EX);
					return $this->gm->api_result($payload, $remarks, $message, $code);
				}
			} else {
				$message = "There was an error uploading the file, please try again!";
				$remarks = "failed";
				$code = 403;
				$payload=null;
				file_put_contents("students.log", date("Y-m-d H:i:s").', Failed to upload '.$folder.' image: '.$studnum.PHP_EOL, FILE_APPEND | LOCK_EX);
				return $this->gm->api_result($payload, $remarks, $message, $code);
			}
		}

		# /Added July 20, 2020
		public function credit_subjects($d){
			$studnum = $d->subjects[0]->studnum_fld;
			$subjects = $d->subjects;

			$sql = "SELECT * FROM acadrecords_tbl WHERE studnum_fld='$studnum' AND iscredited_fld=1";
			$res = $this->gm->execute_query($sql, "No records found");
			if ($res['code'] == 200){
				$sql = "DELETE FROM acadrecords_tbl WHERE studnum_fld=? AND iscredited_fld=1";
				$sql = $this->pdo->prepare($sql);
				$sql->execute([$studnum]);
			}

			foreach ($subjects as $value) {
				$res = $this->gm->insert('acadrecords_tbl', $value);
				if ($res['code'] != 200) {
					$payload = null;
					$remarks = "failed";
					$message = $res['errmsg'];
					file_put_contents(
						"admin.log",
						date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Failed to Credit Subjects: '.$studnum.'-'.$d->ans->studentfullname.PHP_EOL, FILE_APPEND | LOCK_EX);
					return $this->gm->api_result($payload, $remarks, $message, $res['code']);
				}
			}
			file_put_contents(
				"admin.log",
				date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Credited Subjects: '.$studnum.'-'.$d->ans->studentfullname.PHP_EOL, FILE_APPEND | LOCK_EX);
			return $this->gm->api_result(null, "success", "Successfully save responses",200);
		}
		# ./Added July 20, 2020

		# /Added July 25, 2020
		public function confirm_enrollment($id, $year, $sem, $type, $d){

			if ($type!=2&&strlen($id)<9) {
				$target_path = "../uploads/";
				$sql = "SELECT * FROM idnumber_tbl WHERE year_fld='$year' AND sem_fld=$sem";
				$res = $this->gm->execute_query($sql, "No record found");
				if ($res['code']!=200) {
					return $this->gm->api_result(null, "Failed", "No Pattern Record found", $res['code']);
				}
				$prefix_studnum = $res['data'][0]['year_fld'].$res['data'][0]['sem_fld'];  // concatenate current year and semester
				$counter_studnum = $res['data'][0]['counter_fld'];
				$old_studnum_fld = $id;
				$new_studnum_fld = $prefix_studnum.str_pad($counter_studnum, 4, 0, STR_PAD_LEFT);
				$old_directory = $target_path.$old_studnum_fld;
				$new_directory = $target_path.$new_studnum_fld;

				$res = $this->gm->update('accounts_tbl', array("studnum_fld"=>$new_studnum_fld, "isenrolled_fld"=>2, "enrolleddate_fld"=>date("Y-m-d H:i:s")), "studnum_fld = '$old_studnum_fld'");
				if ($res['code']==200) {
					
					$sql = $this->pdo->prepare("UPDATE idnumber_tbl SET counter_fld=counter_fld+1 WHERE year_fld=? AND sem_fld=?");
					$sql->execute([$year,$sem]);
					$sql = null;

					$res = $this->gm->update('students_tbl', array("studnum_fld"=>$new_studnum_fld, "emailadd_fld"=>$new_studnum_fld.'@gordoncollege.edu.ph'), "studnum_fld = '$old_studnum_fld'");
					if ($res['code']==200) {
						$res = $this->gm->update('enrolledsubj_tbl', array("studnum_fld"=>$new_studnum_fld), "studnum_fld='$old_studnum_fld'");
						if ($res['code']==200) {
							# /Added August 3, 2020
							$res = $this->gm->update('acadrecords_tbl', array("studnum_fld"=>$new_studnum_fld), "studnum_fld='$old_studnum_fld'");
							# ./Added August 3, 2020
							if(file_exists($old_directory)) {
								if(rename($old_directory, $new_directory)){
									$sql = "SELECT students_tbl.*, accounts_tbl.studtype_fld, accounts_tbl.isenrolled_fld, accounts_tbl.enrolleddate_fld, accounts_tbl.acadyear_fld, accounts_tbl.sem_fld, accounts_tbl.studyrlevel_fld, accounts_tbl.isenlisted_fld, accounts_tbl.enlistdate_fld, accounts_tbl.enlistreason_fld, accounts_tbl.learningtype_fld, accounts_tbl.imgcor_fld, accounts_tbl.imgprospectus_fld, accounts_tbl.imggrades_fld, accounts_tbl.isregular_fld, accounts_tbl.imgresidencycert_fld, accounts_tbl.imghonordismiss_fld, accounts_tbl.imggoodmoral_fld, accounts_tbl.imghepascreen_fld, accounts_tbl.imgidcard_fld, accounts_tbl.imgf138ytor_fld, accounts_tbl.imgbirthcert_fld, accounts_tbl.block_fld FROM students_tbl INNER JOIN accounts_tbl USING(studnum_fld) WHERE students_tbl.studnum_fld='$new_studnum_fld'";
									$res = $this->gm->execute_query($sql, "No records found");
									if ($res['code'] == 200) {
										$fail = 0;
										$studentdetails = $res['data'][0];
										$images = ['profilepic_fld', 'imgcor_fld', 'imgprospectus_fld', 'imggrades_fld', 'imgresidencycert_fld', 'imghonordismiss_fld', 'imggoodmoral_fld', 'imghepascreen_fld', 'imgidcard_fld', 'imgf138ytor_fld', 'imgbirthcert_fld'];
										
										for ($i=0; $i < count($images); $i++) {
											if ($studentdetails[$images[$i]]!="") {
												$path = str_replace($id,$new_studnum_fld,$studentdetails[$images[$i]]);
												if ($i==0) {
													$res = $this->gm->update('students_tbl', array($images[$i]=>$path), "studnum_fld = '$new_studnum_fld'");
												} else {
													$res = $this->gm->update('accounts_tbl', array($images[$i]=>$path), "studnum_fld = '$new_studnum_fld'");
												}
												if ($res['code']!=200) {
													$fail++;
												}
											}
										}
										
										if ($fail!=0) {
											file_put_contents(
												"admin.log",
												date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Failed to Update directory in database: '.$old_studnum_fld.' to '.$new_studnum_fld.' '.$studentdetails['fname_fld'].' '.$studentdetails['mname_fld'].' '.$studentdetails['lname_fld'].' '.$studentdetails['extname_fld'].PHP_EOL, FILE_APPEND | LOCK_EX);
											return $this->gm->api_result(null, "failed", "unable to save directory to database",400);
										}

										$code = 200;
										$payload = array("student"=>$studentdetails);
										$remarks = "success";
										$message = "Successfully updated data";
									} else {
										try {
											file_put_contents(
												"admin.log",
												date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Tried to get data of: '.$old_studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
										} catch (Exception $e) {
											throw $e;
										}
										return $this->gm->api_result(null, "failed", "Unable to get data", $res['code']);
									}
									try {
										file_put_contents(
											"admin.log",
											date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Confirmed Enrollment: '.$old_studnum_fld.' to '.$new_studnum_fld.' '.$studentdetails['fname_fld'].' '.$studentdetails['mname_fld'].' '.$studentdetails['lname_fld'].' '.$studentdetails['extname_fld'].PHP_EOL, FILE_APPEND | LOCK_EX);
									} catch (Exception $e) {
										throw $e;
									}
									return $this->gm->api_result($payload, $remarks, $message, $code);
								} else {
									try {
										file_put_contents(
											"admin.log",
											date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Directory update to database failed: '.$old_studnum_fld.' to '.$new_studnum_fld.' '.$studentdetails['fname_fld'].' '.$studentdetails['mname_fld'].' '.$studentdetails['lname_fld'].' '.$studentdetails['extname_fld'].PHP_EOL, FILE_APPEND | LOCK_EX);
									} catch (Exception $e) {
										throw $e;
									}
									return $this->gm->api_result(null, "failed", "Unable to update directory",400);
								}
							} else {
								try {
									file_put_contents(
										"admin.log",
										date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userprog.', Updating nonexistent directory: '.$old_studnum_fld.' to '.$new_studnum_fld.' '.$studentdetails['fname_fld'].' '.$studentdetails['mname_fld'].' '.$studentdetails['lname_fld'].' '.$studentdetails['extname_fld'].PHP_EOL, FILE_APPEND | LOCK_EX);
								} catch (Exception $e) {
									throw $e;
								}
								return $this->gm->api_result(null, "failed", "Directory does not exists",400);
							}
						} else {
							try {
								file_put_contents(
									"admin.log",
									date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Enrollment data update failed: '.$old_studnum_fld.' to '.$new_studnum_fld.' '.$studentdetails['fname_fld'].' '.$studentdetails['mname_fld'].' '.$studentdetails['lname_fld'].' '.$studentdetails['extname_fld'].PHP_EOL, FILE_APPEND | LOCK_EX);
							} catch (Exception $e) {
								throw $e;
							}
							return $this->gm->api_result(null, "failed", "Unable to update enroll data", $res['code']);
						}
					} else {
						try {
							file_put_contents(
								"admin.log",
								date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Student data update failed: '.$old_studnum_fld.' to '.$new_studnum_fld.' '.$studentdetails['fname_fld'].' '.$studentdetails['mname_fld'].' '.$studentdetails['lname_fld'].' '.$studentdetails['extname_fld'].PHP_EOL, FILE_APPEND | LOCK_EX);
						} catch (Exception $e) {
							throw $e;
						}
						return $this->gm->api_result(null, "failed", "Unable to update student data", $res['code']);
					}
				} else {
					try {
						file_put_contents(
							"admin.log",
							date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Account data update failed: '.$old_studnum_fld.' to '.$new_studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
					} catch (Exception $e) {
						throw $e;
					}
					return $this->gm->api_result(null, "failed", "Unable to update account data", $res['code']);
				}
			} else {
				$res = $this->gm->update('accounts_tbl', array("isenrolled_fld"=>2, "enrolleddate_fld"=>date("Y-m-d H:i:s")), "studnum_fld = '$id'");
				if ($res['code']==200) {
					$sql = "SELECT students_tbl.*, accounts_tbl.studtype_fld, accounts_tbl.isenrolled_fld, accounts_tbl.enrolleddate_fld, accounts_tbl.acadyear_fld, accounts_tbl.sem_fld, accounts_tbl.studyrlevel_fld, accounts_tbl.isenlisted_fld, accounts_tbl.enlistdate_fld, accounts_tbl.enlistreason_fld, accounts_tbl.learningtype_fld, accounts_tbl.imgcor_fld, accounts_tbl.imgprospectus_fld, accounts_tbl.imggrades_fld, accounts_tbl.isregular_fld, accounts_tbl.imgresidencycert_fld, accounts_tbl.imghonordismiss_fld, accounts_tbl.imggoodmoral_fld, accounts_tbl.imghepascreen_fld, accounts_tbl.imgidcard_fld, accounts_tbl.imgf138ytor_fld, accounts_tbl.imgbirthcert_fld, accounts_tbl.block_fld FROM students_tbl INNER JOIN accounts_tbl USING(studnum_fld) WHERE students_tbl.studnum_fld='$id'";
					$res = $this->gm->execute_query($sql, "No records found");
					if ($res['code']==200) {
						$studentdetails = $res['data'][0];
						$code = 200;
						$payload = array("student"=>$studentdetails);
						$remarks = "success";
						$message = "Successfully updated data";
						try {
							file_put_contents(
								"admin.log",
								date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Confirmed Enrollment: '.$id.' '.$studentdetails['fname_fld'].' '.$studentdetails['mname_fld'].' '.$studentdetails['lname_fld'].' '.$studentdetails['extname_fld'].PHP_EOL, FILE_APPEND | LOCK_EX);
						} catch (Exception $e) {
							throw $e;
						}
						return $this->gm->api_result($payload, $remarks, $message, $code);
					} else {
						try {
							file_put_contents(
								"admin.log",
								date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Tried to get data of: '.$id.PHP_EOL, FILE_APPEND | LOCK_EX);
						} catch (Exception $e) {
							throw $e;
						}
						return $this->gm->api_result(null, "failed", "Unable to get data", $res['code']);
					}
				} else {
					try {
						file_put_contents(
							"admin.log",
							date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Account data update failed: '.$old_studnum_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
					} catch (Exception $e) {
						throw $e;
					}
					return $this->gm->api_result(null, "failed", "Unable to update account data", $res['code']);
				}
			}
		}

		public function admin_studentUpdate($d) {
			$student = $d->ans->student;
			$account = $d->ans->account;

			$res = $this->gm->update('students_tbl', $student, "studnum_fld='".$d->ans->studnum_fld."'");
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				try {
					file_put_contents(
						"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog.
						', Update profile failed: '.$d->ans->studnum_fld.' - '.$student->fname_fld.' '.$student->lname_fld
						.PHP_EOL, FILE_APPEND | LOCK_EX);
				} catch (Exception $e) {
					throw $e;
				}
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}

			$res = $this->gm->update('accounts_tbl', $account, "studnum_fld='".$d->ans->studnum_fld."'");
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				try {
					file_put_contents(
						"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Update account failed: '.$d->ans->studnum_fld.' - '.$student->fname_fld.' '.$student->lname_fld
						.PHP_EOL, FILE_APPEND | LOCK_EX);
				} catch (Exception $e) {
					throw $e;
				}
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}
			
			// try {
			// 	file_put_contents(
			// 		"admin.log",
			// 			date("Y-m-d H:i:s").','
			// 			.$d->ans->userid.','
			// 			.$d->ans->userfullname.','
			// 			.$d->ans->userdept.','
			// 			.$d->ans->userprog.', Updated profile: '.$d->ans->studnum_fld.' - '.$student->fname_fld.' '.$student->lname_fld
			// 			.PHP_EOL, FILE_APPEND | LOCK_EX);
			// } catch (Exception $e) {
			// 	throw $e;
			// }
			
			
			// return $this->gm->api_result(null, "success", "Successfully save responses",200);
			return $this->get->get_student(null, null);
		}
		# ./Added July 25, 2020

		public function admin_userupdate($d) {
			$info = $d->ans->info;
			$res = $this->gm->update('adminaccounts_tbl', $info, "empnum_fld='".$d->ans->empnum_fld."'");
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				file_put_contents(
					"admin.log",
					date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Failed to update data of: '.$d->ans->empnum_fld.' - '.$info->fname_fld.' '.$info->lname_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}
			file_put_contents(
				"admin.log",
				date("Y-m-d H:i:s").','.$d->ans->userid.','.$d->ans->userfullname.','.$d->ans->userdept.','.$d->ans->userprog.', Updated data of: '.$d->ans->empnum_fld.' - '.$info->fname_fld.' '.$info->lname_fld.PHP_EOL, FILE_APPEND | LOCK_EX);

			return $this->get->get_users(0);
		}

		public function admin_userinsert($d) {
			$info = $d->ans->info;
			$res = $this->gm->insert('adminaccounts_tbl', $info);
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				file_put_contents(
					"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Failed to update data of: '.$info->empnum_fld.' - '.$info->fname_fld.' '.$info->lname_fld.PHP_EOL, FILE_APPEND | LOCK_EX);
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}
			file_put_contents(
				"admin.log",
					date("Y-m-d H:i:s").','
					.$d->ans->userid.','
					.$d->ans->userfullname.','
					.$d->ans->userdept.','
					.$d->ans->userprog
					.', Added data of: '.$info->empnum_fld.' - '.$info->fname_fld.' '.$info->lname_fld.PHP_EOL, FILE_APPEND | LOCK_EX);

			$res = $this->gm->update('adminaccounts_tbl', array("pword_fld"=>$this->auth->encrypt_password(strtolower(str_replace(' ', '', $info->lname_fld)))), "empnum_fld='".$info->empnum_fld."'");
			return $this->get->get_users(0);
		}

		public function moveto_archive($d) {
			$res = $this->gm->update('adminaccounts_tbl', array("isdeleted_fld"=>1), "empnum_fld='".$d->ans->empnum_fld."'");
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				file_put_contents(
					"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Failed to Move to Archived List: '.$d->ans->empnum_fld.' - '.$d->ans->empname
						.PHP_EOL, FILE_APPEND | LOCK_EX);
					return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}
			file_put_contents(
				"admin.log",
					date("Y-m-d H:i:s").','
					.$d->ans->userid.','
					.$d->ans->userfullname.','
					.$d->ans->userdept.','
					.$d->ans->userprog
					.', Moved to Archive: '.$d->ans->empnum_fld.' - '.$d->ans->empname
					.PHP_EOL, FILE_APPEND | LOCK_EX);
			return $this->get->get_users(0);
		}

		# Added on July 29, 2020
		public function admin_studentInsert($d) {
			$student = $d->ans->student;
			$account = $d->ans->account;

			$res = $this->gm->insert('students_tbl', $student);
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				try {
					file_put_contents(
						"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog.
						', Add student failed: '.$student->studnum_fld.' - '.$student->fname_fld.' '.$student->lname_fld
						.PHP_EOL, FILE_APPEND | LOCK_EX);
				} catch (Exception $e) {
					throw $e;
				}
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}

			$res = $this->gm->insert('accounts_tbl', $account);
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				try {
					file_put_contents(
						"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Add account failed: '.$student->studnum_fld.' - '.$student->fname_fld.' '.$student->lname_fld
						.PHP_EOL, FILE_APPEND | LOCK_EX);
				} catch (Exception $e) {
					throw $e;
				}
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}
			$res = $this->gm->update('accounts_tbl', array("pword_fld"=>$this->auth->encrypt_password(strtolower(str_replace(' ', '', $student->lname_fld)))), "studnum_fld='".$student->studnum_fld."'");
			try {
				file_put_contents(
					"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog.', Student added: '.$student->studnum_fld.' - '.$student->fname_fld.' '.$student->lname_fld
						.PHP_EOL, FILE_APPEND | LOCK_EX);
			} catch (Exception $e) {
				throw $e;
			}
			
			return $this->get->get_student(null, null);
		}
		# ./Added on July 29, 2020

		# /Added on August 2, 2020
		public function dnremove_student($d) {
			$res = $this->gm->update('accounts_tbl', array("isenlisted_fld"=>1), "studnum_fld='".$d->ans->studnum_fld."'");
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				file_put_contents(
					"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Failed to Update Account: '.$d->ans->studnum_fld.' - '.$d->ans->studname
						.PHP_EOL, FILE_APPEND | LOCK_EX);
					return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}
			file_put_contents(
				"admin.log",
					date("Y-m-d H:i:s").','
					.$d->ans->userid.','
					.$d->ans->userfullname.','
					.$d->ans->userdept.','
					.$d->ans->userprog
					.', Updated Account: '.$d->ans->studnum_fld.' - '.$d->ans->studname
					.PHP_EOL, FILE_APPEND | LOCK_EX);
			$this->moveto_archivestud($d);
		}

		public function moveto_archivestud($d) {
			$res = $this->gm->update('accounts_tbl', array("isdeleted_fld"=>1), "studnum_fld='".$d->ans->studnum_fld."'");
			if ($res['code'] != 200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				file_put_contents(
					"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Failed to Move to Archived List: '.$d->ans->studnum_fld.' - '.$d->ans->studname
						.PHP_EOL, FILE_APPEND | LOCK_EX);
					return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}
			file_put_contents(
				"admin.log",
					date("Y-m-d H:i:s").','
					.$d->ans->userid.','
					.$d->ans->userfullname.','
					.$d->ans->userdept.','
					.$d->ans->userprog
					.', Moved to Archive: '.$d->ans->studnum_fld.' - '.$d->ans->studname
					.PHP_EOL, FILE_APPEND | LOCK_EX);
			return $this->get->get_users(0);
		}
		# ./Added on August 2, 2020


		# /Added on August 3, 2020
		public function update_record($d) {
			$dt = array("studyrlevel_fld"=>$d->studyrlevel_fld);
			$res = $this->gm->update('accounts_tbl', $dt, "studnum_fld='".$d->studnum_fld."'");
			if ($res['code']!=200) {
				file_put_contents(
					"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Updated Year Level of: '.$d->ans->studnum_fld.' - '.$d->ans->studname.' to '.$dt
						.PHP_EOL, FILE_APPEND | LOCK_EX);
			} else {
				file_put_contents(
					"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Failed to Update Year level of: '.$d->ans->studnum_fld.' - '.$d->ans->studname.' to '.$dt
						.PHP_EOL, FILE_APPEND | LOCK_EX);
			}
		}
		# ./Added on August 3, 2020

		// additional melner August 6
		public function update_slot($d) {
			// $dt = array("limit_fld"=>$d->limit_fld);
			// $res = $this->gm->update('slots_tbl', $dt, "recno_fld=".$d->recno_fld);
			$toAdd = $d->limit_fld;


			$sql = "UPDATE slots_tbl SET limit_fld=limit_fld+$toAdd WHERE recno_fld=?";
			$sql = $this->pdo->prepare($sql);
			$sql->execute([$d->recno_fld]);
			$sql = null;

			$sql = "UPDATE classes_tbl SET slots_fld=slots_fld+$toAdd WHERE block_fld=?";
			$sql = $this->pdo->prepare($sql);
			$sql->execute([$d->block_fld]);
			$sql = null;

			file_put_contents(
				"admin.log",
				date("Y-m-d H:i:s").','
				.$d->ans->userid.','
				.$d->ans->userfullname.','
				.$d->ans->userdept.','
				.$d->ans->userprog.
				', Added '.$toAdd.' slots to: '.$d->block_fld
				.PHP_EOL, FILE_APPEND | LOCK_EX);

			return $this->get->get_slots();

			// $res = $this->gm->update('slots_tbl', $dt, "recno_fld=".$d->recno_fld);
		}

		// Additional Paolo August 9
		public function add_announcement($d) {
			$announce = $d->announcement;
            
			$res = $this->gm->insert("announce_tbl", $announce);
			if ($res['code']!=200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				file_put_contents(
					"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Failed to add announcement: '.$d->announcement->title_fld
						.PHP_EOL, FILE_APPEND | LOCK_EX);
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}

			file_put_contents(
				"admin.log",
					date("Y-m-d H:i:s").','
					.$d->ans->userid.','
					.$d->ans->userfullname.','
					.$d->ans->userdept.','
					.$d->ans->userprog
					.', Added announcement: '.$d->announcement->title_fld
					.PHP_EOL, FILE_APPEND | LOCK_EX);

			// return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			return $this->get->get_announcements($d->ans->mode, $d->ans->userdept);
		}

		public function remove_announcement($d) {
			$recno = $d->recno_fld;
            $title = $d->title_fld;
			$res = $this->gm->update("announce_tbl", array("isdeleted_fld"=>1), "recno_fld=".$recno);
			if ($res['code']!=200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				file_put_contents(
					"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Failed to archive announcement: '.$title
						.PHP_EOL, FILE_APPEND | LOCK_EX);
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}

			file_put_contents(
				"admin.log",
					date("Y-m-d H:i:s").','
					.$d->ans->userid.','
					.$d->ans->userfullname.','
					.$d->ans->userdept.','
					.$d->ans->userprog
					.', Moved announcement to archive: '.$title
					.PHP_EOL, FILE_APPEND | LOCK_EX);

			return $this->get->get_announcements();
		}

		public function assign_instructor($d) {
			$emp = $d->ans->empnum;
			$classcode = $d->ans->code;

			$res = $this->gm->update("classes_tbl", array("empnum_fld"=>$emp), "classcode_fld=".$classcode);
			if ($res['code']!=200) {
				$payload = null;
				$remarks = "failed";
				$message = $res['errmsg'];
				file_put_contents(
					"admin.log",
						date("Y-m-d H:i:s").','
						.$d->ans->userid.','
						.$d->ans->userfullname.','
						.$d->ans->userdept.','
						.$d->ans->userprog
						.', Failed to assign instructor to class: '.$classcode
						.PHP_EOL, FILE_APPEND | LOCK_EX);
				return $this->gm->api_result($payload, $remarks, $message, $res['code']);
			}

			file_put_contents(
				"admin.log",
					date("Y-m-d H:i:s").','
					.$d->ans->userid.','
					.$d->ans->userfullname.','
					.$d->ans->userdept.','
					.$d->ans->userprog
					.', Assign instructor to class: '.$classcode
					.PHP_EOL, FILE_APPEND | LOCK_EX);
			
			return $this->gm->api_result(null, "success", "Successfully Assigned Instructor", $res['code']);		
		}
		
		public function update_faculty($en, $dt){
			$code;
			$res = $this->gm->update('faculty_tbl', $dt->info, "empnum_fld='".$en."'");
			if($res['code']==200){
				$dt->data->img = $dt->info->picture_fld;
				$payload = $dt->data;
				$remarks = "success";
				$message = "Successfully saved info";
				
			}
			else{
				$payload = null;
				$remarks = "failed";
				$message = "failed to save info";
				
			}
			
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);	
		}


	}
?>
