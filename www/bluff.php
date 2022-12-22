<?php
//require_once "../lib/db_upass.php";
require_once "../lib/DBConnection.php";
require_once "../lib/home.php";


$method = $_SERVER['REQUEST_METHOD'];

//$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
/* print_r($request);
exit; */
// Σε περίπτωση που τρέχουμε php –S
$input = json_decode(file_get_contents('php://input'), true);
switch ($r = array_shift($request)) {
    case 'board':

        switch ($b = array_shift($request)) {
            case '':
                show_home($method, $input);
                break;
            case null:
                show_home($method, $input);
                break;
            case 'test':
                show_home($method, $input);
                break;
            case 'player':
                handle_board($method, $input);
                break;
            default:
                header("HTTP/1.1 404 Not Found");
                break;
        }
        break;
    default:

        header("location: home.html");

        exit;
}

function handle_board($method, $input)
{
    if ($method == 'GET') {
        show_board($input);
    }/*  else if ($method=='POST') {
            reset_board();
            show_board($input);
    } */ else {
        header('HTTP/1.1 405 Method Not Allowed');
    }
}

function show_board($input)
{
    header('HTTP/1.1 405 Method Not Allowed');
}

function show_home($method, $input)
{

    /* header("location: ./ADISE22_THEITGUYS/www/home.html"); */
    if (strcmp($method, "GET")  ==  0) {
        header('location: ../../home.html');
    } else if (strcmp($method, "POST") == 0) {
        show_rooms();
    }
}
