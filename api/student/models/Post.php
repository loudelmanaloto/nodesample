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
		$fld = null;

		switch ($table) {


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
			case 'submissions_tbl':
				$codestart = "SB";
				$filter = "submitcode_fld";
				break;
			case 'forumcontent_tbl':
				$codestart = "SUB";
				$filter = "code_fld";
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

	public function getdata($dt, $table)
	{
		switch ($table) {

			case 'announcements_tbl':
				# code...
				return $this->get->getannouncement('ALL', $dt);
				break;
			case 'classpost_tbl':
				# code...
				return $this->get->getclassdetails($dt->param1);
				break;
			case 'classcomments_tbl':
				# code...
				return $this->get->getcomments($dt->param1);
				break;
			case 'activity_tbl':
				# code...
				return $this->get->getactivitydetails($dt->param1);
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
			case 'submissions_tbl':
				return $this->get->getsubmit($dt->param1, $dt->param2);
				break;
			case 'forumcontent_tbl':
				return $this->get->getforumcontent($dt->param1);
				break;
			case 'resource_tbl';
				return $this->get->getresource($dt->param1);
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
			return $this->getdata($dt->PARAM, $table);
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
			case 'students_tbl':
				return "studnum_fld = '$dt->studnum_fld'";
				break;
			case 'submissions_tbl':
				return "submitcode_fld = '$dt->submitcode_fld'";
				break;
			case 'classpost_tbl':
					return "postcode_fld = '$dt->postcode_fld'";
					break;
			/*  Added Sept 09 2020 6:55PM - Owen Jasper Vargas 
			ADDED DELETE ACTIVITIES COMMENTS */
			case 'classcomments_tbl':
				return "commentcode_fld  = '$dt->commentcode_fld'";
				break;
			default:
				# code...
				break;
		}
	}

	public function edit($dt, $table)
	{
		$condition = $this->condString($table, $dt->CONTENT);

		$res = $this->gm->update($table, $dt->CONTENT, $condition);
		if ($res['code'] == 200) {
			return $this->getdata($dt->PARAM, $table);
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

	public function process_upload($option, $studid, $teacherid, $scode)
	{



		$file = $_FILES['file']['name'];
		$temp_file = $_FILES['file']['tmp_name'];

		$target_path = "../uploads/students/$studid/";

		if ($option == 2) {
			// $target_path = "../uploads/faculty/$studid/submissions/$scode/";
			$target_path = "../uploads/faculty/$teacherid/$scode/submissions/$studid/";
		}

		if (!is_dir($target_path)) {
			mkdir($target_path, 0777, true);
		}

		$target_path = $target_path . basename($file);

		$path_parts = pathinfo($target_path);


		if ($option == 1) {

			$target_path = $path_parts['dirname'] . '/profile.' . $path_parts['extension'];
		}



		if (move_uploaded_file($temp_file, $target_path)) {
			header('Content-type: application/json');
			$message = 'Upload and move success';
			$filepath = substr($target_path, 3);

			if ($option == 1) {
				$sql = "UPDATE faculty_tbl SET img_fld = '$filepath' WHERE empno_fld='$studid'";
				$this->gm->execute_query($sql, '');
				$message = 'Uploaded file and move success';
			}

			$data = ['filepath' => $filepath, 'success' => true, 'message' => $message];
			return $data;
		} else {
			$data = ['filepath' => null, 'success' => false, 'message' => 'There was an error uploading the file, please try again!'];
			return $data;
		}
	}
}
