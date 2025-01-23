<?
    include("db.php")

    dd($_POST)
    return 
    if(isset($_POST['submit'])) { 
       $patientName=$_POST['patient-name'];
       $patientEmail=$_POST['patient-email'];
       $package_name=$_POST['package-name'];

       if(!isset($conn)){
        die()
       }

       $sql = "INSERT INTO appointment ('id', 'name', 'email', 'package', 'date' ) VALUES(null, $patientName, $patientEmail, $package_name, null)";

       $data = $conn->query($sql);
       if(!$data){
        echo 'Something went wrong'
       }
    }

?>