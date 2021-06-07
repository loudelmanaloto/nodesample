<?php
class Post
{
	protected $gm;
	protected $pdo;
	protected $get;

	public function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
		$this->gm = new GlobalMethods($pdo);
		$this->get = new Get($pdo);
	}


	#####################################
	# ANNOUNCEMENTS METHODS
	####################################


	public function codegenerator($table)
	{
		$y = new DateTime();
		$codestart = "";
		$filter = "";
		// $fld;

		switch ($table) {

			case 'announcements_tbl':
				# code...
				$codestart = "AN";
				$filter = "announcecode_fld";

				break;
			case 'classpost_tbl':
				# code...
				$codestart = "CP";
				$filter = "postcode_fld";

				break;
			case 'classcomments_tbl':
				# code...
				$codestart = "CC";
				$filter = "commentcode_fld";

				break;
			case 'activity_tbl':
				# code...
				$codestart = "AC";
				$filter = "postcode_fld";

				break;
			case 'topic_tbl':
				# code...
				$codestart = "T";
				$filter = "topiccode_fld";

				break;
			case 'forumcontent_tbl':
				# code...
				$codestart = "SUB";
				$filter = "code_fld";
				break;
			case 'resource_tbl':
				$codestart = "RS";
				$filter = "rescode_fld";
				break;
			default:
				# code...
				break;
		}
		$codestart .= $y->format('Y');
		$sql = "SELECT COUNT(recno_fld) FROM $table WHERE $filter LIKE '$codestart%'";
		$res = $this->gm->execute_query($sql, "Unable to count the records");
		$ordercode = $codestart . str_pad($res['data'][0]['COUNT(recno_fld)'] + 1, 5, "0", STR_PAD_LEFT);
		$val[$filter] = $ordercode;
		return $val;
	}



	public function getdata($dt, $table, $type)
	{
		$action = "";

		if($type==1){
			$action = "Added new ";
		}
		if($type==2){
			$action = "Updated/Archived ";
		}
		switch ($table) {

			case 'announcements_tbl':
				# code...
				return $this->get->getannouncement('ALL', $dt);
				break;
			case 'classpost_tbl':
				# code...
				$this->gm->facultylog($dt->empnum, $dt->fullname, $action."post for class ".$dt->param1);
				return $this->get->getclassdetails($dt->param1);
				break;
			case 'classcomments_tbl':
				# code...
			    $this->gm->facultylog($dt->empnum, $dt->fullname, $action."post for class ".$dt->param1);
				return $this->get->getclassdetails($dt->param1);
				break;
			case 'activity_tbl':
				# code...
				if($action==1){
					$this->gm->facultylog($dt->empnum, $dt->fullname, $action."activty for ".$dt->recipient." students in class ".$dt->param1."");
				}
				else{	
					$this->gm->facultylog($dt->empnum, $dt->fullname, $action."activty "."in class ".$dt->param1."");
				}
			    
				return $this->get->getactivitydetails($dt->param1);
				break;
			case 'resource_tbl';			
				$this->gm->facultylog($dt->empnum, $dt->fullname, $action."resource for class ".$dt->param1);
				return $this->get->getresource($dt->param1);
				break;	
			case 'topic_tbl':
				# code...
				return $this->get->gettopic($dt->param1);
				break;
			case 'classes_tbl':
				return $this->get->getclass($dt->param1);
				break;
			case 'faculty_tbl':
				return $this->get->getfacultyinfo($dt->param1);
				break;
			case 'students_tbl':
				return $this->get->getstudentinfo($dt->param1);
				break;
			case 'forumcontent_tbl':
				return $this->get->getforumcontent($dt->param1);
				break;
			case 'submissions_tbl':
				return $this->get->getsubmit($dt->param1);
				break;
			default:
				# code...
				break;
		}
	}

	



	public function add($dt, $table)
	{
		$val =  $this->codegenerator($table);
		$data = array_merge((array)$dt->CONTENT, $val);

		$res = $this->gm->insert($table, $data);

		if ($res['code'] == 200) {

			return $this->getdata($dt->PARAM, $table, 1);
		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}

		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}

	public function condString($table, $dt)
	{
		switch ($table) {
			case 'classes_tbl':
				return "classcode_fld = '$dt->classcode_fld'";
				break;
			case 'faculty_tbl':
				return "empno_fld = '$dt->empno_fld'";
				break;
			case 'students_tbl':
				return "studnum_fld = '$dt->studnum_fld'";
				break;
			case 'announcements_tbl':
				return "announcecode_fld = '$dt->announcecode_fld'";
				break;
			case 'submissions_tbl':
				return "actcode_fld = '$dt->actcode_fld' AND studnum_fld = '$dt->studnum_fld'";
				break;
			case 'activity_tbl':
					return "postcode_fld = '$dt->postcode_fld'";
				break;
			/*  Added Sept 13 2020 3:32PM - Owen Jasper Vargas ADDED DELETE CLASSPOST */
			case 'classpost_tbl':
				return "postcode_fld = '$dt->postcode_fld'";
				break;
			case 'classcomments_tbl':
				return "commentcode_fld  = '$dt->commentcode_fld'";
				break;
			/*  END OF MODIFICATION */
			default:
				# code...
				break;
		}
	}

	public function edit($dt, $table)
	{

		// return $this->gm->api_result($dt, 'try', 'return data', 200);
		$condition = $this->condString($table, $dt->CONTENT);

		$res = $this->gm->update($table, $dt->CONTENT, $condition);
		if ($res['code'] == 200) {
			return $this->getdata($dt->PARAM, $table, 2);
		} else {
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}
		return $this->gm->api_result($payload, $remarks, $message, $res['code']);
	}

	public function delete($param, $module)
	{
	}

	
	public function process_upload($ecode, $classcode, $option) {
		$code = 403;
		$file = $_FILES['file']['name']	;
	    $temp_file = $_FILES['file']['tmp_name'];
	    $fullname = bd($_POST['fullname']);
	    $data = jd(bd($_POST['data']));
	    $action = bd($_POST['action']);

	    $res = file_get_contents('php://input');


		$target_path = "../../personnel/$ecode/";

		if($option==2){
			$target_path = "../uploads/faculty/$ecode/$classcode/resources/";
		}
		if($option==3){
			$target_path = "../uploads/announcements/";
		}

		if (!is_dir($target_path)) {
			mkdir($target_path, 0777, true);
		}

		$target_path = $target_path . basename($file);
		
		$path_parts = pathinfo($target_path);


		if($option==1){
			// $target_path = $path_parts['dirname'].'/profile.'.$path_parts['extension'];
			// $path_parts['filename'] = 'profile';
			// $path_parts['basename'] = 'profile.png';

		}



		if (move_uploaded_file($temp_file, $target_path)) {
			
			header('Content-type: application/json');
		
			if($option==1){
				$filepath = substr($target_path, 6);
			}
			else{
				$filepath = substr($target_path, 3);

			}

			// $payload = array("filepath"=>$filepath, "filename"=>$path_parts);
			// $remarks = "Success";
			// $message = "Upload and move success";
			// $code = 200;

			$this->gm->facultylog($ecode, $fullname, 'Successfully uploaded file '.basename($file).' in '.$classcode);

			if($action!=null||$action!='undefined'){
				switch($action){
					case 'addassign':
						$data->CONTENT->filedir_fld = $filepath;
						return $payload = $this->add($data, 'activity_tbl');
					break;
					case 'addresource':
						$data->CONTENT->filedir_fld = $filepath;
						return $this->add($data, 'resource_tbl');
					break;
					case 'editassign':
					$payload = 'Edit Assign HERE';
					break;
					case 'editresource':
					$payload = 'Edit Resource HERE';
					break;
					default:
					break;
				}
			}
		
			// return $this->gm->api_result($payload, $message, $remarks, $code);
		} 

		else {
			$payload = array("filepath"=>null, "filename"=>null);
			$remarks = "Failed";
			$message = "There was an error uploading the file, please try again!";

			$this->gm->facultylog($ecode, $fullname, 'Failed to upload file '.basename($file).' in '.$classcode);
			

			return $this->gm->api_result($payload, $message, $remarks, $code);
		}
	}
    






    ##Added September 13, 2020

	public function deletecomment($pcode, $ccode, $data){
		$res = $this->gm->update("classcomments_tbl", $data, "commentcode_fld= '$ccode' ");
		
		if($res['code']==200){
			return $this->get->getacomments($pcode);
		}

		return array("payload"=>null, "remarks"=>"Failed", 
			"message"=>"Failed to update", $res['code']);

	}
	
	public function addcomment($table, $dt){

		$val =  $this->codegenerator($table);


		$data = array_merge((array)$dt->CONTENT, $val);

		$res = $this->gm->insert($table, $data);

		if($res['code']==200){				

			return $this->get->getacomments($dt->PARAM->param1);
		}
		else{
			$payload = null;
			$remarks = "failed";
			$message = $res['errmsg'];
		}

		return $this->gm->api_result($payload, $remarks, $message, $res['code']);

	}

	##September 21, 2020




}
