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
