<?php
$mysqli = new mysqli('localhost', 'pavan', '$pass', 'katepokedex');
// Check connection
if(mysqli_connect_errno()) {
    printf('Connect failed: %s\n', mysqli_connect_errno());
    exit();
}
// Reutrn the name of the current default database
if($result = $mysqli->query("SELECT DATABASE()")) {
    $row = $result-> fetch_row();
    printf("Default database is %s.\n", $row[0]);
    $result->close();
}
$mysqli->close();
?>