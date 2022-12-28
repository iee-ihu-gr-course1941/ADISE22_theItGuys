<?php

require_once "../lib/DBConnection.php";
require_once "../lib/home.php";
require_once "../lib/game.php";


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
            case 'getTotalRooms':
                getNumOfTotalRooms($method);
                break;
            default:
                header("HTTP/1.1 404 Not Found");
                break;
        }
        break;
    case 'game':
        switch ($b = array_shift($request)) {
            case is_numeric($b):
                go_to_room($method, $b);
                break;
            case 'getInfo':
                loadRoomInfo($method);
                break;
            case 'getGameStatus':
                getGameStatus($method);
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
        show_rooms('full');
    }
}

function getNumOfTotalRooms($method)
{
    if (strcmp($method, "GET")  ==  0) {
        header("HTTP/1.1 405 Not Allowed");
    } else if (strcmp($method, "POST") == 0) {
        show_rooms('numOfRooms');
    }
}

function go_to_room($method, $b)
{
    if (strcmp($method, "GET") == 0) {
        if (empty($b))
            header("HTTP/1.1 404 Not Found");
        else
            log_in_to_game($b);
    } else {
        header("HTTP/1.1 405 Not Allowed");
    }
}

function loadRoomInfo($method)
{
    if (strcmp($method, "GET")  ==  0) {
        header("HTTP/1.1 404 Not Found");
    } else if (strcmp($method, "POST") == 0) {
        get_room_info($_POST["room_id"]);
    }
}
