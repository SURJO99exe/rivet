<?php
require_once 'includes/auth.php';
$auth->logout();
redirect('login.php');
?>
