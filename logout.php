<?php
require_once(__DIR__.'/config.php');
session_start();
if(isset($_SESSION['user'])){
    unset($_SESSION['user']);
}
header('location: '.$GLOBALS['APP_URL']);