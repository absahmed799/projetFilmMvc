<?php
require 'app/includes/config.php';
require 'app/includes/chargementClasses.inc.php';
session_start();
// echo "<pre>".print_r($_SESSION, true)."</pre>";
// echo "<pre>".print_r($_POST, true)."</pre>";
(new Routeur)->router();