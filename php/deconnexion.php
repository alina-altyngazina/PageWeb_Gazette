<?php
require_once('./bibli_gazette.php');

// démarrage de la session
session_start();

em_session_exit(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php');

?>
