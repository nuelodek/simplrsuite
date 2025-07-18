<?php
session_start();
session_destroy();
header("Location: talentlogin.php");
exit();
?>
