<?php
   
    require 'connection.inc.php';
    

    session_start();
  
    if(isset($_POST['name']) && isset($_POST['hometown']) && isset($_POST['gender']) && 
       isset($_POST['phone_number']) && isset($_POST['moms_name'])) { 
        
        // Create local variables based on the values in the form
        $name = $_POST['name'];
        $hometown = $_POST['hometown'];
        $gender = $_POST['gender'];
        $phone_number = $_POST['phone_number'];
        $moms_name = $_POST['moms_name'];
        
        // If anything is empty tell the user they need to fill all fields in
        if(!empty($name) && !empty($hometown) && !empty($gender) && !empty($phone_number) && !empty($moms_name)) {
            
            // Search the database for the user
            $query = "SELECT * FROM trainer WHERE name='".mysql_real_escape_string($name)."'";
            $name_query = mysql_query($query);
            // Make sure the name does not already exist
            if(mysql_num_rows($name_query) != 1) {
                
                // If they do not exist, built the insert statement with their supplied values
                $insert = "INSERT INTO trainer VALUES ('".mysql_real_escape_string($name)."', 
                        '', '".mysql_real_escape_string($gender)."', '".mysql_real_escape_string($hometown)."',
                        '".mysql_real_escape_string($phone_number)."','".mysql_real_escape_string($moms_name)."')";
                // If the query runs
                if($run_query = mysql_query($insert)){
                    
                    // Set all gathered variables to session variables.
                    $_SESSION['name'] = $name;
                    $_SESSION['hometown'] = $hometown;
                    $_SESSION['gender'] = $gender;
                    $_SESSION['phone_number'] = $phone_number;
                    $_SESSION['moms_name'] = $moms_name;
                    
                    // Change location and terminate this page.
                    header('Location: trainer.php');
                    die();
                } else {
                    // There was an error with the insert query.
                    echo "query". mysql_error();
                }
            } else {
                // The name already exists in the database
                echo 'Name '.$name.' already exists';
            }
        } else {
            // One or more fields were left empty.
            echo 'All fields are required.';
        }
    }
?>