<?php

function handleUser($method, $input)
{
    if ($method == 'POST')
        setUser($input);
}

function setUser($usernameInput)
{
    if (!isset($usernameInput['username']) || $usernameInput['username'] == '') {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "No username given."]);
        exit;
    }

    global $conn;

    $sql = 'insert into users(name, log_in_time, token) values(?, NOW(), md5(CONCAT( ?, NOW())))';
    $st = $conn->prepare($sql);
    $st->bind_param('ss', $usernameInput['username'], $usernameInput['username']);
    $st->execute();

    $sql = 'select * from users where name=?';
    $st = $conn->prepare($sql);
    $st->bind_param('s', $usernameInput['username']);
    $st->execute();
    $res = $st->get_result();

    //user session 
    $userData = $res->fetch_all(MYSQLI_ASSOC)[0];
    $userObj = '{"id":"' . $userData["id"] . '","name":"' . $userData["name"] . '","token":"' . $userData['token'] . '"}';

    session_start();
    $_SESSION['user'] = $userObj;

    header('Content-type: application/json');
    print json_encode($userObj);
}

function handleRoom($method)
{
    session_start();
    if (strcmp($method, "POST") != 0) {
        print json_encode("νοτ ριγητ");
        exit;
    }
    if (!isset($_COOKIE["room"])) {
        print json_encode("νοτ 2");
        exit;
    }
    if (!isset($_SESSION["user"])) {
        print json_encode("νοτ 3");
        exit;
    }

    global $conn;

    $sql = 'SELECT owner_id, status, users_online FROM rooms WHERE id=?';
    $st = $conn->prepare($sql);
    $st->bind_param('s', $_COOKIE['room']);
    $st->execute();
    $res = $st->get_result();
    $results = $res->fetch_all(MYSQLI_ASSOC);

    //user session 
    $roomOwner = $results[0]["owner_id"];
    $roomStatus = $results[0]["status"];
    $roomUserCount = $results[0]["users_online"];

    if ($roomOwner == (int)json_decode($_SESSION["user"])->id) {
        $stmt = $conn->prepare('UPDATE rooms SET owner_id =NULL WHERE id=?');
        $stmt->bind_param('s', $_COOKIE['room']);
        $stmt->execute();
    }
    if (strcmp($roomStatus, "pending") == 0 && $roomUserCount == 1) {
        $query = 'UPDATE rooms SET status="empty", users_online=users_online-1 WHERE id=?';
    }
    if (strcmp($roomStatus, "full") == 0) {
        $query = 'UPDATE rooms SET status="pending", users_online=users_online-1 WHERE id=?';
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $_COOKIE['room']);
    $stmt->execute();
}
