<?php
    if (!isset($_SESSION)) {
        session_start();
    }
?>

<?php
	include('api/scrudAPI.php');

    $connection = new mysqli('localhost', 'root', 't00r', 'hospital');
    $conn = new Database();
	try {
	  $conn->connect();
	} catch(ConnectErrorException $e) {
	  print "Connect Says ".$e->getMessage();
	  exit();
	}

    $error_flag = 0;
    $result;

    function login($email_id_unsafe, $password_unsafe, $table = 'users')
    {
        global $conn;

        $email_id = $email_id_unsafe;
        $password = $password_unsafe;

        $conn->select($table, 'COUNT(*)', 'email = :email AND password = :pass', ['email'=>$email_id, 'pass' => $password]);
		$result = $conn->getResult(); 
		
        $num_rows = (int) $result[0]['COUNT(*)'];

        if ($num_rows > 1) {
            //send email to sysadmin that my site has been hacked
              return 0;
        } elseif ($num_rows == 0) {
            echo status('no-match');

            return 0;
        } else {
            echo "<div class='alert alert-success'> <strong>Access Granted!</strong> Logged In</div>";
            $_SESSION['username'] = $email_id;

            if ($table == 'admin') {
                $_SESSION['user-type'] = 'admin';
            }

            if ($table == 'users' || $table == 'doctors' || $table == 'clerks') {
				$bind = ['email' => $email_id, 'password' => $password];
                $conn->select($table, 'fullname', 'email = :email AND password = :password', $bind);
                
                $result = $conn->getResult()[0]; //we expect one row
                
				$fullname = $result['fullname'];
                $_SESSION['fullname'] = $fullname;
                if ($table == 'users') {
                    $_SESSION['user-type'] = 'normal';
                } elseif ($table == 'clerks') {
                    $_SESSION['user-type'] = 'clerk';
                } else {
                    $_SESSION['user-type'] = 'doctor';
                    $_SESSION['email'] = $email_id;
                }
            }

            return 1;
        }
    }

    function register($email_id_unsafe, $password_unsafe, $full_name_unsafe, $speciality_unsafe = 'doctor', $table = 'users')
    {
        global $conn, $error_flag;

        $email = $email_id_unsafe;
        $password = $password_unsafe;
        $speciality = $speciality_unsafe;
        $fullname = ucfirst($full_name_unsafe);

        $sql;

        switch ($table) {
            case 'users':
                $sql = "INSERT INTO $table VALUES ('$email', '$password', '$fullname');";
                break;
            case 'doctors':
                $sql = "INSERT INTO $table VALUES ('$email', '$password', '$fullname','$speciality');";
                break;
            case 'clerks':
                $sql = "INSERT INTO $table VALUES ('$email', '$password', '$fullname');";
                break;
            default:
                // code...
                break;
        }

        try {
			$conn->sqlQuery($sql);
            echo status('record-success');
            if ($table == 'users' && $error_flag == 0) {
                return login($email, $password);
            }
        } catch (QueryErrorException $e) {
            echo status('record-fail');
        }
    }

    function status($type, $data = 0)
    {
        $success = "<div class='alert alert-success'> <strong>Done!</strong>";
        $fail = "<div class='alert alert-warning'><strong>Sorry!</strong>";
        $end = '</div>';

        switch ($type) {
            case 'record-success':
                return "$success New record created successfully! $end";
                break;
            case 'record-fail':
                return "$fail New record creation failed. $end";
                break;
            case 'record-dup':
                return "$fail Duplicate record exists. $end";
                break;
            case 'no-match':
                return "$fail Record did not match. $end";
                break;
            case 'con-failed':
                return "$fail connection Failed! $end";
                break;
            case 'appointment-success':
                return "$success Your appointment is booked successfully! Your appointment no is $data $end";
                break;
            case 'appointment-fail':
                return "$fail Failed to book your appointment Failed! $end";
                break;
            case 'update-success':
                return "$success New record updated successfully! $end";
                break;
            case 'update-fail':
                return "$fail Failed to update data! $end";
                break;
            default:
                // code...
                break;
        }
    }

  function enter_patient_info($full_name_unsafe, $age_unsafe, $weight_unsafe, $phone_no_unsafe, $address_unsafe)
  {
      global $conn, $error_flag, $result;

      $full_name = ucfirst($full_name_unsafe);
      $age = $age_unsafe;
      $weight = $weight_unsafe;
      $phone_no = $phone_no_unsafe;
      $address = $address_unsafe;

      $sql = "INSERT INTO `patient_info` VALUES (NULL, '$full_name', $age,$weight, '$phone_no','$address');";

      try {
		$conn->sqlQuery($sql);
		echo status('record-success');
		return $conn->dbgNr();
      } catch (QueryErrorException $e) {
		echo status('record-fail');
		return 0;
      }
  }

    function appointment_booking($patient_id_unsafe, $specialist_unsafe, $medical_condition_unsafe)
    {
        global $conn;
        $patient_id = $patient_id_unsafe;
        $specialist = $specialist_unsafe;
        $medical_condition = $medical_condition_unsafe;

        try {
			$insid = $conn->insert('appointments',['appointment_no'=>NULL, 'patient_id'=>$patient_id, 'speciality'=>$specialist, 'medical_condition'=>$medical_condition, 'doctors_suggestion'=>NULL, 'payment_amount'=>NULL, 'case_closed'=>'no']);
            echo status('appointment-success', $insid);
        } catch (QueryErrorException $e) {
            echo status('appointment-fail');
            echo 'Error: <br/>'.$e->getMessage();
        }
    }

    function update_appointment_info($appointment_no_unsafe, $column_name_unsafe, $data_unsafe)
    {
        global $conn;

        $sql;

        $appointment_no = (int) $appointment_no_unsafe;
        $column_name = $column_name_unsafe;
        $data = $data_unsafe;

        if ($column_name == 'payment_amount') {
            $data = (int) $data;
            $sql = "UPDATE `appointments` SET `payment_amount` = '$data', `case_closed` = 'no' WHERE appointment_no` = $appointment_no";
        } else {
            $sql = "UPDATE appointments SET $column_name = '$data' WHERE appointment_no = $appointment_no;";
        }
        echo $sql;
        try {
			$conn->sqlQuery($sql);
            echo status('update-success');
            return 1;
        } catch (QueryErrorException $e) {
            echo status('update-fail');
            echo 'Error: <br/>'.$e->getMessage();
            return 0;
        }
    }

    function getPatientsFor($doctor = 'Dentist')
    {
        global $conn;
		$conn->sqlQuery("SELECT appointment_no, full_name, medical_condition FROM patient_info, appointments where speciality='$doctor' AND patient_info.patient_id = appointments.patient_id");
		return $conn->getResult();
    }

    function getAllAppointments()
    {
        global $conn;
        $conn->sqlQuery('SELECT appointment_no, full_name,speciality, medical_condition FROM patient_info, appointments where patient_info.patient_id = appointments.patient_id');
        return $conn->getResult();
    }

    function getAllPatientDetail($appointment_no)
    {
		global $conn;
		$bind = ['appointment_no'=>$appointment_no];
        $conn->sqlQuery("SELECT appointment_no, full_name, dob, weight, phone_no, address, medical_condition FROM patient_info, appointments where appointment_no = :appointment_no AND patient_info.patient_id = appointments.patient_id;", $bind);
        return $conn->getResult();
    }

    function get_table($purpose, $data)
    {
        global $conn;

        $sql;

        switch ($purpose) {
            case 'patient_information':
                $sql = 'SELECT * FROM patient_info AND (SELECT )';
                break;
            case 'doctor-home':
                $sql = '';

                $result = $conn->sqlQuery($sql);

                echo "<table border='1'>
				<tr>
				<th>appointment_no</th>
				<th>patient_name</th>
				<th>age</th>
				<th>appointment_time</th>
				<th>medical_condition</th>
				<th>option</th>
				</tr>";

                foreach ($result as $row) {
                    echo '<tr>';
                    echo '<td>'.$row['appointment_no'].'</td>';
                    echo '<td>'.$row['full_name'].'</td>';
                    echo '<td>'.$row['age'].'</td>';
                    echo '<td>'.$row['appointment_time'].'</td>';
                    echo '<td>'.$row['medical_condition'].'</td>';
                    echo "<td> <button class='btn btn-primary'> Open Case</button> <button class='btn btn-primary'> Close Case</button> </td>";
                    echo '</tr>';
                }
                echo '</table>';
                break;
            case 'all':
                $sql = 'SELECT * FROM patient_info AND (SELECT )';
                break;
            case 'patient_information':
                $sql = 'SELECT * FROM patient_info AND (SELECT )';
                break;
            default:
                // code...
                break;
        }
    }

    function appointment_status($appointment_no_unsafe)
    {
        global $connection;

        $appointment_no = $appointment_no_unsafe;
        $i = 0;

        $result = $connection->query("SELECT doctors_suggestion FROM appointments WHERE appointment_no=$appointment_no;");
        if ($result === false) {
            return 0;
        } else {
            ++$i;
        }

        $result = $connection->query('SELECT payment_amount FROM appointments WHERE appointment_no=appointment_no;');
        if ($result->num_rows == 1) {
            ++$i;
        }

        return $i;
    }

    function delete($table, $id_unsafe)
    {
		global $conn;
        $id = $id_unsafe;
        $conn->delete($table, "email = $id");
        return $conn->getResult();
    }

    function getListOfEmails($table)
    {
		global $conn;
        $conn->select($table, 'email');
        return $conn->getResult();
    }
    
    function getDoctorDetails($email)
    {
		global $conn;
        $conn->select('doctors','email, speciality','email = :email',['email'=>$email]);
        return $conn->getResult()[0]; // We expect a single row since the email is assumed to be unique
    }

    /* noAccess Functions -- Redundant but neccesary*/
    function noAccessForNormal()
    {
        if (isset($_SESSION['user-type'])) {
            if ($_SESSION['user-type'] == 'normal') {
                echo '<script type="text/javascript">window.location = "add_patient.php"</script>';
            }
        }
    }
    
    function noAccessForDoctor()
    {
        if (isset($_SESSION['user-type'])) {
            if ($_SESSION['user-type'] == 'doctor') {
				$email = $_SESSION['email'];
				$result = getDoctorDetails($email);
                echo '<script type="text/javascript">window.location = "patient_info.php?speciality='.$result['speciality'].'"</script>';
            }
        }
    }
    
    function noAccessForClerk()
    {
        if (isset($_SESSION['user-type'])) {
            if ($_SESSION['user-type'] == 'clerk') {
                echo '<script type="text/javascript">window.location = "all_appointments.php"</script>';
            }
        }
    }

    function noAccessForAdmin()
    {
        if (isset($_SESSION['user-type'])) {
            if ($_SESSION['user-type'] == 'admin') {
                echo '<script type="text/javascript">window.location = "admin_home.php"</script>';
            }
        }
    }

    function noAccessIfLoggedIn()
    {
        if (isset($_SESSION['user-type'])) {
            noAccessForNormal();
            noAccessForAdmin();
            noAccessForClerk();
            noAccessForDoctor();
        }
    }

    function noAccessIfNotLoggedIn()
    {
        if (!isset($_SESSION['user-type'])) {
            echo '<script type="text/javascript">window.location = "index.php"</script>';
        }
    }
	/* End of noAccess Functions*/
?>
