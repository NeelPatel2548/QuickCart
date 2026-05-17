<?php
session_start();
session_unset();
session_destroy();

// Redirect to homepage immediately
header("Location: index.php");
exit();
