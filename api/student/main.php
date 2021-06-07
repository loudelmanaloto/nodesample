<?php
require_once "./config/Connection.php";
require_once "./models/Global.php";
require_once "./models/Get.php";
require_once "./models/Auth.php";
require_once "./models/Procedural.php";
require_once "./models/Post.php";

$db = new Connection();
$pdo = $db->connect();

$auth = new Auth($pdo);
$get = new Get($pdo);
$post = new Post($pdo);

if (isset($_REQUEST['request'])) {
	$req = explode('/', rtrim($_REQUEST['request'], '/'));
} else {
	$req = array("errorcatcher");
}


// $req[0] = base64_encode($req[0]);
// if (count($req) > 1) {
// 	$req[1] = base64_encode($req[1]);
// 	$req[2] = base64_encode($req[2]);
// }

switch ($_SERVER['REQUEST_METHOD']) {
	case "POST":
		switch (bd($req[0])) {


				########################################
				# 	GET METHODS
				########################################


				########################################
				# 	LOGIN MODULE
				########################################

			case "login":
				$d = jd(file_get_contents("php://input"));
				// $d = jd(bd(file_get_contents("php://input")));
				echo response($auth->student_login($d));

				break;

			case "forgotpassword":
				// $d = jd(bd(file_get_contents("php://input")));
				$d = jd(file_get_contents("php://input"));
				echo response($auth->requestpassword($d));
				break;

			case "changepassword":
				$d = jd(file_get_contents("php://input"));
				echo response($auth->changepass($d));
				break;



				########################################
				# 	END OF LOGIN MODULE
				########################################

				########################################
				# 	START OF FACULTY MODULE
				########################################


			case "studentinfo":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getstudentinfo($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "forgotpassword":
				// $d = jd(bd(file_get_contents("php://input")));
				$d = jd(file_get_contents("php://input"));
				echo response($auth->requestpassword($d));
				break;

			case "changepassword":
				$d = jd(file_get_contents("php://input"));
				// echo response($auth->changepassword($d));
				break;

				########################################
				# 	END OF FACULTY MODULE
				########################################

				########################################
				# 	START OF ANNOUNCEMENTS MODULE
				########################################

			case "announcements":
				// $d = jd(bd(file_get_contents("php://input")));	
				$d = jd(file_get_contents("php://input"));
				if ($auth->authorized()) {
					$param = bd($req[1]);
					// echo response($get->getannouncement($param, $d));
					echo response($get->getannouncement($param, $d));
				} else {
					echo errMsg(401);
				}
				break;

				########################################
				# 	END OF ANNOUNCEMENTS MODULE
				########################################

				########################################
				# 	START OF FORUM MODULE
				########################################

			case "forum":
				if ($auth->authorized()) {
					echo response($get->getforumcategory());
				} else {
					echo errMsg(401);
				}
				break;



			case "subforum":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getsubforum($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "forumcontent":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getforumcontent($param));
					// echo jd($get->getforumcontent($param));
				} else {
					echo errMsg(401);
				}
				break;



				########################################
				# 	END OF FORUM MODULE
				########################################


				########################################
				# 	START OF CLASS MODULE
				########################################

			case "classes":

				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getclass($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "classinfo":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getclassdetails($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "studentlist":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getstudentlist($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "activityinfo":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getactivitydetails($param));
				} else {
					echo errMsg(401);
				}
				break;


			case "comments":
				if ($auth->authorized()) {
					$param = bd($req[1]);

					echo response($get->getcomments($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "classlist":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getclasslist($param));
				}
				break;

			case 'classworks';
	        	if($auth->authorized()){
		    	echo response($get->getstudentworks(bd($req[1]), bd($req[2])));	
	        	}
		        else{
		        	echo errMsg(401);
		        }
	        break;

			case "getsubmit":
				if ($auth->authorized()) {
					$param1 = bd($req[1]);
					$param2 = bd($req[2]);
					echo response($get->getsubmit($param1, $param2));
					// echo je($get->getsubmit($param1, $param2));
				}
				break;

			case "submitactivty":
				$d = jd(file_get_contents("php://input"));
				if ($auth->authorized()) {
					$studid = bd($req[1]);
					echo response($get->getsubmit($studid, $d));
				} else {
					echo errMsg(401);
				}
				break;

			case 'getresource':
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getresource($param));
				} else {
					echo errMsg(401);
				}
				break;

				########################################
				# 	END OF CLASS MODULE
				########################################

				###########upload files
			case "uploadfile":
				if ($auth->authorized()) {
					$option = bd($req[1]);
					$studid = bd($req[2]);
					$teacherid = null;
					$subjectcode = null;
					if ($option == 2) {
						$teacherid = bd($req[3]);
						$subjectcode = bd($req[4]);
					}

					echo response($post->process_upload($option, $studid, $teacherid, $subjectcode));
					// echo je($post->process_upload($param, $option, $ccode, $acode));
				} else {
					echo errMsg(401);
				}
				break;


				#######################################
				# CUD METHODS
				#######################################	

			case "add":
				$d = jd(file_get_contents("php://input"));

				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($post->add($d, $param . '_tbl'));
					// echo je($post->add($d, $param . '_tbl'));
				} else {
					echo errMsg(401);
				}
				break;

			case "edit":
				$d = jd(file_get_contents("php://input"));
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($post->edit($d, $param . '_tbl'));
				} else {
					echo errMsg(401);
				}
				break;

			case "delete":
				$d = jd(file_get_contents("php://input"));
				if ($auth->authorized()) {

					echo response($post->delete($d, $req[1] . '_tbl'));
				} else {
					echo errMsg(401);
				}
				break;


			default:
				echo errMsg(400);
				break;
		}
		break;

	default:
		echo errMsg(403);
		break;
}
