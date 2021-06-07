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

	//Get Classes
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


	# Added July 20, 2020
	public function get_stats() {
		# variables
		$total=0; $enlisted=0; $enrolled=0; $enlisted1=0; $enrolled1=0; $enlisted2=0; $enrolled2=0; $enlisted3=0; $enrolled3=0; $enlisted4=0; $enrolled4=0;
		$cahs=0; $cba = 0; $ccs = 0; $ceas = 0; $chtm = 0;
		$cahsEnlisted=0; $cbaEnlisted = 0; $ccsEnlisted = 0; $ceasEnlisted = 0; $chtmEnlisted = 0;
		$cahsEnrolled=0; $cbaEnrolled = 0; $ccsEnrolled = 0; $ceasEnrolled = 0; $chtmEnrolled = 0;

		$cahsL1=0; $cbaL1 = 0; $ccsL1 = 0; $ceasL1 = 0; $chtmL1 = 0;
		$cahsR1=0; $cbaR1 = 0; $ccsR1 = 0; $ceasR1 = 0; $chtmR1 = 0;

		$cahsL2=0; $cbaL2 = 0; $ccsL2 = 0; $ceasL2 = 0; $chtmL2 = 0;
		$cahsR2=0; $cbaR2 = 0; $ccsR2 = 0; $ceasR2 = 0; $chtmR2 = 0;

		$cahsL3=0; $cbaL3 = 0; $ccsL3 = 0; $ceasL3 = 0; $chtmL3 = 0;
		$cahsR3=0; $cbaR3 = 0; $ccsR3 = 0; $ceasR3 = 0; $chtmR3 = 0;

		$cahsL4=0; $cbaL4 = 0; $ccsL4 = 0; $ceasL4 = 0; $chtmL4 = 0;
		$cahsR4=0; $cbaR4 = 0; $ccsR4 = 0; $ceasR4 = 0; $chtmR4 = 0;

		$bsn=0; $bsm=0;
		$bsa=0; $bsca=0; $bsbafm=0; $bsbamkt=0; $bsbahrm=0; $bsbahrdm=0;
		$bscs=0; $bsit=0; $bsemc=0; $act=0; 
		$bacom=0; $bcaed=0; $beced=0; $beed=0; $bped=0; $beedgen=0; $bsedbio=0; $bsedmapeh=0; $bsede=0; $bsedfil=0; $bsedm=0; $bsedsci=0; $bsedsoc=0; $profed=0; 
		$bshm=0; $bshrm=0; $bstm=0;

		$bsnEnlisted=0; $bsmEnlisted=0;
		$bsaEnlisted=0; $bscaEnlisted=0; $bsbafmEnlisted=0; $bsbamktEnlisted=0; $bsbahrmEnlisted=0; $bsbahrdmEnlisted=0;
		$bscsEnlisted=0; $bsitEnlisted=0; $bsemcEnlisted=0; $actEnlisted=0; 
		$bacomEnlisted=0; $bcaedEnlisted=0; $becedEnlisted=0; $beedEnlisted=0; $bpedEnlisted=0; $beedgenEnlisted=0; $bsedbioEnlisted=0; $bsedmapehEnlisted=0; $bsedeEnlisted=0; $bsedfilEnlisted=0; $bsedmEnlisted=0; $bsedsciEnlisted=0; $bsedsocEnlisted=0; $profedEnlisted=0; 
		$bshmEnlisted=0; $bshrmEnlisted=0; $bstmEnlisted=0;

		$bsnL1=0; $bsmL1=0;
		$bsaL1=0; $bscaL1=0; $bsbafmL1=0; $bsbamktL1=0; $bsbahrmL1=0; $bsbahrdmL1=0;
		$bscsL1=0; $bsitL1=0; $bsemcL1=0; $actL1=0; 
		$bacomL1=0; $bcaedL1=0; $becedL1=0; $beedL1=0; $bpedL1=0; $beedgenL1=0; $bsedbioL1=0; $bsedmapehL1=0; $bsedeL1=0; $bsedfilL1=0; $bsedmL1=0; $bsedsciL1=0; $bsedsocL1=0; $profedL1=0; 
		$bshmL1=0; $bshrmL1=0; $bstmL1=0;

		$bsnL2=0; $bsmL2=0;
		$bsaL2=0; $bscaL2=0; $bsbafmL2=0; $bsbamktL2=0; $bsbahrmL2=0; $bsbahrdmL2=0;
		$bscsL2=0; $bsitL2=0; $bsemcL2=0; $actL2=0; 
		$bacomL2=0; $bcaedL2=0; $becedL2=0; $beedL2=0; $bpedL2=0; $beedgenL2=0; $bsedbioL2=0; $bsedmapehL2=0; $bsedeL2=0; $bsedfilL2=0; $bsedmL2=0; $bsedsciL2=0; $bsedsocL2=0; $profedL2=0; 
		$bshmL2=0; $bshrmL2=0; $bstmL2=0;

		$bsnL3=0; $bsmL3=0;
		$bsaL3=0; $bscaL3=0; $bsbafmL3=0; $bsbamktL3=0; $bsbahrmL3=0; $bsbahrdmL3=0;
		$bscsL3=0; $bsitL3=0; $bsemcL3=0; $actL3=0; 
		$bacomL3=0; $bcaedL3=0; $becedL3=0; $beedL3=0; $bpedL3=0; $beedgenL3=0; $bsedbioL3=0; $bsedmapehL3=0; $bsedeL3=0; $bsedfilL3=0; $bsedmL3=0; $bsedsciL3=0; $bsedsocL3=0; $profedL3=0; 
		$bshmL3=0; $bshrmL3=0; $bstmL3=0;

		$bsnL4=0; $bsmL4=0;
		$bsaL4=0; $bscaL4=0; $bsbafmL4=0; $bsbamktL4=0; $bsbahrmL4=0; $bsbahrdmL4=0;
		$bscsL4=0; $bsitL4=0; $bsemcL4=0; $actL4=0; 
		$bacomL4=0; $bcaedL4=0; $becedL4=0; $beedL4=0; $bpedL4=0; $beedgenL4=0; $bsedbioL4=0; $bsedmapehL4=0; $bsedeL4=0; $bsedfilL4=0; $bsedmL4=0; $bsedsciL4=0; $bsedsocL4=0; $profedL4=0; 
		$bshmL4=0; $bshrmL4=0; $bstmL4=0;

		$bsnEnrolled=0; $bsmEnrolled=0;
		$bsaEnrolled=0; $bscaEnrolled=0; $bsbafmEnrolled=0; $bsbamktEnrolled=0; $bsbahrmEnrolled=0; $bsbahrdmEnrolled=0;
		$bscsEnrolled=0; $bsitEnrolled=0; $bsemcEnrolled=0; $actEnrolled=0; 
		$bacomEnrolled=0; $bcaedEnrolled=0; $becedEnrolled=0; $beedEnrolled=0; $bpedEnrolled=0; $beedgenEnrolled=0; $bsedbioEnrolled=0; $bsedmapehEnrolled=0; $bsedeEnrolled=0; $bsedfilEnrolled=0; $bsedmEnrolled=0; $bsedsciEnrolled=0; $bsedsocEnrolled=0; $profedEnrolled=0; 
		$bshmEnrolled=0; $bshrmEnrolled=0; $bstmEnrolled=0;

		$bsnR1=0; $bsmR1=0;
		$bsaR1=0; $bscaR1=0; $bsbafmR1=0; $bsbamktR1=0; $bsbahrmR1=0; $bsbahrdmR1=0;
		$bscsR1=0; $bsitR1=0; $bsemcR1=0; $actR1=0; 
		$bacomR1=0; $bcaedR1=0; $becedR1=0; $beedR1=0; $bpedR1=0; $beedgenR1=0; $bsedbioR1=0; $bsedmapehR1=0; $bsedeR1=0; $bsedfilR1=0; $bsedmR1=0; $bsedsciR1=0; $bsedsocR1=0; $profedR1=0; 
		$bshmR1=0; $bshrmR1=0; $bstmR1=0;

		$bsnR2=0; $bsmR2=0;
		$bsaR2=0; $bscaR2=0; $bsbafmR2=0; $bsbamktR2=0; $bsbahrmR2=0; $bsbahrdmR2=0;
		$bscsR2=0; $bsitR2=0; $bsemcR2=0; $actR2=0; 
		$bacomR2=0; $bcaedR2=0; $becedR2=0; $beedR2=0; $bpedR2=0; $beedgenR2=0; $bsedbioR2=0; $bsedmapehR2=0; $bsedeR2=0; $bsedfilR2=0; $bsedmR2=0; $bsedsciR2=0; $bsedsocR2=0; $profedR2=0; 
		$bshmR2=0; $bshrmR2=0; $bstmR2=0;

		$bsnR3=0; $bsmR3=0;
		$bsaR3=0; $bscaR3=0; $bsbafmR3=0; $bsbamktR3=0; $bsbahrmR3=0; $bsbahrdmR3=0;
		$bscsR3=0; $bsitR3=0; $bsemcR3=0; $actR3=0; 
		$bacomR3=0; $bcaedR3=0; $becedR3=0; $beedR3=0; $bpedR3=0; $beedgenR3=0; $bsedbioR3=0; $bsedmapehR3=0; $bsedeR3=0; $bsedfilR3=0; $bsedmR3=0; $bsedsciR3=0; $bsedsocR3=0; $profedR3=0; 
		$bshmR3=0; $bshrmR3=0; $bstmR3=0;

		$bsnR4=0; $bsmR4=0;
		$bsaR4=0; $bscaR4=0; $bsbafmR4=0; $bsbamktR4=0; $bsbahrmR4=0; $bsbahrdmR4=0;
		$bscsR4=0; $bsitR4=0; $bsemcR4=0; $actR4=0; 
		$bacomR4=0; $bcaedR4=0; $becedR4=0; $beedR4=0; $bpedR4=0; $beedgenR4=0; $bsedbioR4=0; $bsedmapehR4=0; $bsedeR4=0; $bsedfilR4=0; $bsedmR4=0; $bsedsciR4=0; $bsedsocR4=0; $profedR4=0; 
		$bshmR4=0; $bshrmR4=0; $bstmR4=0;


		# ./variables


		$sql = "SELECT students_tbl.dept_fld, students_tbl.program_fld, accounts_tbl.studyrlevel_fld, accounts_tbl.isenlisted_fld, accounts_tbl.isenrolled_fld FROM students_tbl INNER JOIN accounts_tbl USING (studnum_fld) WHERE accounts_tbl.isdeleted_fld=0";
		$res = $this->gm->execute_query($sql, "No records found");
		if ($res['code']==200) {
			foreach ($res['data'] as $key=>$value) {
				$total+=1;
				if($value['isenlisted_fld']==2) { $enlisted+=1; }
				if($value['isenrolled_fld']==2) { $enrolled+=1; }
				if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $enlisted1+=1; }
				if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $enrolled1+=1; }
				if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $enlisted2+=1; }
				if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $enrolled2+=1; }
				if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $enlisted3+=1; }
				if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $enrolled3+=1; }
				if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $enlisted4+=1; }
				if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $enrolled4+=1; }

				switch ($value['dept_fld']) {
					case "CAHS": 
						$cahs+=1; 
						if($value['isenlisted_fld']==2) { $cahsEnlisted+=1; }
						if($value['isenrolled_fld']==2) { $cahsEnrolled+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $cahsL1+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $cahsR1+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $cahsL2+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $cahsR2+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $cahsL3+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $cahsR3+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $cahsL4+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $cahsR4+=1; }

						switch ($value['program_fld']) {
							case "BSM": 
								$bsm+=1;
								if($value['isenlisted_fld']==2) { $bsmEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsmEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsmL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsmR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsmL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsmR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsmL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsmR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsmL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsmR4+=1; }
								break;
							case "BSN": 
								$bsn+=1; 
								if($value['isenlisted_fld']==2) { $bsnEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsnEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsnL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsnR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsnL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsnR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsnL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsnR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsnL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsnR4+=1; }
								break;
								break;
							default: break;
						}
						break;

					case "CBA": 
						$cba+=1;
					 	if($value['isenlisted_fld']==2) { $cbaEnlisted+=1; }
						if($value['isenrolled_fld']==2) { $cbaEnrolled+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $cbaL1+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $cbaR1+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $cbaL2+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $cbaR2+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $cbaL3+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $cbaR3+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $cbaL4+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $cbaR4+=1; }

						switch ($value['program_fld']) {
							case "BSA": 
								$bsa+=1; 
								if($value['isenlisted_fld']==2) { $bsaEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsaEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsaL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsaR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsaL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsaR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsaL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsaR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsaL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsaR4+=1; }
								break;
							case "BSCA": 
								$bsca+=1; 
								if($value['isenlisted_fld']==2) { $bscaEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bscaEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bscaL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bscaR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bscaL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bscaR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bscaL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bscaR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bscaL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bscaR4+=1; }
								break;
							case "BSBA-FM": 
								$bsbafm+=1; 
								if($value['isenlisted_fld']==2) { $bsbafmEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsbafmEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsbafmL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsbafmR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsbafmL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsbafmR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsbafmL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsbafmR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsbafmL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsbafmR4+=1; }
								break;
							case "BSBA-MKT": 
								$bsbamkt+=1; 
								if($value['isenlisted_fld']==2) { $bsbamktEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsbamktEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsbamktL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsbamktR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsbamktL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsbamktR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsbamktL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsbamktR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsbamktL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsbamktR4+=1; }
								break;
							case "BSBA-HRM": 
								$bsbahrm+=1; 
								if($value['isenlisted_fld']==2) { $bsbahrmEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsbahrmEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsbahrmL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsbahrmR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsbahrmL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsbahrmR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsbahrmL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsbahrmR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsbahrmL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsbahrmR4+=1; }
								break;
							case "BSBA-HRDM": 
								$bsbahrdm+=1; 
								if($value['isenlisted_fld']==2) { $bsbahrdmEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsbahrdmEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsbahrdmL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsbahrdmR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsbahrdmL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsbahrdmR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsbahrdmL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsbahrdmR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsbahrdmL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsbahrdmR4+=1; }
								break;
							default: break;
						}
						break;

					case "CCS": 
						$ccs+=1; 
						if($value['isenlisted_fld']==2) { $ccsEnlisted+=1; }
						if($value['isenrolled_fld']==2) { $ccsEnrolled+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $ccsL1+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $ccsR1+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $ccsL2+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $ccsR2+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $ccsL3+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $ccsR3+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $ccsL4+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $ccsR4+=1; }

						switch ($value['program_fld']) {
							case "BSCS": 
								$bscs+=1; 
								if($value['isenlisted_fld']==2) { $bscsEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bscsEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bscsL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bscsR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bscsL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bscsR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bscsL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bscsR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bscsL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bscsR4+=1; }
								break;
							case "BSIT": 
								$bsit+=1; 
								if($value['isenlisted_fld']==2) { $bsitEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsitEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsitL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsitR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsitL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsitR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsitL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsitR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsitL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsitR4+=1; }
								break;
							case "BSEMC": 
								$bsemc+=1; 
								if($value['isenlisted_fld']==2) { $bsemcEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsemcEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsemcL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsemcR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsemcL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsemcR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsemcL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsemcR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsemcL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsemcR4+=1; }
								break;
							case "ACT": 
								$act+=1; 
								if($value['isenlisted_fld']==2) { $actEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $actEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $actL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $actR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $actL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $actR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $actL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $actR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $actL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $actR4+=1; }
								break;
							default: break;
						}
						break;

					case "CEAS": 
						$ceas+=1; 
						if($value['isenlisted_fld']==2) { $ceasEnlisted+=1; }
						if($value['isenrolled_fld']==2) { $ceasEnrolled+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $ceasL1+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $ceasR1+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $ceasL2+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $ceasR2+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $ceasL3+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $ceasR3+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $ceasL4+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $ceasR4+=1; }

						switch ($value['program_fld']) {
							case "BACOM": 
								$bacom+=1; 
								if($value['isenlisted_fld']==2) { $bacomEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bacomEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bacomL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bacomR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bacomL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bacomR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bacomL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bacomR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bacomL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bacomR4+=1; }
								break;
							case "BCAEd": 
								$bcaed+=1; 
								if($value['isenlisted_fld']==2) { $bcaedEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bcaedEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bcaedL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bcaedR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bcaedL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bcaedR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bcaedL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bcaedR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bcaedL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bcaedR4+=1; }
								break;
							case "BECEd": 
								$beced+=1; 
								if($value['isenlisted_fld']==2) { $becedEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $becedEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $becedL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $becedR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $becedL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $becedR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $becedL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $becedR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $becedL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $becedR4+=1; }
								break;
							case "BEEd": 
								$beed+=1; 
								if($value['isenlisted_fld']==2) { $beedEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $beedEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $beedL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $beedR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $beedL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $beedR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $beedL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $beedR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $beedL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $beedR4+=1; }
								break;
							case "BPEd": 
								$bped+=1; 
								if($value['isenlisted_fld']==2) { $bpedEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bpedEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bpedL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bpedR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bpedL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bpedR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bpedL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bpedR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bpedL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bpedR4+=1; }
								break;
							case "BEEd-GEN": 
								$beedgen+=1; 
								if($value['isenlisted_fld']==2) { $beedgenEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $beedgenEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $beedgenL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $beedgenR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $beedgenL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $beedgenR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $beedgenL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $beedgenR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $beedgenL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $beedgenR4+=1; }
								break;
							case "BSEd-BIO": 
								$bsedbio+=1; 
								if($value['isenlisted_fld']==2) { $bsedbioEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsedbioEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsedbioL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsedbioR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsedbioL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsedbioR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsedbioL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsedbioR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsedbioL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsedbioR4+=1; }
								break;
							case "BSEd-MAPEH": 
								$bsedmapeh+=1; 
								if($value['isenlisted_fld']==2) { $bsedmapehEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsedmapehEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsedmapehL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsedmapehR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsedmapehL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsedmapehR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsedmapehL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsedmapehR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsedmapehL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsedmapehR4+=1; }
								break;
							case "BSEd-E": 
								$bsede+=1; 
								if($value['isenlisted_fld']==2) { $bsedeEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsedeEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsedeL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsedeR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsedeL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsedeR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsedeL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsedeR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsedeL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsedeR4+=1; }
								break;
							case "BSEd-FIL": 
								$bsedfil+=1; 
								if($value['isenlisted_fld']==2) { $bsedfilEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsedfilEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsedfilL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsedfilR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsedfilL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsedfilR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsedfilL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsedfilR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsedfilL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsedfilR4+=1; }
								break;
							case "BSEd-M": 
								$bsedm+=1; 
								if($value['isenlisted_fld']==2) { $bsedmEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsedmEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsedmL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsedmR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsedmL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsedmR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsedmL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsedmR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsedmL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsedmR4+=1; }
								break;
							case "BSEd-SCI": 
								$bsedsci+=1; 
								if($value['isenlisted_fld']==2) { $bsedsciEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsedsciEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsedsciL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsedsciR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsedsciL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsedsciR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsedsciL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsedsciR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsedsciL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsedsciR4+=1; }
								break;
							case "BSEd-SOC": 
								$bsedsoc+=1; 
								if($value['isenlisted_fld']==2) { $bsedsocEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bsedsocEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bsedsocL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bsedsocR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bsedsocL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bsedsocR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bsedsocL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bsedsocR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bsedsocL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bsedsocR4+=1; }
								break;
							case "PROFED": 
								$profed+=1; 
								if($value['isenlisted_fld']==2) { $profedEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $profedEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $profedL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $profedR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $profedL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $profedR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $profedL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $profedR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $profedL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $profedR4+=1; }
								break;
							default: break;
						}
						break;

					case "CHTM": 
						$chtm+=1; 
						if($value['isenlisted_fld']==2) { $chtmEnlisted+=1; }
						if($value['isenrolled_fld']==2) { $chtmEnrolled+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $chtmL1+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $chtmR1+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $chtmL2+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $chtmR2+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $chtmL3+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $chtmR3+=1; }
						if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $chtmL4+=1; }
						if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $chtmR4+=1; }

						switch ($value['program_fld']) {
							case "BSHM": 
								$bshm+=1; 
								if($value['isenlisted_fld']==2) { $bshmEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bshmEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bshmL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bshmR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bshmL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bshmR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bshmL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bshmR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bshmL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bshmR4+=1; }
								break;
							case "BSHRM": 
								$bshrm+=1; 
								if($value['isenlisted_fld']==2) { $bshrmEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bshrmEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bshrmL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bshrmR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bshrmL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bshrmR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bshrmL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bshrmR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bshrmL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bshrmR4+=1; }
								break;
							case "BSTM": 
								$bstm+=1; 
								if($value['isenlisted_fld']==2) { $bstmEnlisted+=1; }
								if($value['isenrolled_fld']==2) { $bstmEnrolled+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==1) { $bstmL1+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==1) { $bstmR1+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==2) { $bstmL2+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==2) { $bstmR2+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==3) { $bstmL3+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==3) { $bstmR3+=1; }
								if($value['isenlisted_fld']==2 && $value['studyrlevel_fld']==4) { $bstmL4+=1; }
								if($value['isenrolled_fld']==2 && $value['studyrlevel_fld']==4) { $bstmR4+=1; }
								break;
							default: break;
						}
						break;

					default: break;
				}
			}
			$remarks = "success";
			$message = "Successfully retrieved requested data";
		}

		

		#arrays
		//CAHS
		$cahsProgramsEnlisted = array(
			'all'=>$cahsEnlisted,'bsm'=>$bsmEnlisted, 'bsn'=>$bsnEnlisted, 
			'bsn1'=>$bsnL1, 'bsn2'=>$bsnL2, 'bsn3'=>$bsnL3, 'bsn4'=>$bsnL4, 'bsnAll'=>$bsn,
			'bsm1'=>$bsmL1, 'bsm2'=>$bsmL2, 'bsm3'=>$bsmL3, 'bsm4'=>$bsmL4, 'bsmAll'=>$bsm
		);
		$cahsProgramsEnrolled = array('all'=>$cahsEnrolled,'bsm'=>$bsmEnrolled, 'bsn'=>$bsnEnrolled, 
			'bsn1'=>$bsnR1, 'bsn2'=>$bsnR2, 'bsn3'=>$bsnR3, 'bsn4'=>$bsnR4, 
			'bsm1'=>$bsmR1, 'bsm2'=>$bsmR2, 'bsm3'=>$bsmR3, 'bsm4'=>$bsmR4
		);
		$cahs_arr = array('total'=>$cahs, 'enlisted'=>$cahsProgramsEnlisted, 'enrolled'=>$cahsProgramsEnrolled, 'R1'=>$cahsR1, 'R2'=>$cahsR2, 'R3'=>$cahsR3, 'R4'=>$cahsR4, 'L1'=>$cahsL1, 'L2'=>$cahsL2, 'L3'=>$cahsL3, 'L4'=>$cahsL4);

		//CBA
		$cbaProgramsEnlisted = array('all'=>$cbaEnlisted,'bsa'=>$bsaEnlisted, 'bsca'=>$bscaEnlisted, 'bsbafm'=>$bsbafmEnlisted, 'bsbamkt'=>$bsbamktEnlisted, 'bsbahrm'=>$bsbahrmEnlisted,'bsbahrdm'=>$bsbahrdmEnlisted, 
			'bsa1'=>$bsaL1, 'bsa2'=>$bsaL2, 'bsa3'=>$bsaL3, 'bsa4'=>$bsaL4, 'bsaAll'=>$bsa,
			'bsca1'=>$bscaL1, 'bsca2'=>$bscaL2, 'bsca3'=>$bscaL3, 'bsca4'=>$bscaL4, 'bscaAll'=>$bsca,
			'bsbafm1'=>$bsbafmL1, 'bsbafm2'=>$bsbafmL2, 'bsbafm3'=>$bsbafmL3, 'bsbafm4'=>$bsbafmL4, 'bsbafmAll'=>$bsbafm,
			'bsbamkt1'=>$bsbamktL1, 'bsbamkt2'=>$bsbamktL2, 'bsbamkt3'=>$bsbamktL3, 'bsbamkt4'=>$bsbamktL4, 'bsbamktAll'=>$bsbamkt,
			'bsbahrm1'=>$bsbahrmL1, 'bsbahrm2'=>$bsbahrmL2, 'bsbahrm3'=>$bsbahrmL3, 'bsbahrm4'=>$bsbahrmL4, 'bsbahrmAll'=>$bsbahrm,
			'bsbahrdm1'=>$bsbahrdmL1, 'bsbahrdm2'=>$bsbahrdmL2, 'bsbahrdm3'=>$bsbahrdmL3, 'bsbahrdm4'=>$bsbahrdmL4, 'bsbahrdmAll'=>$bsbahrdm
		);
		$cbaProgramsEnrolled = array('all'=>$cbaEnrolled,'bsa'=>$bsaEnrolled, 'bsca'=>$bscaEnrolled, 'bsbafm'=>$bsbafmEnrolled, 'bsbamkt'=>$bsbamktEnrolled, 'bsbahrm'=>$bsbahrmEnrolled,'bsbahrdm'=>$bsbahrdmEnrolled, 
			'bsa1'=>$bsaR1, 'bsa2'=>$bsaR2, 'bsa3'=>$bsaR3, 'bsa4'=>$bsaR4, 
			'bsca1'=>$bscaR1, 'bsca2'=>$bscaR2, 'bsca3'=>$bscaR3, 'bsca4'=>$bscaR4, 
			'bsbafm1'=>$bsbafmR1, 'bsbafm2'=>$bsbafmR2, 'bsbafm3'=>$bsbafmR3, 'bsbafm4'=>$bsbafmR4, 
			'bsbamkt1'=>$bsbamktR1, 'bsbamkt2'=>$bsbamktR2, 'bsbamkt3'=>$bsbamktR3, 'bsbamkt4'=>$bsbamktR4, 
			'bsbahrm1'=>$bsbahrmR1, 'bsbahrm2'=>$bsbahrmR2, 'bsbahrm3'=>$bsbahrmR3, 'bsbahrm4'=>$bsbahrmR4, 
			'bsbahrdm1'=>$bsbahrdmR1, 'bsbahrdm2'=>$bsbahrdmR2, 'bsbahrdm3'=>$bsbahrdmR3, 'bsbahrdm4'=>$bsbahrdmR4
		);
		$cba_arr = array('total'=>$cba, 'enlisted'=>$cbaProgramsEnlisted, 'enrolled'=>$cbaProgramsEnrolled, 'R1'=>$cbaR1, 'R2'=>$cbaR2, 'R3'=>$cbaR3, 'R4'=>$cbaR4, 'L1'=>$cbaL1, 'L2'=>$cbaL2, 'L3'=>$cbaL3, 'L4'=>$cbaL4);

		//CCS
		$ccsProgramsEnlisted = array(
			'all'=>$ccsEnlisted,'bscs'=>$bscsEnlisted, 'bsit'=>$bsitEnlisted,'bsemc'=>$bsemcEnlisted, 'act'=>$actEnlisted,
			'bscs1'=>$bscsL1, 'bscs2'=>$bscsL2, 'bscs3'=>$bscsL3, 'bscs4'=>$bscsL4, 'bscsAll'=>$bscs,
			'bsit1'=>$bsitL1, 'bsit2'=>$bsitL2, 'bsit3'=>$bsitL3, 'bsit4'=>$bsitL4, 'bsitAll'=>$bsit,
			'bsemc1'=>$bsemcL1, 'bsemc2'=>$bsemcL2, 'bsemc3'=>$bsemcL3, 'bsemc4'=>$bsemcL4, 'bsemcAll'=>$bsemc,
			'act1'=>$actL1, 'act2'=>$actL2, 'act3'=>$actL3, 'act4'=>$actL4, 'actAll'=>$act
		);
		$ccsProgramsEnrolled = array(
			'all'=>$ccsEnrolled,'bscs'=>$bscsEnrolled, 'bsit'=>$bsitEnrolled,'bsemc'=>$bsemcEnrolled, 'act'=>$actEnrolled,
			'bscs1'=>$bscsR1, 'bscs2'=>$bscsR2, 'bscs3'=>$bscsR3, 'bscs4'=>$bscsR4,
			'bsit1'=>$bsitR1, 'bsit2'=>$bsitR2, 'bsit3'=>$bsitR3, 'bsit4'=>$bsitR4,
			'bsemc1'=>$bsemcR1, 'bsemc2'=>$bsemcR2, 'bsemc3'=>$bsemcR3, 'bsemc4'=>$bsemcR4,
			'act1'=>$actR1, 'act2'=>$actR2, 'act3'=>$actR3, 'act4'=>$actR4
		);
		$ccs_arr = array('total'=>$ccs, 'enlisted'=>$ccsProgramsEnlisted, 'enrolled'=>$ccsProgramsEnrolled, 'R1'=>$ccsR1, 'R2'=>$ccsR2, 'R3'=>$ccsR3, 'R4'=>$ccsR4, 'L1'=>$ccsL1, 'L2'=>$ccsL2, 'L3'=>$ccsL3, 'L4'=>$ccsL4);



		//CEAS
		$ceasProgramsEnlisted = array(
			'all'=>$ceasEnlisted,'bacom'=>$bacomEnlisted, 'bcaed'=>$bcaedEnlisted, 'beced'=>$becedEnlisted, 'beed'=>$beedEnlisted, 'bped'=>$bpedEnlisted, 'beedgen'=>$beedgenEnlisted, 'bsedbio'=>$bsedbioEnlisted, 'bsedmapeh'=>$bsedmapehEnlisted, 'bsede'=>$bsedeEnlisted, 'bsedfil'=>$bsedfilEnlisted, 'bsedm'=>$bsedmEnlisted, 'bsedsci'=>$bsedsciEnlisted, 'bsedsoc'=>$bsedsocEnlisted, 'profed'=>$profedEnlisted,
			'bacom1'=>$bacomL1, 'bacom2'=>$bacomL2, 'bacom3'=>$bacomL3, 'bacom4'=>$bacomL4, 'bacomAll'=>$bacom,
			'bcaed1'=>$bcaedL1, 'bcaed2'=>$bcaedL2, 'bcaed3'=>$bcaedL3, 'bcaed4'=>$bcaedL4, 'bcaedAll'=>$bcaed,
			'beced1'=>$becedL1, 'beced2'=>$becedL2, 'beced3'=>$becedL3, 'beced4'=>$becedL4, 'becedAll'=>$beced,
			'beed1'=>$beedL1, 'beed2'=>$beedL2, 'beed3'=>$beedL3, 'beed4'=>$beedL4, 'beedAll'=>$beed,
			'bped1'=>$bpedL1, 'bped2'=>$bpedL2, 'bped3'=>$bpedL3, 'bped4'=>$bpedL4, 'bpedAll'=>$bped,
			'beedgen1'=>$beedgenL1, 'beedgen2'=>$beedgenL2, 'beedgen3'=>$beedgenL3, 'beedgen4'=>$beedgenL4, 'beedgenAll'=>$beedgen,
			'bsedmapeh1'=>$bsedmapehL1, 'bsedmapeh2'=>$bsedmapehL2, 'bsedmapeh3'=>$bsedmapehL3, 'bsedmapeh4'=>$bsedmapehL4, 'bsedmapehAll'=>$bsedmapeh,
			'bsede1'=>$bsedeL1, 'bsede2'=>$bsedeL2, 'bsede3'=>$bsedeL3, 'bsede4'=>$bsedeL4, 'bsedeAll'=>$bsede,
			'bsedbio1'=>$bsedbioL1, 'bsedbio2'=>$bsedbioL2, 'bsedbio3'=>$bsedbioL3, 'bsedbio4'=>$bsedbioL4, 'bsedbioAll'=>$bsedbio,
			'bsedfil1'=>$bsedfilL1, 'bsedfil2'=>$bsedfilL2, 'bsedfil3'=>$bsedfilL3, 'bsedfil4'=>$bsedfilL4, 'bsedfilAll'=>$bsedfil,
			'bsedm1'=>$bsedmL1, 'bsedm2'=>$bsedmL2, 'bsedm3'=>$bsedmL3, 'bsedm4'=>$bsedmL4, 'bsedmAll'=>$bsedm,
			'bsedsci1'=>$bsedsciL1, 'bsedsci2'=>$bsedsciL2, 'bsedsci3'=>$bsedsciL3, 'bsedsci4'=>$bsedsciL4, 'bsedsciAll'=>$bsedsci,
			'bsedsoc1'=>$bsedsocL1, 'bsedsoc2'=>$bsedsocL2, 'bsedsoc3'=>$bsedsocL3, 'bsedsoc4'=>$bsedsocL4, 'bsedsocAll'=>$bsedsoc,
			'profed1'=>$profedL1, 'profed2'=>$profedL2, 'profed3'=>$profedL3, 'profed4'=>$profedL4, 'profedAll'=>$profed
		);
		$ceasProgramsEnrolled = array(
			'all'=>$ceasEnrolled,'bacom'=>$bacomEnrolled, 'bcaed'=>$bcaedEnrolled, 'beced'=>$becedEnrolled, 'beed'=>$beedEnrolled, 'bped'=>$bpedEnrolled, 'beedgen'=>$beedgenEnrolled, 'bsedbio'=>$bsedbioEnrolled, 'bsedmapeh'=>$bsedmapehEnrolled, 'bsede'=>$bsedeEnrolled, 'bsedfil'=>$bsedfilEnrolled, 'bsedm'=>$bsedmEnrolled, 'bsedsci'=>$bsedsciEnrolled, 'bsedsoc'=>$bsedsocEnrolled, 'profed'=>$profedEnrolled,
			'bacom1'=>$bacomR1, 'bacom2'=>$bacomR2, 'bacom3'=>$bacomR3, 'bacom4'=>$bacomR4,
			'bcaed1'=>$bcaedR1, 'bcaed2'=>$bcaedR2, 'bcaed3'=>$bcaedR3, 'bcaed4'=>$bcaedR4,
			'beced1'=>$becedR1, 'beced2'=>$becedR2, 'beced3'=>$becedR3, 'beced4'=>$becedR4,
			'beed1'=>$beedR1, 'beed2'=>$beedR2, 'beed3'=>$beedR3, 'beed4'=>$beedR4,
			'bped1'=>$bpedR1, 'bped2'=>$bpedR2, 'bped3'=>$bpedR3, 'bped4'=>$bpedR4,
			'beedgen1'=>$beedgenR1, 'beedgen2'=>$beedgenR2, 'beedgen3'=>$beedgenR3, 'beedgen4'=>$beedgenR4,
			'bsedmapeh1'=>$bsedmapehR1, 'bsedmapeh2'=>$bsedmapehR2, 'bsedmapeh3'=>$bsedmapehR3, 'bsedmapeh4'=>$bsedmapehR4,
			'bsede1'=>$bsedeR1, 'bsede2'=>$bsedeR2, 'bsede3'=>$bsedeR3, 'bsede4'=>$bsedeR4,
			'bsedbio1'=>$bsedbioR1, 'bsedbio2'=>$bsedbioR2, 'bsedbio3'=>$bsedbioR3, 'bsedbio4'=>$bsedbioR4,
			'bsedfil1'=>$bsedfilR1, 'bsedfil2'=>$bsedfilR2, 'bsedfil3'=>$bsedfilR3, 'bsedfil4'=>$bsedfilR4,
			'bsedm1'=>$bsedmR1, 'bsedm2'=>$bsedmR2, 'bsedm3'=>$bsedmR3, 'bsedm4'=>$bsedmR4,
			'bsedsci1'=>$bsedsciR1, 'bsedsci2'=>$bsedsciR2, 'bsedsci3'=>$bsedsciR3, 'bsedsci4'=>$bsedsciR4,
			'bsedsoc1'=>$bsedsocR1, 'bsedsoc2'=>$bsedsocR2, 'bsedsoc3'=>$bsedsocR3, 'bsedsoc4'=>$bsedsocR4,
			'profed1'=>$profedR1, 'profed2'=>$profedR2, 'profed3'=>$profedR3, 'profed4'=>$profedR4
		);
		$ceas_arr = array('total'=>$ceas, 'enlisted'=>$ceasProgramsEnlisted, 'enrolled'=>$ceasProgramsEnrolled, 'R1'=>$ceasR1, 'R2'=>$ceasR2, 'R3'=>$ceasR3, 'R4'=>$ceasR4, 'L1'=>$ceasL1, 'L2'=>$ceasL2, 'L3'=>$ceasL3, 'L4'=>$ceasL4);

				// $bshm=0; $bshrm=0; $bstm=0;
		//CHTM
		$chtmProgramsEnlisted = array(
			'all'=>$chtmEnlisted,'bshm'=>$bshmEnlisted, 'bshrm'=>$bshrmEnlisted, 'bstm'=>$bstmEnlisted,
			'bshm1'=>$bshmL1, 'bshm2'=>$bshmL2, 'bshm3'=>$bshmL3, 'bshm4'=>$bshmL4, 'bshmAll'=>$bshm,
			'bshrm1'=>$bshrmL1, 'bshrm2'=>$bshrmL2, 'bshrm3'=>$bshrmL3, 'bshrm4'=>$bshrmL4, 'bshrmAll'=>$bshrm,
			'bstm1'=>$bstmL1, 'bstm2'=>$bstmL2, 'bstm3'=>$bstmL3, 'bstm4'=>$bstmL4, 'bstmAll'=>$bstm,
		);
		$chtmProgramsEnrolled = array(
			'all'=>$chtmEnrolled,'bshm'=>$bshmEnrolled, 'bshrm'=>$bshrmEnrolled, 'bstm'=>$bstmEnrolled,
			'bshm1'=>$bshmR1, 'bshm2'=>$bshmR2, 'bshm3'=>$bshmR3, 'bshm4'=>$bshmR4,
			'bshrm1'=>$bshrmR1, 'bshrm2'=>$bshrmR2, 'bshrm3'=>$bshrmR3, 'bshrm4'=>$bshrmR4,
			'bstm1'=>$bstmR1, 'bstm2'=>$bstmR2, 'bstm3'=>$bstmR3, 'bstm4'=>$bstmR4
		);
		$chtm_arr = array('total'=>$chtm, 'enlisted'=>$chtmProgramsEnlisted, 'enrolled'=>$chtmProgramsEnrolled, 'R1'=>$chtmR1, 'R2'=>$chtmR2, 'R3'=>$chtmR3, 'R4'=>$chtmR4, 'L1'=>$chtmL1, 'L2'=>$chtmL2, 'L3'=>$chtmL3, 'L4'=>$chtmL4);


		// test here;
		$stats_arr = array('total'=>$total, 'enlisted'=>$enlisted, 'enrolled'=>$enrolled, 'L1'=>$enlisted1, 'R1'=>$enrolled1, 'L2'=>$enlisted2, 'R2'=>$enrolled2, 'L3'=>$enlisted3, 'R3'=>$enrolled3, 'L4'=>$enlisted4, 'R4'=>$enrolled4,'cahs'=>$cahs_arr,'cba'=>$cba_arr,'ccs'=>$ccs_arr,'ceas'=>$ceas_arr,'chtm'=>$chtm_arr);
		// $enlisted_arr = array('cahs'=>$cahsEnlisted,'cba'=>$cbaEnlisted,'ccs'=>$ccsEnlisted,'ceas'=>$ceasEnlisted,'chtm'=>$chtmEnlisted);
		// $enrolled_arr = array('cahs'=>$cahsEnrolled,'cba'=>$cbaEnrolled,'ccs'=>$ccsEnrolled,'ceas'=>$ceasEnrolled,'chtm'=>$chtmEnrolled);

		// $payload = array('stats'=>$stats_arr);
		$remarks = "success";
		$message = "Successfully retrieved requested data";
		$code = 200;
		return $this->gm->api_result($stats_arr, $remarks, $message, $code);
	}
	# ./Added July 20, 2020



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
}
?>