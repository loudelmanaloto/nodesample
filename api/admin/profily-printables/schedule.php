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
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico">
    <title>Official Schedule</title>
    <style type="text/css">
        table, th, td {
            border: 1px solid black;
        }

        table {
            border-collapse: collapse;
        }

        th {
            height: 30px;
            vertical-align: middle;
            padding: 3px;
        }

        td {
            height: 20px;
            vertical-align: middle;
            padding: 3px;
        }
    </style>
</head>
<body style='margin: 0; padding: 0; font-family: Arial; font-size: 12px'>
    <div>
        <img src='./assets/logo_gc.png' alt='Gordon College' style="width: 12%; float: left;" />
        
    </div>
    <div style="padding-top: 11px">
        <span style="font-size: 1.3rem; font-weight: bolder; font-family: Arial;" >GORDON COLLEGE</span><br>
        <span>Olongapo City Sports Complex, Donor St., <br>East Tapinac, Olongapo City, Zambales PH</span>
    </div><br><br><br>

    <!-- <div style="text-align: center; font-weight: bolderl font-size: 16px">
        <span>Official Class Scehdule of Student No. <?php echo $idNumber; ?></span>
    </div> -->

    <div style="text-align: center; font-weight: bolder; font-size: 20px">
        <span>
            Official Class Schedule for
            <?php 
                if ($semester == 1) {
                    echo '1st Semester, ';
                } elseif ($semester == 2) {
                    echo '2nd Semester, '; 
                } else {
                    echo 'Mid Year, ';
                }
            ?>
            AY <?php echo $academicYear; ?>
        </span>
    </div><br><br>
    <!-- <h3> Class Schedule - 
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
    </h3> -->

    <div>
        <?php 
            $sql = mysqli_query($conn, "SELECT fname_fld, mname_fld, lname_fld, extname_fld FROM students_tbl WHERE studnum_fld=".$idNumber." LIMIT 1");
            $res2 = mysqli_fetch_assoc($sql);
            $fullname = $res2['lname_fld'].', '. $res2['fname_fld'].' '. $res2['mname_fld'].' '. $res2['extname_fld'];
        ?>
        Student Number: <span style="font-weight: bolder"><?php echo $idNumber; ?></span><br>
        Student Name:&nbsp;&nbsp;&nbsp; <span style="font-weight: bolder"><?php echo $fullname; ?></span>
    </div><br>

    <table cellspacing='3'>
        <thead >
            <th style="width: 8%; text-align: center; align-items: center;">Class Code</th>
            <th style="width: 8%; text-align: center; align-items: center;">Subject Code</th>
            <th style="width: 36%; text-align: center; align-items: center;">Subject Desc</th>
            <th style="width: 8%; text-align: center; align-items: center;">Lec Units</th>
            <th style="width: 8%; text-align: center; align-items: center;">Lab Units</th>
            <th style="width: 8%; text-align: center; align-items: center;">RLE Units</th>
            <th style="width: 10%; text-align: center; align-items: center;">Day</th>
            <th style="width: 20%; text-align: center; align-items: center;">Time</th>
        </thead>
        <tbody>
            <?php 
            $query = mysqli_query($conn, "SELECT enrolledsubj_tbl.*, (SELECT subjects_tbl.subjdesc_fld from subjects_tbl WHERE subjcode_fld=enrolledsubj_tbl.subjcode_fld LIMIT 1) AS subjdesc_fld, (SELECT subjects_tbl.lecunits_fld from subjects_tbl WHERE subjcode_fld=enrolledsubj_tbl.subjcode_fld LIMIT 1) AS lecunits_fld, (SELECT subjects_tbl.labunits_fld from subjects_tbl WHERE subjcode_fld=enrolledsubj_tbl.subjcode_fld LIMIT 1) AS labunits_fld, (SELECT subjects_tbl.rleunits_fld from subjects_tbl WHERE subjcode_fld=enrolledsubj_tbl.subjcode_fld LIMIT 1) AS rleunits_fld, classes_tbl.room_fld, classes_tbl.starttime_fld, classes_tbl.endtime_fld, classes_tbl.day_fld, classes_tbl.empnum_fld, (SELECT CONCAT(fname_fld, ' ', lname_fld) FROM adminaccounts_tbl WHERE empnum_fld=classes_tbl.empnum_fld) AS fullname_fld FROM enrolledsubj_tbl INNER JOIN classes_tbl USING (classcode_fld) WHERE enrolledsubj_tbl.studnum_fld='$idNumber' AND enrolledsubj_tbl.ay_fld='$academicYear' AND enrolledsubj_tbl.sem_fld=$semester AND classes_tbl.block_fld=enrolledsubj_tbl.block_fld");
            if(mysqli_num_rows($query)>0){
              while($res = mysqli_fetch_assoc($query)){

                $classCode = $res['classcode_fld'];
                $subCode = $res['subjcode_fld'];
                $subDesc = $res['subjdesc_fld'];
                $lecunits = $res['lecunits_fld'];
                $labunits = $res['labunits_fld'];
                $rleunits = $res['rleunits_fld'];
                $day = $res['day_fld'];
                $startTime = $res['starttime_fld'];
                $endTime = $res['endtime_fld'];

                echo "
                <tr>
                    <td style='text-align: center; align-items: center;'>".$classCode."</td>
                    <td style='text-align: center; align-items: center;'>".$subCode."</td>
                    <td>".$subDesc."</td>
                    <td style='text-align: center; align-items: center;'>".$lecunits."</td>
                    <td style='text-align: center; align-items: center;'>".$labunits."</td>
                    <td style='text-align: center; align-items: center;'>".$rleunits."</td>
                    <td style='text-align: center; align-items: center;'>".$day."</td>
                    <td  style='text-align: center; align-items: center;'>".$startTime." - ".$endTime."</td>
                </tr>";
              }
            }
            ?>
        </tbody>
    </table>
    <br />
    <i>Congratulations! You are now officially enrolled. Student no. <?php echo $idNumber; ?></i>
    </td>
    </tr>
    </table>
</body>
</html>

<script>
    var css = '@page { size: landscape; }',
    head = document.head || document.getElementsByTagName('head')[0],
    style = document.createElement('style');

    style.type = 'text/css';
    style.media = 'print';

    if (style.styleSheet){
      style.styleSheet.cssText = css;
    } else {
      style.appendChild(document.createTextNode(css));
    }

    head.appendChild(style);
    window.print();
    // window.close();
</script>