<?php
require_once "db_pass.php";

define('DB_SERVER', 'localhost');
define('DB_USERNAME', $DB_USER);
define('DB_PASSWORD', $DB_PASS);
define('DB_NAME', 'bluff_game');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn == false) {
    die("ERROR: Connection failed " . mysqli_connect_error());
}
