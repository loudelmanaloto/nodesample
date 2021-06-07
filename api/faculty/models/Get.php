<?php
class Get
{
	protected $gm;

	public function __construct(\PDO $pdo)
	{
		$this->gm = new GlobalMethods($pdo);
	}



	public function getfacultyinfo($param)
	{

		$sql = "SELECT * FROM faculty_tbl WHERE empno_fld='$param' AND isdel=0";
		$res = $this->gm->execute_query($sql, 'No data found');

		if ($res['code'] == 200) {

			$payload = $res['data'];
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


	public function getArchiveClass($param)
	{
		$sql = "SELECT * FROM classes_tbl WHERE empno_fld=$param AND isdel=1";
		$res = $this->gm->execute_query($sql, 'No data found');

		if ($res['code'] == 200) {
			$payload = $res['data'];
			$remarks = "success";
			$message = "Successfully retrieved requested data";
		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}



	public function getannouncement($param, $dt)
	{


		$role = 3;
		$sql = "SELECT * FROM announcements_tbl WHERE recipientcode_fld='$dt->param1' OR recipientcode_fld='$dt->param2' OR recipientcode_fld='$param' AND isdel=0 ORDER BY timestamp_fld DESC";
		$res = $this->gm->execute_query($sql, 'No data found');


		// $tmp_announcement = $res['data'];
		// $len = count($tmp_announcement);
		// $record = array();
		// $data = [];

		// for($i = 0; $i<$len; $i++){
		// 	$data[$tmp_announcement[$i]['recipientcode_fld']] = $record; 
		// }

		// $options = array();

		// switch ($role) {
		// 	case '2':
		// 		# code...
		// 		$options = array("0"=>"CS");
		// 		break;
		// 	case '3':
		// 		$options = array(
		// 			"0"=>"CCS",
		// 			"1"=>"CS"
		// 		);

		// 	break;

		// 	case '4':
		// 		$options = array("0"=>"ALL");
		// 	break;
		// 	default:
		// 		# code...
		// 		break;
		// }
		// $options = array(
		// 	"1"=>"ALL",
		// 	"2"=>"CCS",
		// 	"3"=>"CS"	
		// );


		// foreach ($data as $key => $value) {
		// 	for($i=0; $i<$len; $i++){
		// 		$val = $tmp_announcement[$i]; 
		// 		if($key===$val['recipientcode_fld']){
		// 			array_push($data[$key], array("recno_fld"=>$val['recno_fld'],
		// 										  "announcecode_fld"=>$val['announcecode_fld'],
		// 										  "title_fld"=>$val['title_fld'],
		// 										  "content_fld"=>$val['content_fld'],
		// 										  "withimg_fld"=>$val['withimg_fld'],
		// 										  "imgdir_fld"=>$val['imgdir_fld'],					
		// 										  "timestamp_fld"=>$val['timestamp_fld'],
		// 										  "isdel"=>$val['isdel']));
		// 		}
		// 	}
		// }

		if ($res['code'] == 200) {

			$payload = array("announcements" => $res['data']);
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



	public function getforumcategory()
	{
		$sql = "SELECT * FROM forums_tbl WHERE isdel=0 ORDER BY forumtitle_fld";
		$res = $this->gm->execute_query($sql, "No records found");
		if ($res['code'] == 200) {
			$payload = $res['data'];
			$remarks = "success";
			$message = "Successfully retrieved requested data";
		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}

	public function getsubforum($param)
	{

		$comments = null;
		$sql = "SELECT * FROM subforum_tbl WHERE code_fld=$param AND isdel=0 ORDER BY subtitle_fld";
		$res = $this->gm->execute_query($sql, "No content found");

		if ($res['code'] == 200) {


			for ($i = 0; $i < count($res['data']); $i++) {
				# code...
				$res['data'][$i]['contentcount'] = 0;
			}

			$com = $this->getforumcontent($param);
			if ($com['payload'] == null) {
				$comments = null;
			} else {

				foreach ($com['payload'] as $key => $value) {

					$commentctr = 0;
					$commentctr = count($com['payload'][$key]);
					for ($i = 0; $i < count($res['data']); $i++) {
						if ($key == $res['data'][$i]['subcode_fld']) {
							$res['data'][$i]['contentcount'] = $commentctr;
						}
					}
				}
				$comments = $com['payload'];
			}


			$payload = array("subforum" => $res['data']);
			// $payload = array("subforum"=>$res['data'], "subcontent"=>$com['payload']);
			$remarks = "success";
			$message = "Successfully retrieved requested data";
		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}

	public function getforumcontent($param)
	{

		$sql = "SELECT * FROM forumcontent_tbl WHERE subcode_fld='$param'";

		$res = $this->gm->execute_query($sql, "No content found");


		if ($res['code'] == 200) {

			$payload = array("forumcontent" => $res['data']);
			$remarks = "success";
			$message = "Successfully retrieved requested data";
		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}




	public function getclass($param)
	{

		$array = array("class" => array(), "archived" => array());


		$sql = "SELECT * FROM classes_tbl WHERE empno_fld= '$param' ORDER BY desc_fld ASC";

		$res = $this->gm->execute_query($sql, 'Failed to get data');




		if ($res['code'] == 200) {

			$data = $res['data'];
			for ($i = 0; $i < count($data); $i++) {
				if ($data[$i]['isdel'] == 0) {
					array_push($array['class'], $data[$i]);
				}
				if ($data[$i]['isdel'] == 1) {
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


	public function getclassdetails($param)
	{

		$sql = "SELECT * FROM classpost_tbl WHERE classcode_fld=$param AND isdel=0 ORDER BY timestamp_fld DESC";

		$res = $this->gm->execute_query($sql, 'No post found');
		$classlist = $this->getclasslist($param);


		if ($res['code'] == 200) {

			$comments = null;
			for ($i = 0; $i < count($res['data']); $i++) {
				# code...
				$res['data'][$i]['commentcount'] = 0;
			}


			$com = $this->getcomments($param);

			if ($com['payload'] == null) {
				$comments = null;
			} else {

				foreach ($com['payload'] as $key => $value) {

					$commentctr = 0;
					$commentctr = count($com['payload'][$key]);
					for ($i = 0; $i < count($res['data']); $i++) {
						if ($key == $res['data'][$i]['postcode_fld']) {
							$res['data'][$i]['commentcount'] = $commentctr;
						}
					}
				}
				$comments = $com['payload'];
			}


			$payload = array("classpost" => $res['data'], "comments" => $comments, "classlist" => $classlist['payload']);
			$code = 200;
			$remarks = "success";
			$message = "Successfully retrieved requested data";
		} else {
			$payload = array("classpost" => null, "comments" => null, "classlist" => $classlist['payload']);
			$remarks = "failed";
			$message = $res['errmsg'];
		}

		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}


	public function getclasslist($param)
	{
		$sql = "SELECT faculty_tbl.*  FROM classes_tbl INNER JOIN faculty_tbl ON classes_tbl.empno_fld=faculty_tbl.empno_fld WHERE  classes_tbl.isdel = 0 AND classes_tbl.classcode_fld='$param' ORDER BY faculty_tbl.fname_fld ASC;";


		$res = $this->gm->execute_query($sql, 'Failed to get data');

		if ($res['code'] == 200) {

			$stud = $this->getstudentlist($param);

			$payload = array("teacher" => $res['data'], "students" => $stud);
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

	public function getstudentlist($param)
	{
		$sql = "SELECT enrolled_tbl.*, students_tbl.fname_fld, students_tbl.lname_fld, students_tbl.mname_fld, students_tbl.nameext_fld, students_tbl.img_fld FROM enrolled_tbl INNER JOIN students_tbl ON enrolled_tbl.studnum_fld=students_tbl.studnum_fld WHERE classcode_fld='$param' AND enrolled_tbl.isdel=0 ORDER BY students_tbl.fname_fld ASC";


		$res = $this->gm->execute_query($sql, 'Failed to get data');

		if ($res['code'] == 200) {

			$payload = $res['data'];
			$code = 200;
			$remarks = "success";
			$message = "Successfully retrieved requested data";
		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $payload;
	}

	public function getresource($param)
	{
		$sql = "SELECT * FROM resource_tbl WHERE isdel=0 AND classcode_fld='$param'";
		$res = $this->gm->execute_query($sql, "No records found");

		if ($res['code'] == 200) {
			$payload = $res['data'];
			$remarks = "success";
			$message = "Successfully retrieved requested data";
		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}

	public function getactivitydetails($param)
	{

		$sql = "SELECT * FROM activity_tbl WHERE recipient_fld LIKE '%$param%' OR classcode_fld=$param AND isdel=0 ORDER BY actdate_fld DESC";

		$res = $this->gm->execute_query($sql, 'No data found');


		if ($res['code'] == 200) {

			$comments = null;
			for ($i = 0; $i < count($res['data']); $i++) {
				# code...
				$res['data'][$i]['commentcount'] = 0;
			}


			$com = $this->getcomments($param);

			if ($com['payload'] == null) {
				$comments = null;
			} else {

				foreach ($com['payload'] as $key => $value) {

					$commentctr = 0;
					$commentctr = count($com['payload'][$key]);
					for ($i = 0; $i < count($res['data']); $i++) {
						if ($key == $res['data'][$i]['postcode_fld']) {
							$res['data'][$i]['commentcount'] = $commentctr;
						}
					}
				}
				$comments = $com['payload'];
			}

			$payload = array("activity" => $res['data'], "comments" => $comments);
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


	public function getsubmit($param)
	{
		$sql = "SELECT submissions_tbl.*, students_tbl.fname_fld, students_tbl.lname_fld, students_tbl.img_fld FROM submissions_tbl INNER JOIN students_tbl ON submissions_tbl.studnum_fld=students_tbl.studnum_fld WHERE submissions_tbl.isdel=0  AND actcode_fld='$param' ";

		$res = $this->gm->execute_query($sql, 'Failed to get data');

		if ($res['code'] == 200) {

			$payload = $res['data'];
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



	public function getcomments($param)
	{
		$sql = "SELECT * FROM classcomments_tbl WHERE isdel=0  AND source_fld='$param' OR postcode_fld='$param' ";

		$res = $this->gm->execute_query($sql, 'No data found');


		if ($res['code'] == 200) {

			$tmp_comment = $res['data'];
			$len = count($tmp_comment);
			$record = array();
			$data = [];

			for ($i = 0; $i < $len; $i++) {
				$data[$tmp_comment[$i]['postcode_fld']] = $record;
			}


			foreach ($data as $key => $value) {
				for ($i = 0; $i < $len; $i++) {
					$val = $tmp_comment[$i];
					if ($key == $tmp_comment[$i]['postcode_fld']) {
						array_push($data[$key], array(
							"recno_fld" => $val['recno_fld'],
							"commentcode_fld" => $val['commentcode_fld'],
							"senderid_fld" => $val['senderid_fld'],
							"sendername_fld" => $val['sendername_fld'],
							"content_fld" => $val['content_fld'],
							"timestamp_fld" => $val['timestamp_fld'],
							"isdel" => $val['isdel']
						));
					}
				}
			}



			$payload = $data;
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

	public function gettopic($param)
	{
		$sql = "SELECT * FROM topic_tbl WHERE classcode_fld='$param'";

		$res = $this->gm->execute_query($sql, "No records found");
		if ($res['code'] == 200) {
			$payload = $res['data'];
			$remarks = "success";
			$message = "Successfully retrieved requested data";
		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}
	
	
	##September 13, 2020
	public function getacomments($param){
		
		$sql = "SELECT * FROM classcomments_tbl WHERE postcode_fld='$param' AND isdel=0";

	    $res = $this->gm->execute_query($sql, "No records found");
		if ($res['code'] == 200) {
			$payload = $res['data'];
			$remarks = "success";
			$message = "Successfully retrieved requested data";
		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}   
	##


	// public function getquiz($param){
	// 	$sql = "SELECT * FROM quiz_tbl WHERE classcode_fld=$param";

	// 	$res = $this->gm->execute_query($sql, "No quiz found");
	// 	$quest = $this->getquestion($res['data'][0]['quizid_fld']);

	// 	$load = $res['data'][0]+array("questions"=>$quest['payload']);


	// 	if($res['code']==200){
	// 		$payload = $load;
	// 		$remarks = "success";
	// 		$message = "Successfully retrieved requested data"; 
	// 	}
	// 	else{
	// 		$payload = null;
	// 		$remarks = "failed";
	// 		$message = $res['errmsg'];
	// 	}
	// 	// return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	// }

	// public function questiontype($param){
	// 	switch ($param) {
	// 		case '1':
	// 			# code...
	// 			return 'Multiple Choice';
	// 			break;
	// 		case '2';
	// 			return 'True or False';
	// 		break;	

	// 		default:
	// 			# code...
	// 			return 'Bad request';
	// 			break;
	// 	}
	// }

	// public function getquestion($param){
	// 	$sql = "SELECT questionid_fld, questionid_fld, questionname_fld, questiontype_fld FROM questions_tbl WHERE quizid_fld=$param";
	// 	$res = $this->gm->execute_query($sql, "No question found");
	// 	$answer = $this->getquizoption($param);

	// 	$optionsarray = array();

	// 	$load = $res['data'];
	// 	$loadoptions = $answer['payload'];
	// 	for($i=0; $i<count($load); $i++){
	// 		$load[$i]['questiontype_fld'] = $this->questiontype($load[$i]['questiontype_fld']);	

	// 		for($k=0; $k<count($loadoptions); $k++){

	// 			if($load[$i]['questionid_fld'],$loadoptions[$k]['questionid_fld']){	
	// 				print_r('true');	
	// 				$load[$i]['options'] = array_push($optionsarray, $loadoptions[$k]);
	// 			}
	// 		}
	// 	}



	// 	if($res['code']==200){
	// 		$payload = $load;
	// 		$remarks = "success";
	// 		$message = "Successfully retrieved requested data"; 
	// 	}
	// 	else{
	// 		$payload = null;
	// 		$remarks = "failed";
	// 		$message = $res['errmsg'];
	// 	}
	// 	// return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	// }

	// public function getquizoption($param){
	// 	$sql = "SELECT * FROM options_tbl WHERE quizid_fld=$param";

	// 	$res = $this->gm->execute_query($sql, "No type found");

	// 	if($res['code']==200){
	// 		$payload = $res['data'];
	// 		$remarks = "success";
	// 		$message = "Successfully retrieved requested data"; 
	// 	}
	// 	else{
	// 		$payload = null;
	// 		$remarks = "failed";
	// 		$message = $res['errmsg'];
	// 	}
	// 	return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	// }










}
