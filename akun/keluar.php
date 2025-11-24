<?php
session_start();
session_unset();
session_destroy();

header('Location: /glowify/akun/masuk.php');
exit;
