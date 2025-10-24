<?php
session_start();

unset($_SESSION['code']);
unset($_SESSION['user']);

session_destroy();
session_write_close();

header("Location: index.html");
exit();
?>