<?php
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 

	include("header.php");
	include("library.php");

	noAccessForNormal();
	noAccessForAdmin();
	noAccessForDoctor();
	noAccessIfNotLoggedIn();
	
	include('nav-bar.php');
?>

<div class="container">
<h2>Update Payment Information</h2>
<p>Enter information below</p>
<table class="table table-striped">
<?php

  if(isset($_POST['payment'])){
      $i = update_appointment_info($_POST['appointment_no'], 'payment_amount', $_POST['payment']);
      if($i==1){
        echo "<script type='text/javascript'>window.location = 'all_appointments.php'</script>";
      }
  }

  if(isset($_GET['appointment_no'])){
    $appointment_no = $_GET['appointment_no'];

    $row = getAllPatientDetail($appointment_no)[0];
	
    $link = "<tr><th>";
    $mid = "</th><td>";
    $endingTag = "</td></tr>";
    $pay = ($row['payment_amount']) ? "R".$row['payment_amount'] : "R0.00"; //The 'R' is for rands :-)
    echo "<tr>";   // appointment_no, full_name, dob, weight, phone_no, address, blood_group, medical_condition

    echo "$link Appointment No $mid". $row['appointment_no'] . "$endingTag";
    echo "$link Full Name $mid" . $row['full_name'] . "$endingTag";
    echo "$link Age (in years) $mid" . $row['dob'] . "$endingTag";

    echo "$link Weight $mid" . $row['weight'] . "$endingTag";

    echo "$link Phone No $mid" . $row['phone_no'] . "$endingTag";

    echo "$link Address $mid" . $row['address'] . "$endingTag";

    echo "$link Medical Condition $mid" . $row['medical_condition'] . "$endingTag";


    echo "$link Payment $mid" . "<form action='payment.php' method='post'>

	<textarea class ='form-control' name='payment' style='width: 500;' placeholder=".$pay."></textarea>
	<input type='number' style='visibility: hidden; width; 1px;' name='appointment_no' value =" . $appointment_no . ">
	<input type='submit' class='btn btn-primary' action='payment.php'></form>" . "$endingTag";
  
  }
?>
</table>

</div>

<?php include("footer.php"); ?>