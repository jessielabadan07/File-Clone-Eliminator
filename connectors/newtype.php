<?php
include('db.inc.php');
$fileType = $_POST['fileType'];
$insert = mysql_query("INSERT INTO file_type (id, extension_name) VALUES (NULL, '$fileType')") or die(mysql_error());
?>