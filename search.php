<?php
    
    session_start();
    
   
    require 'connection.inc.php';
   
    $name = $_POST['name'];
    
    $_SESSION['name'] = $name;
    
    $_SESSION['pokemon'] = array('Name','Name', 'Name', 'Name', 'Name', 'Name');
    $_SESSION['abilities'] = array('0','1','2','3','4','5','6','7');
    
    
    
    $query = sprintf("SELECT hometown, gender, phone_number, momsname FROM trainer WHERE name ='%s'", 
                     mysql_real_escape_string($name)) or die('search query');
    
    $result = mysql_query($query) or die('query');
       if(mysql_num_rows($result) == 0){
        header('Location: registration_form.php');
        die();
    }
    
    while($row = mysql_fetch_assoc($result)) {
        $hometown = $row['hometown'] or die('hometown');
        $gender = $row['gender'] or die('gender');
        $phone_number = $row['phone_number'] or die('phone number');
        $moms_name = $row['momsname'] or die('moms name');
    }
    
    $_SESSION['hometown'] = $hometown;
    $_SESSION['gender'] =$gender;
    $_SESSION['phone_number'] =$phone_number;
    $_SESSION['moms_name'] =$moms_name;
    // Transfer the user to their profile page.
    header('Location: trainer.php');
?>