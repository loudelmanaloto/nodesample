<?php

    require_once "connection.php";

    if (isset($_GET['idNumber'])) {
        $idNumber = $_GET['idNumber'];
        $query = mysqli_query($conn, "SELECT * from settings_tbl WHERE isactive_fld=1");
        if(mysqli_num_rows($query)>0){
          while($res = mysqli_fetch_assoc($query)){
            $academicYear = $res['acadyear_fld'];
            $semester = $res['sem_fld'];
          }
        }
    } else {
        // header('Location: https://www.gordoncollegeccs.edu.ph');
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule List</title>
</head>
<body style='margin: 0; padding: 0;'>
    <table border='0' cellpadding='0' cellspacing='0' width='100%'>
    <tr>
    <td>
    <table align='center' border='0' cellpadding='0' cellspacing='0' width='800px'
    style='border-collapse: collapse;'>
    <tr>
    <td style='color:antiquewhite'>
    <table>
    <tr>
    <td>
        <img src='./logo_gc.png'
    alt='Gordon College' width='200' height='150' />
    </td>
    <td style='color: #153643; font-family: Arial, sans-serif;line-height: 10px;'>
    <br><br>
        <h1>Gordon College</h1>
        <p>Serving the Community with a Culture of Excellence</p>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td bgcolor='#ffffff'>
    <table border='0' cellpadding='0' cellspacing='0' width='100%'>
    <tr>
    <td style='color: #153643; font-family: Arial, sans-serif; font-size: 24px; text-align: center'>
    Student No. <?php echo $idNumber; ?>
    </td>
    </tr>
    <tr>
    <td
    style='padding: 20px 0 30px 0;color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px; text-align: justify'>
    <h3> Official List of Schedules - 
        <?php 
            if ($semester == 1) {
                echo '1st Semester';
            } elseif ($semester == 2) {
                echo '2nd Semester'; 
            } else {
                echo 'Mid Year';
            }
        ?>
        AY <?php echo $academicYear; ?>
    </h3>
    <table cellspacing='3' width='100%'>
        <thead>
            <th>#</th>
            <th>Class Code</th>
            <th>Subject Code</th>
            <th>Subject Desc</th>
            <th>Lec Units</th>
            <th>Lab Units</th>
            <th>RLE Units</th>
            <th>Room</th>
            <th>Day</th>
            <th>Time</th>
        </thead>
        <tbody>
            <?php 
            $query = mysqli_query($conn, "SELECT enrolledsubj_tbl.*, (SELECT subjects_tbl.subjdesc_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS subjdesc_fld, (SELECT subjects_tbl.lecunits_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS lecunits_fld, (SELECT subjects_tbl.labunits_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS labunits_fld, (SELECT subjects_tbl.rleunits_fld from subjects_tbl WHERE subjcode_fld=classes_tbl.subjcode_fld LIMIT 1) AS rleunits_fld, classes_tbl.room_fld, classes_tbl.starttime_fld, classes_tbl.endtime_fld, classes_tbl.day_fld, classes_tbl.empnum_fld, (SELECT CONCAT(fname_fld, ' ', lname_fld) FROM adminaccounts_tbl WHERE empnum_fld=classes_tbl.empnum_fld) AS fullname_fld FROM enrolledsubj_tbl INNER JOIN classes_tbl USING (classcode_fld) WHERE enrolledsubj_tbl.studnum_fld='$idNumber' AND enrolledsubj_tbl.ay_fld='$academicYear' AND enrolledsubj_tbl.sem_fld=$semester");
            $x = 1;
            $totalLecUnits = 0;
            $totalLabUnits = 0;
            $totalRLEUnits = 0;
            if(mysqli_num_rows($query)>0){
              while($res = mysqli_fetch_assoc($query)){

                $classCode = $res['classcode_fld'];
                $subCode = $res['subjcode_fld'];
                $subDesc = $res['subjdesc_fld'];
                $lecUnits = $res['lecunits_fld'];
                $labUnits = $res['labunits_fld'];
                $rleUnits = $res['rleunits_fld'];
                $room = $res['room_fld'];
                $day = $res['day_fld'];
                $startTime = $res['starttime_fld'];
                $endTime = $res['endtime_fld'];

                echo "
                <tr>
                    <td>". $x ."</td>
                    <td>".$classCode."</td>
                    <td>".$subCode."</td>
                    <td>".$subDesc."</td>
                    <td>".$lecUnits."</td>
                    <td>".$labUnits."</td>
                    <td>".$rleUnits."</td>
                    <td>".$room."</td>
                    <td>".$day."</td>
                    <td>".$startTime."-".$endTime."</td>
                </tr>";

                
                $totalLecUnits = $totalLecUnits + $lecUnits;
                $totalLabUnits = $totalLabUnits + $labUnits;
                $totalRLEUnits = $totalRLEUnits + $rleUnits;
                $x++;
              }
            } else {
                // header('Location: https://www.gordoncollegeccs.edu.ph');
            }
            ?>
        </tbody>
    </table>
    <br />
            <strong>Lec Units: <?php echo $totalLecUnits; ?> </strong>
    <br />
            <strong>Lab Units: <?php echo $totalLabUnits; ?></strong>
    <br />
            <strong>RLE Units: <?php echo $totalRLEUnits; ?></strong>
    <br />
            <h3>Total Units: <?php echo ($totalLecUnits + $totalLabUnits + $totalRLEUnits); ?></h3>
    <br />
    <br />
    <i>Congratulations! You are now officially enrolled. Student no. <?php echo $idNumber; ?></i>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td bgcolor='#191f28' style='padding: 30px 30px 30px 30px;'>
    <table border='0' cellpadding='0' cellspacing='0' width='100%'>
    <tr>
    <td style='color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;'>
    <a href='https://gordoncollegeccs.edu.ph' style='color: #ffffff;'>
    <font color='#ffffff'>Gordon College</font>
    </a><br />
    Gordon College Registrar's Office
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
</body>
</html>

<script>
    window.print();
</script>