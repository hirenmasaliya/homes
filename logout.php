<?php

if (isset($_COOKIE['login'])) {
    setcookie('login', "false", time() - 3600, '/');
}
header("Location: login.php");
exit();
?>
