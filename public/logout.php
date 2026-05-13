<?php
require_once '../config/app.php';

logout_user();

header('Location: login.php?status=logout');
exit();
