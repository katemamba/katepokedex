<?php 
    $connect = mysql_connect('localhost', 'root', '') or die('unable to connect');
    mysql_select_db('katepokedex') or die('db');
?>