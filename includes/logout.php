<?php
declare(strict_types=1);
require_once("db.php");
session_destroy();
header("Location: ../index.php");
