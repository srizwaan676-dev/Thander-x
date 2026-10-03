<?php
session_start();

session_unset();      // Session data remove
session_destroy();    // Session destroy

header("Location: index.php"); // Ya login.php
exit();
?>