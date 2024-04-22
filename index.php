<?php
ob_start();

require 'routes/routes.php';

// Cors allow all
Configuration::AccessControlAllowOrigin("*");