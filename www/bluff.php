<?php

require_once "../lib/DBConnection.php";
require_once "../lib/home.php";
require_once "../lib/game.php";
require_once "../lib/users.php";


$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);
if ($input == null) {
    $input = [];
}
if (isset($_SERVER['HTTP_X_TOKEN'])) {
    $input['token'] = $_SERVER['HTTP_X_TOKEN'];
} else {
    $input['token'] = '';
};
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
                getGameStatus($_REQUEST["id"]);
                break;
            case 'getRoomPlayers':
                getOnlinePlayersByRoomId($_REQUEST["roomId"]);
                break;
            case 'start':
                startGame();
                break;
            case 'getGameOwner':
                getOwnerInfo();
                break;
            case 'getGameInfo':
                getGameInfo($method);
                break;
            case 'getMyCards':
                getMyCards($method);
                break;
            case 'playYourBluff':
                playMyBluff($method, $_REQUEST["valueOfCardsPlayed"], $_REQUEST["cardsPlayed"]);
                break;
            case 'callBluff':
                callBluff($method);
                break;
            case 'getCalledBluffCards':
                getCardsFromCalledBluff($method, $_REQUEST["userToCollectBank"]);
                break;
            case 'passOnBluff':
                passAction($method);
                break;
            case 'resetPasses':
                resetGamePasses($method);
                break;
            case 'addCardsToBank':
                addCardsToBank($method);
                break;
            case 'checkIfYouWin':
                checkIfWinner($method);
                break;
            case 'getWinner':
                getGameWinner($method);
                break;
            case 'restoreRoom':
                restoreRoomAndClean($method);
                break;
            default:
                header("HTTP/1.1 404 Not Found");
                break;
        }
        break;
    case 'players':
        switch ($b = array_shift($request)) {
            case '':
                handleUser($method, $input);
                break;
            case null:
                handleUser($method, $input);
                break;
            default:
                header("HTTP/1.1 404 Not Found");
                break;
        }
        break;
    default:
        header("location: home.php");
        exit;
}

function show_home($method)
{
    if (strcmp($method, "GET")  ==  0) {
        header('location: ../../home.php');
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
