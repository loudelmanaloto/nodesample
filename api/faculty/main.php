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
// 	// $req[2] = base64_encode($req[2]);
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
				echo response($auth->faculty_login($d));
				break;

			case "forgotpassword":
				$d = jd(file_get_contents("php://input"));
				echo response($auth->requestpassword($d));
				break;

			case "changepassword":
				$d = jd(file_get_contents("php://input"));
				// echo je($auth->changepass($d));
				echo response($auth->changepass($d));
				break;

				########################################
				# 	END OF LOGIN MODULE
				########################################

				########################################
				# 	START OF FACULTY MODULE
				########################################

			case "facultyinfo":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getfacultyinfo($param));
				} else {
					echo errMsg(401);
				}
				break;

			case 'getresource':
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getresource($param));
				}
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
					// echo je($get->getannouncement($param, $d));
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
					// echo je($get->getforumcategory());
					echo response($get->getforumcategory());
				} else {
					echo errMsg(401);
				}
				break;

			case "subforum":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					// echo je($get->getsubforum($param));
					echo response($get->getsubforum($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "forumcontent":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					// echo je($get->getforumcontent($param));
					echo response($get->getforumcontent($param));
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

            ## Added September 13, 2020
            case "getactivitycomments":
		        if($auth->authorized()){
			    $pcode = bd($req[1]);
			    echo response($get->getacomments($pcode));
		        }
		        else{
		    	echo errMsg(401);
		        }		
	       break;

	        case "deletecomment":
	    	$d = jd(file_get_contents("php://input"));
	    	
		    if($auth->authorized()){
			    $pcode = bd($req[1]);
			    $ccode = bd($req[2]);
			    echo response($post->deletecomment($pcode, $ccode,  $d));
		    }
		    else{
		    	echo errMsg(401);
		    }		
	        break;
	        
	        case "addactivitycomment":
		    $d = jd(file_get_contents("php://input"));
		    if($auth->authorized()){
		    	$table = bd($req[1]);
		    	echo response($post->addcomment($table.'_tbl', $d));
	    	}
	    	else{
		    	echo errMsg(401);
	    	}		
        	break;
            ##


			case "classes":

				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getclass($param));
					// echo je($get->getclass($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "classinfo":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					// echo je($get->getclassdetails($param));
					echo response($get->getclassdetails($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "classlist":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getclasslist($param));
					// echo je($get->getclasslist($param));

				} else {
					echo errMsg(401);
				}
				break;

			case "activityinfo":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					// echo je($get->getactivitydetails($param));
					echo response($get->getactivitydetails($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "comments":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					// echo je($get->getcomments($param));
					echo response($get->getcomments($param));
				} else {
					echo errMsg(401);
				}
				break;

			case "topic":
				if ($auth->authorized()) {
					$param = bd($req[1]);
					// echo je($get->gettopic($param));
					echo response($get->gettopic($param));
				} else {
					echo errMsg(401);
				}
				break;

			case 'submits':
				if ($auth->authorized()) {
					$param = bd($req[1]);
					echo response($get->getsubmit($param));
					// echo je($get->getsubmit($param));
				}
				break;
				########################################
				# 	END OF CLASS MODULE
				########################################

			case "uploadfile":
				if ($auth->authorized()) {
					$ecode = bd($req[1]);
					$option = bd($req[2]);
					$classcode = bd($req[3]);
					
					echo response($post->process_upload($ecode, $classcode, $option));
					
				} else {
					echo errMsg(401);
				}
				break;

				#######################################
				# CRUD METHODS
				#######################################

			case "add":
				// $d = jd(bd(file_get_contents("php://input")));
				$d = jd(file_get_contents("php://input"));
				if ($auth->authorized()) {
					$param = bd($req[1]);
					// echo je($post->add($d, $param . '_tbl'));
					echo response($post->add($d, $param . '_tbl'));
					// echo je($param);
				} else {
					echo errMsg(401);
				}

				break;

			case "edit":
				$d = jd(file_get_contents("php://input"));
				if ($auth->authorized()) {
					$table = bd($req[1]);
					// echo je($post->edit($d, $table . '_tbl'));
					echo response($post->edit($d, $table . '_tbl'));
				} else {
					echo errMsg(401);
				}
				break;

			case "delete":
				$d = jd(file_get_contents("php://input"));
				if ($auth->authorized()) {
					echo je($post->delete($d, $req[1] . '_tbl'));
				} else {
					echo errMsg(401);
				}
				break;

				###############QUIZ MODULES######################

				// // case "quiz":
				// // 	if($auth->authorized()){
				// // 		$param = bd($req[1]);
				// // 		echo je($get->getquiz($param));
				// // 	}
				// // 	else{
				// // 		echo errMsg(401);
				// // 	}
				// break;

			default:
				echo errMsg(400);
				break;
		}
		break;

	default:
		echo errMsg(403);
		break;
}
