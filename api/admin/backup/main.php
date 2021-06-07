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

switch ($_SERVER['REQUEST_METHOD']) {
case "POST":
	switch (base64_decode($req[0])) {

	########################################
	# 	GET METHODS
	########################################
	case "loginstudent":
		$d = jd(base64_decode(file_get_contents("php://input")));
		echo response($auth->student_login($d));
	break;

	case "loginadmin":
		$d = jd(base64_decode(file_get_contents("php://input")));
		echo response($auth->faculty_login($d));
	break;

	case "getregions":
		if ($auth->authorized()) {
			echo response($get->get_reference("refregion_tbl", null));
		} else {
			echo errMsg(401);
		}
	break;

	case "getprovince":
		if ($auth->authorized()) {
			echo response($get->get_reference("refprovince_tbl", "regCode='".$req[1]."'"));
		} else {
			echo errMsg(401);
		}
	break;

	case "getcitymun":
		if ($auth->authorized()) {
			echo response($get->get_reference("refcitymun_tbl", "provCode='".$req[1]."'"));
		} else {
			echo errMsg(401);
		}
	break;

	case "getbrgy":
		if ($auth->authorized()) {
			echo response($get->get_reference("refbrgy_tbl", "citymunCode='".$req[1]."'"));
		} else {
			echo errMsg(401);
		}
	break;

	case "getstudent":
		if ($auth->authorized()) {
			if (count($req)>1) {
				echo response($get->get_student($req[1],1));
			} else {
				echo response($get->get_student(null, null));
			}
		} else {
			echo errMsg(401);
		}
	break;

	case "getstudentsubj":
		if ($auth->authorized()) {
			echo response($get->get_enrolledsubj($req[1], $req[2], $req[3]));
		} else {
			echo errMsg(401);
		}
	break;


	case "getstudentbydept":
		if ($auth->authorized()) {
			echo response($get->get_student($req[1], 2));
		} else {
			echo errMsg(401);
		}
	break;

	case "getstudentbyprogram":
		if ($auth->authorized()) {
			echo response($get->get_student($req[1], 3));
		} else {
			echo errMsg(401);
		}
	break;

	case "getcourses":
		if ($auth->authorized()) {
			echo response($get->get_courses());
		} else {
			echo errMsg(401);
		}
	break;

	case "getprograms":
		if ($auth->authorized()) {
			if (count($req)>1) {
				echo response($get->get_programs($req[1]));
			} else {
				echo response($get->get_programs(null));
			}
		} else {
			echo errMsg(401);
		}
	break;

	case "getclasses":
		if ($auth->authorized()) {
			if (count($req)>3) {
				echo response($get->get_classes($req[1], $req[2], $req[3]));
			} else {
				echo response($get->get_classes($req[1], $req[2], null));
			}
		} else {
			echo errMsg(401);
		}
	break;

	case "getacadyear":
		if ($auth->authorized()) {
			echo response($get->get_acadyear());
		} else {
			echo errMsg(401);
		}
	break;

	case "getblocks":
		if ($auth->authorized()) {
			echo response($get->get_blocks($req[1], $req[2], $req[3]));
		} else {
			echo errMsg(401);
		}
	break;

	case "getenrolled":
		if ($auth->authorized()) {
			echo response($get->get_enrolled($req[1], $req[2], $req[3]));
		} else {
			echo errMsg(401);
		}
	break;

	case "getscheduledenrolledstudent":
		if ($auth->authorized()) {
			echo response($get->get_student(null, 4));
		} else {
			echo errMsg(401);
		}
	break;

	# Added July 18, 2020
	# Edited July 19, 2020
	case "getacadrecords":
		if ($auth->authorized()) {
			if (count($req)>2){
				echo response($get->get_acadrecords(base64_decode($req[1]), base64_decode($req[2])));
			} else {
				echo response($get->get_acadrecords(base64_decode($req[1]), null));
			}
		} else {
			echo errMsg(401);
		}
	break;
	# ./Edited July 19, 2020

	case "getsubjects":
		if ($auth->authorized()) {
			echo response($get->get_subjects(base64_decode($req[1])));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added July 18, 2020


	// Admin Methods

	# Added July 21, 2020
	case "getstats":
		if ($auth->authorized()) {
			echo response($get->get_stats());
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added July 21, 2020

	# Added July 21, 2020
	case "getusers":
		if ($auth->authorized()) {
			echo response($get->get_users(base64_decode($req[1])));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added July 21, 2020

	# Added July 28, 2020
	case "getadminlogs":
		if ($auth->authorized()) {
			echo response($get->get_adminlogs());
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added July 28, 2020

	    	// Dean Methods
	# /Added on August 2, 2020
	case "getclassesperdept":
		if ($auth->authorized()) {
			echo response($get->get_classesperdept(base64_decode($req[1]), base64_decode($req[2]), base64_decode($req[3])));
		} else {
			echo errMsg(401);
		}
	break;

	case "getblocksperdept":
		if ($auth->authorized()) {
			echo response($get->get_blocksperdept(base64_decode($req[1]), base64_decode($req[2]), base64_decode($req[3])));
		} else {
			echo errMsg(401);
		}
	break;

	case "getenrolledperblock":
		if ($auth->authorized()) {
			echo response($get->get_enrolledperblock(base64_decode($req[1]), base64_decode($req[2]), base64_decode($req[3])));
		} else {
			echo errMsg(401);
		}
	break;

	case "getenrolledperclass":
		if ($auth->authorized()) {
			echo response($get->get_enrolledperclass(base64_decode($req[1]), base64_decode($req[2]), base64_decode($req[3])));
		} else {
			echo errMsg(401);
		}
	break;

	case "getenrolledperprog":
		if ($auth->authorized()) {
			echo response($get->get_enrolledperprog(base64_decode($req[1]), base64_decode($req[2]), base64_decode($req[3])));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added on August 2, 2020

	case "getslots":
		if ($auth->authorized()) {
			echo response($get->get_slots());
		} else {
			echo errMsg(401);
		}
	break;

	// Announcements
	case "getannouncements":
		if ($auth->authorized()) {
			echo response($get->get_announcements());
		} else {
			echo errMsg(401);
		}
	break;

	# /Added August 19, 2020
	case "getallfaculty":
		if ($auth->authorized()) {
			echo response($get->get_faculty(0,null));
		} else {
			echo errMsg(401);
		}
	break;

	case "getfacultyperdept":
		if ($auth->authorized()) {
			echo response($get->get_faculty(1, base64_decode($req[1])));
		} else {
			echo errMsg(401);
		}
	break;

	case "getfaculty":
		if ($auth->authorized()) {
			echo response($get->get_faculty(2, base64_decode($req[1])));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added August 19, 2020

	########################################
	# 	POST METHODS
	########################################

	case "updatestudentinfo":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->update_studentinfo($d));
		} else {
			echo errMsg(401);
		}
	break;

	case "saveclass":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->process_classes($d));
		} else {
			echo errMsg(401);
		}
	break;

	case "updateaccount":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->update_accounts($d));
		} else {
			echo errMsg(401);
		}
	break;

	case "uploadimage":
		if ($auth->authorized()) {
			echo response($post->process_upload($req[1], $req[2], $req[3]));
		} else {
			echo errMsg(401);
		}
	break;


	case "unschedule":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->unschedule(base64_decode($req[1]), base64_decode($req[2]), base64_decode($req[3]), $d));
		} else {
			echo errMsg(401);
		}
	break;

	# /Added July 20, 2020
	case "credit":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->credit_subjects($d));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added July 20, 2020


	# /Added July 25, 2020
	case "confirmenrollment":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->confirm_enrollment(base64_decode($req[1]),base64_decode($req[2]),base64_decode($req[3]),base64_decode($req[4]), $d));
		} else {
			echo errMsg(401);
		}
	break;
	
	# Admin Endpoints

	case "admupdatestudent":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->admin_studentUpdate($d));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added July 25, 2020

	# ./Added July 27, 2020
	case "admupdateuser":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->admin_userupdate($d));
		} else {
			echo errMsg(401);
		}
	break;

	case "adminsertuser":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->admin_userinsert($d));
		} else {
			echo errMsg(401);
		}
	break;

	case "archiveuser":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->moveto_archive($d));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added July 27, 2020

	# Added on July 29, 2020
	case "adminsertstudent":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->admin_studentInsert($d));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added on July 29, 2020
	# ./Admin Endpoints


	# Added on August 3, 2020
	case "updatecredentials":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($auth->change_password($d));
		} else {
			echo errMsg(401);
		}
	break;

	case "updateyrlevel":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->update_record($d));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added on August 3, 2020

	# Dean Endpoints


	# /Added on August 2, 2020
	case "dnremovestudent":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->dnremove_student($d));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added on August 2, 2020
	# ./Dean Endpoints

	case "updateslot":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->update_slot($d));
		} else {
			echo errMsg(401);
		}
	break;

	//Announcements
	case "addannouncement":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->add_announcement($d));
		} else {
			echo errMsg(401);
		}
	break;

	case "removeannouncement":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->remove_announcement($d));
		} else {
			echo errMsg(401);
		}
	break;

	# Added on August 15
	case "changepassword":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($auth->studentPasswordChange($d));
		} else {
			echo errMsg(401);
		}
	break;
	# Added on August 15

	# /Added August 20, 2020
	case "assigninstructor":
		if ($auth->authorized()) {
			$d = jd(base64_decode(file_get_contents("php://input")));
			echo response($post->assign_instructor($d));
		} else {
			echo errMsg(401);
		}
	break;
	# ./Added August 20, 2020

	default:
		echo errMsg(400);
	break;
	}
break;

default:
	echo errMsg(403);
	break;
}
?>