<?php
class Get {
	protected $gm;

	public function __construct(\PDO $pdo) {
		$this->gm = new GlobalMethods($pdo);
	}

	// Regions, City/Municipality/ Barangay
	public function get_reference($table, $conditions) {
		$sql = "SELECT * FROM $table";
		if($conditions!=null) {
			$sql.=" WHERE ".$conditions;
		}
		return $this->gm->_common($sql);
	}

	//Get Student
	public function get_student($d, $sw) {
		$sql = "SELECT students_tbl.*, accounts_tbl.studtype_fld, accounts_tbl.isenrolled_fld, accounts_tbl.enrolleddate_fld, accounts_tbl.acadyear_fld, accounts_tbl.sem_fld, accounts_tbl.studyrlevel_fld, accounts_tbl.isenlisted_fld, accounts_tbl.enlistdate_fld, accounts_tbl.enlistreason_fld, accounts_tbl.learningtype_fld, accounts_tbl.imgcor_fld, accounts_tbl.imgprospectus_fld, accounts_tbl.imggrades_fld, accounts_tbl.isregular_fld, accounts_tbl.imgresidencycert_fld, accounts_tbl.imghonordismiss_fld, accounts_tbl.imggoodmoral_fld, accounts_tbl.imghepascreen_fld, accounts_tbl.imgidcard_fld, accounts_tbl.imgf138ytor_fld, accounts_tbl.imgbirthcert_fld, accounts_tbl.block_fld FROM students_tbl INNER JOIN accounts_tbl USING(studnum_fld) WHERE accounts_tbl.isdeleted_fld=0";

		if($d!=null){
			if ($sw==1) {
				$sql.= " AND students_tbl.studnum_fld='$d'";
			} else if ($sw==2) {
				$sql.= " AND students_tbl.dept_fld='$d'";
			} else if ($sw==3) {
				$sql.= " AND students_tbl.program_fld='$d'";
			}
		} else if($d==null){
			if ($sw==4) {
				$sql.= " AND (accounts_tbl.isenrolled_fld=1 AND accounts_tbl.isenlisted_fld=2) OR (accounts_tbl.isenrolled_fld=2 AND accounts_tbl.isenlisted_fld=2)";
			}
		}
		return $this->gm->_common($sql);
	}

	//Get Enrolled Subjects


	public function get_enrolledsubj($ay, $sem, $studnum){
		$studentdetails=[];
		$enrolledsubjects=[];
		$code=404;

		$sql = "SELECT students_tbl.*, accounts_tbl.studtype_fld, accounts_tbl.isenrolled_fld, accounts_tbl.enrolleddate_fld, accounts_tbl.acadyear_fld, accounts_tbl.sem_fld, accounts_tbl.studyrlevel_fld, accounts_tbl.isenlisted_fld, accounts_tbl.enlistdate_fld, accounts_tbl.enlistreason_fld, accounts_tbl.learningtype_fld, accounts_tbl.imgcor_fld, accounts_tbl.imgprospectus_fld, accounts_tbl.imggrades_fld, accounts_tbl.isregular_fld, accounts_tbl.imgresidencycert_fld, accounts_tbl.imghonordismiss_fld, accounts_tbl.imggoodmoral_fld, accounts_tbl.imghepascreen_fld, accounts_tbl.imgidcard_fld, accounts_tbl.imgf138ytor_fld, accounts_tbl.imgbirthcert_fld, accounts_tbl.block_fld FROM students_tbl INNER JOIN accounts_tbl USING(studnum_fld) WHERE students_tbl.studnum_fld='$studnum'";

		$res = $this->gm->execute_query($sql, "No records found");
		if ($res['code'] == 200) {
			$code=200;
			$studentdetails = $res['data'];
			$remarks = "success";
			$message = "Successfully retrieved requested data";

			$sql = "SELECT enrolledsubj_tbl.*, (SELECT subjects_tbl.subjdesc_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS subjdesc_fld, (SELECT subjects_tbl.lecunits_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS lecunits_fld, (SELECT subjects_tbl.labunits_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS labunits_fld, (SELECT subjects_tbl.rleunits_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS rleunits_fld, classes_tbl.room_fld, classes_tbl.starttime_fld, classes_tbl.endtime_fld, classes_tbl.day_fld, classes_tbl.empnum_fld, (SELECT CONCAT(des_fld, '. ', fname_fld, ' ', lname_fld, ' ', extname_fld) FROM faculty_tbl WHERE empnum_fld=classes_tbl.empnum_fld) AS faculty_fld, (SELECT emailadd_fld FROM faculty_tbl WHERE empnum_fld=classes_tbl.empnum_fld) AS facultyemail_fld FROM enrolledsubj_tbl INNER JOIN classes_tbl USING (classcode_fld) WHERE enrolledsubj_tbl.studnum_fld='$studnum' AND enrolledsubj_tbl.ay_fld='$ay' AND enrolledsubj_tbl.sem_fld=$sem AND enrolledsubj_tbl.block_fld=classes_tbl.block_fld";
			$res = $this->gm->execute_query($sql, "No records found");
			if ($res['code']==200) {
				$enrolledsubjects=$res['data'];
			}

			$payload = array("student"=>$studentdetails, "subjects"=>$enrolledsubjects);
		} else {
			$code = 404;
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $this->gm->api_result($payload, $remarks, $message, $code);
	}

	//Get Courses
	public function get_courses() {
		$sql = "SELECT DISTINCTROW(dept_fld) from courses_tbl";
		return $this->gm->_common($sql);
	}

	//Get Programs
	public function get_programs($d) {
		$sql = "SELECT * from courses_tbl";
		if ($d!=null) {
			$sql.= " WHERE dept_fld='$d'";
		}
		return $this->gm->_common($sql);
	}

	//Get Classes 201810998
	public function get_classes($sem, $ay, $d) {
		$sql = "SELECT classes_tbl.*, (SELECT subjects_tbl.subjdesc_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS subjdesc_fld, (SELECT subjects_tbl.lecunits_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS lecunits_fld, (SELECT subjects_tbl.labunits_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS labunits_fld, (SELECT subjects_tbl.rleunits_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS rleunits_fld FROM classes_tbl WHERE classes_tbl.sem_fld=$sem AND classes_tbl.ay_fld='$ay' AND classes_tbl.slots_fld>0";
		if ($d!=null) {
			$sql.= " AND classes_tbl.block_fld='$d'";
		}
		return $this->gm->_common($sql);
	}
	//Get Current Acad Year and Semester
	public function get_acadyear() {
		$sql = "SELECT * from settings_tbl WHERE isactive_fld=1";
		return $this->gm->_common($sql);
	}

	//Get blocks
	public function get_blocks($sem, $ay, $program) {
		$sql = "SELECT * FROM slots_tbl WHERE sem_fld=$sem AND ay_fld='$ay' AND program_fld='$program'";
		return $this->gm->_common($sql);
	}

	public function get_enrolled($ay, $sem, $block) {
		$sql = "SELECT students_tbl.studnum_fld, students_tbl.fname_fld, students_tbl.mname_fld, students_tbl.lname_fld, students_tbl.extname_fld, accounts_tbl.block_fld, accounts_tbl.enrolleddate_fld, accounts_tbl.learningtype_fld FROM students_tbl INNER JOIN accounts_tbl USING (studnum_fld) WHERE accounts_tbl.sem_fld=$sem AND accounts_tbl.acadyear_fld='$ay' AND accounts_tbl.block_fld='$block' AND isenrolled_fld=2";
		return $this->gm->_common($sql);
	}


	# Added July 18, 2020
	# Edited July 19, 2020
	public function get_acadrecords($dt, $cr) {
		if ($cr != null) {
			$sql = "SELECT * FROM acadrecords_tbl WHERE studnum_fld='$dt' AND iscredited_fld=1 ORDER BY acadyear_fld DESC, sem_fld ASC";
		} else {
			$sql = "SELECT * FROM acadrecords_tbl WHERE studnum_fld='$dt' ORDER BY acadyear_fld DESC, sem_fld ASC";
		}
		return $this->gm->_common($sql);
	}
	# ./Edited July 19, 2020

	public function get_subjects($pr) {
		$sql = "SELECT * FROM subjects_tbl WHERE program_fld='$pr' ORDER BY curryear_fld DESC, sem_fld ASC, yrlevel_fld ASC";
		return $this->gm->_common($sql);
	}
	# ./Added July 18, 2020


	# Added July 26, 2020
	public function get_users($isdel) {
		$sql = "SELECT recno_fld, empnum_fld, fname_fld, mname_fld, lname_fld, extname_fld, picture_fld, role_fld, dept_fld, program_fld, emailadd_fld, isdeleted_fld FROM adminaccounts_tbl WHERE empnum_fld>'100000'";
		return $this->gm->_common($sql);
	}
	# ./Added July 26, 2020


	public function get_adminlogs() {

		$h = fopen("admin.log", "r");
		$logs = [];
		while (($data = fgetcsv($h)) !== FALSE) {
		    array_push($logs, array(
		    	"logdate"=>$data[0],
					"userid"=>$data[1],
					"username"=>$data[2],
					"userdept"=>$data[3],
					"userprog"=>$data[4],
		    	"desc"=>$data[5]
		    ));
		}
		fclose($h);
		$remarks = "success";
		$message = "Successfully retrieved requested data";
		$code = 200;
		return $this->gm->api_result($logs, $remarks, $message, $code);
	}

		# /Dean Methods
	# /Added on August 2, 2020
	# /Edited on August 5, 2020 | August 19, 2020
	public function get_classesperdept($sem, $ay, $d){
		$sql = "SELECT classes_tbl.*, (SELECT subjects_tbl.subjdesc_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS subjdesc_fld, (SELECT CONCAT(fname_fld, ' ', lname_fld) FROM faculty_tbl WHERE empnum_fld=classes_tbl.empnum_fld) AS fullname_fld FROM classes_tbl INNER JOIN slots_tbl USING (block_fld) WHERE classes_tbl.sem_fld=$sem AND classes_tbl.ay_fld='$ay' AND slots_tbl.dept_fld='$d'";

		return $this->gm->_common($sql);
	}
	# ./Edited on August 5, 2020 | August 19, 2020

	public function get_blocksperdept($sem, $ay, $dept) {
		$sql = "SELECT * FROM slots_tbl WHERE sem_fld=$sem AND ay_fld='$ay' AND dept_fld='$dept'";
		return $this->gm->_common($sql);
	}

	public function get_enrolledperprogram($ay, $sem, $program) {
		$sql = "SELECT students_tbl.studnum_fld, students_tbl.fname_fld, students_tbl.mname_fld, students_tbl.lname_fld, students_tbl.extname_fld, accounts_tbl.block_fld, accounts_tbl.enrolleddate_fld, accounts_tbl.learningtype_fld, accounts_tbl.studyrlevel_fld, students_tbl.program_fld FROM students_tbl INNER JOIN accounts_tbl USING (studnum_fld) WHERE accounts_tbl.sem_fld=$sem AND accounts_tbl.acadyear_fld='$ay' AND students_tbl.program_fld = '$program' AND isenrolled_fld=2";
		
		return $this->gm->_common($sql);
	}

	public function get_enrolledperblock($ay, $sem, $block) {
		$sql = "SELECT students_tbl.studnum_fld, students_tbl.fname_fld, students_tbl.mname_fld, students_tbl.lname_fld, students_tbl.extname_fld, accounts_tbl.block_fld, accounts_tbl.enrolleddate_fld, accounts_tbl.learningtype_fld, accounts_tbl.studyrlevel_fld, students_tbl.program_fld FROM students_tbl INNER JOIN accounts_tbl USING (studnum_fld) WHERE accounts_tbl.sem_fld=$sem AND accounts_tbl.acadyear_fld='$ay' AND accounts_tbl.block_fld='$block' AND isenrolled_fld=2";

		return $this->gm->_common($sql);
	}

	public function get_enrolledperclass($ay, $sem, $code) {
		$sql = "SELECT students_tbl.studnum_fld, students_tbl.fname_fld, students_tbl.mname_fld, students_tbl.lname_fld, students_tbl.extname_fld, accounts_tbl.block_fld, accounts_tbl.enrolleddate_fld, accounts_tbl.learningtype_fld, accounts_tbl.studyrlevel_fld, students_tbl.program_fld FROM students_tbl INNER JOIN accounts_tbl USING (studnum_fld) INNER JOIN enrolledsubj_tbl USING (studnum_fld) WHERE accounts_tbl.sem_fld=$sem AND accounts_tbl.acadyear_fld='$ay' AND enrolledsubj_tbl.classcode_fld=$code  AND isenrolled_fld=2";
		
		return $this->gm->_common($sql);
	}

	public function get_enrolledperprog($ay, $sem, $prog) {
		$sql = "SELECT students_tbl.studnum_fld, students_tbl.fname_fld, students_tbl.mname_fld, students_tbl.lname_fld, students_tbl.extname_fld, accounts_tbl.block_fld, accounts_tbl.enrolleddate_fld, accounts_tbl.learningtype_fld, accounts_tbl.studyrlevel_fld, students_tbl.program_fld FROM students_tbl INNER JOIN accounts_tbl USING (studnum_fld) WHERE accounts_tbl.sem_fld=$sem AND accounts_tbl.acadyear_fld='$ay' AND students_tbl.program_fld='$prog' AND isenrolled_fld=2";

		return $this->gm->_common($sql);
	}
	# ./Added on August 2, 2020

	//Additonal Melner
	public function get_slots() {
		$sql = "SELECT * FROM slots_tbl";
		return $this->gm->_common($sql);
	}

	//Additional Paolo
	public function get_announcements($mode, $dept) {
		$sql = "SELECT * FROM announce_tbl WHERE isdeleted_fld=0"; 
		if ($mode==1) {
			$sql .= " AND dept_fld='$dept'";
		} else if ($mode==2) {
			$sql .= " AND (dept_fld='$dept' OR dept_fld='ALL')";
		}
		$sql .= " ORDER BY datetime_fld DESC LIMIT 15";
		return $this->gm->_common($sql);
	}

	# /Added August 19, 2020
	public function get_faculty($type, $param) {
		$sql = "SELECT * FROM faculty_tbl WHERE isdeleted_fld=0";
		if ($param!=null) {
			if ($type==1) {
				$sql.= " AND dept_fld='$param'";
			} else if ($type==2) {
				$sql.= " AND empnum_fld='$param'";
			}
		}
		return $this->gm->_common($sql);
	}
	# ./Added August 19, 2020

	# /Added August 29, 2020
	public function get_classlist($ay, $sem, $empno){

		$sql = "SELECT DISTINCT classes_tbl.*, subjects_tbl.subjdesc_fld,subjects_tbl.subjcode_fld, classes_tbl.starttime_fld,classes_tbl.endtime_fld,classes_tbl.day_fld FROM classes_tbl INNER JOIN subjects_tbl USING(subjcode_fld)WHERE classes_tbl.ay_fld='$ay' AND classes_tbl.sem_fld=$sem AND empnum_fld='$empno' AND classes_tbl.isdeleted_fld=0 GROUP BY classes_tbl.classcode_fld ORDER BY subjects_tbl.subjdesc_fld ASC";
		$res = $this->gm->execute_query($sql, "Invalid Request");

		if ($res['code'] == 200) {
			
			$data = $res['data'];
			$array = array("class"=>array(),"archived"=>array());

			for($i=0; $i<count($data); $i++){
				if($data[$i]['isdeleted_fld']==0){
					array_push($array['class'], $data[$i]);
				}
				if($data[$i]['isdeleted_fld']==1){
					array_push($array['archived'], $data[$i]);
				}
			}


			$payload = $array;
			$code = 200;
			$remarks = "success";
			$message = "Successfully retrieved requested data";

		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}

	public function get_studentmember($classcode){
		$student = [];
		$teacher = [];
		$sql = "SELECT accounts_tbl.learningtype_fld, accounts_tbl.block_fld, students_tbl.emailadd_fld,students_tbl.contactnum_fld, students_tbl.studnum_fld, students_tbl.fname_fld, students_tbl.lname_fld, students_tbl.profilepic_fld FROM enrolledsubj_tbl INNER JOIN students_tbl USING (studnum_fld) LEFT JOIN accounts_tbl USING(studnum_fld) WHERE enrolledsubj_tbl.classcode_fld='$classcode' ORDER BY students_tbl.lname_fld ASC";
		$res = $this->gm->execute_query($sql, 'No data Found');

		if($res['code']==200){
			$code = 200;
			$student = $res['data'];
			$remarks = "success";
			$message = "Successfully retrieved requested data";

			$sql = "SELECT faculty_tbl.fname_fld, faculty_tbl.lname_fld, faculty_tbl.picture_fld, faculty_tbl.empnum_fld FROM classes_tbl INNER JOIN faculty_tbl USING (empnum_fld) WHERE classes_tbl.classcode_fld = '$classcode' LIMIT 1";
			$res = $this->gm->execute_query($sql, 'No data found');
			if($res['code']==200){
				$teacher = $res['data'];
			}
			if($res['code']==403){
				$teacher=[];
			}
			$payload = array("students"=>$student, "teacher"=>$teacher);
		}
		else{
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}

	public function get_studentclass($ay, $sem, $sn){
		$sql = "SELECT enrolledsubj_tbl.*, classes_tbl.empnum_fld, subjects_tbl.subjdesc_fld, faculty_tbl.fname_fld, faculty_tbl.lname_fld, faculty_tbl.picture_fld, classes_tbl.starttime_fld,classes_tbl.endtime_fld,classes_tbl.day_fld  
			FROM enrolledsubj_tbl 
			LEFT JOIN classes_tbl 
			USING (classcode_fld) 
			LEFT JOIN subjects_tbl 
			ON classes_tbl.subjcode_fld=subjects_tbl.subjcode_fld 
			LEFT JOIN faculty_tbl 
			ON classes_tbl.empnum_fld=faculty_tbl.empnum_fld 
			WHERE enrolledsubj_tbl.studnum_fld='$sn' 
			AND enrolledsubj_tbl.ay_fld='$ay' 
			AND enrolledsubj_tbl.sem_fld=$sem GROUP BY enrolledsubj_tbl.classcode_fld ORDER BY subjects_tbl.subjdesc_fld ASC";

		return $this->gm->_common($sql);
	}
	public function get_studentdetails($sn){
		$sql = "SELECT studnum_fld, fname_fld, mname_fld, lname_fld, extname_fld, emailadd_fld, program_fld, dept_fld, sex_fld, profilepic_fld FROM students_tbl WHERE studnum_fld='$sn'";
		return $this->gm->_common($sql);
	}
}
?>