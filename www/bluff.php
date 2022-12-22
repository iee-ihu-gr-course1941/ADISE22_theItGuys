<?php

require_once "../lib/DBConnection.php";
require_once "../lib/home.php";


$method = $_SERVER['REQUEST_METHOD'];

//$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));

switch ($r = array_shift($request)) {
    case 'bluff':
        switch ($b = array_shift($request)) {
            case '':
                show_home($method);
                break;
            case null:
                show_home($method);
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

function show_home($method)
{
    if (strcmp($method, "GET")  ==  0) {
        header('location: ../../home.html');
    } else if (strcmp($method, "POST") == 0) {
        show_rooms();
    }
}
