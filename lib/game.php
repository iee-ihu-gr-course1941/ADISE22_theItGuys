<?php

require_once "../lib/DBConnection.php";

function log_in_to_game($b)
{
    $statusObj = get_status_of_room($b);

    if (strcmp($statusObj['status'], "full") == 0) {
        header("HTTP/1.1 405 Not Allowed");
        exit;
    }

    //add extra person to users_online field
    update_room_and_user_status($b);
}

function update_room_and_user_status($room_id)
{
    global $conn;

    $sql = 'select users_online from rooms where id=? LIMIT 1';
    $st = $conn->prepare($sql);
    $st->bind_param('i', $room_id);
    $st->execute();
    $res = $st->get_result();

    $onlineUsers = $res->fetch_assoc()['users_online'];
    if ((int)$onlineUsers + 1 > 4) {
        header('location: ../../home.php?error=notavailable');
        exit;
    }
    if ((int)$onlineUsers + 1 == 4) {
        //update online users and status to full
        $sql = 'UPDATE rooms SET status="full", users_online=? WHERE id=?';
        $st2 = $conn->prepare($sql);
        $onlineUsers++;
        $st2->bind_param('ii', $onlineUsers, $room_id);
        $st2->execute();

        setGameOwner($room_id);
    }
    if ((int)$onlineUsers + 1 < 4) {
        //add only user and make room status pending
        $sql = 'UPDATE rooms SET status="pending", users_online=? WHERE id=?';
        $st2 = $conn->prepare($sql);
        $onlineUsers++;
        $st2->bind_param('ii', $onlineUsers, $room_id);
        $st2->execute();
    }
    //update room id to user_id
    updateUserRoom($room_id);
    //set cookie for roomId
    setcookie("room", $room_id, time() + 86400, "/");
    //return view
    header('location: ../../room.php');
}


function get_status_of_room($room_id)
{
    global $conn;

    $sql = 'select status from rooms where id=? LIMIT 1';
    $st = $conn->prepare($sql);
    $st->bind_param('i', $room_id);
    $st->execute();
    $res = $st->get_result();

    return $res->fetch_array(MYSQLI_ASSOC);
}

//get room info -- post (needs id)
function get_room_info($room_id)
{
    global $conn;

    $sql = 'select * from rooms where id=? LIMIT 1';
    $st = $conn->prepare($sql);
    $st->bind_param('i', $room_id);
    $st->execute();
    $res = $st->get_result();

    print json_encode($res->fetch_array(MYSQLI_ASSOC));
}

function getGameStatus($id)
{
    global $conn;

    $sql = 'select status from rooms where id=?';
    $st = $conn->prepare($sql);
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();

    print json_encode($res->fetch_array(MYSQLI_ASSOC));
}

function getOnlinePlayersByRoomId($roomId)
{
    global $conn;

    $stmt = $conn->prepare('select id,name from users where room_id=? order by log_in_time asc');
    $stmt->bind_param('s', $roomId);
    $stmt->execute();
    $result = $stmt->get_result();

    print json_encode($result->fetch_all(MYSQLI_ASSOC));
}


function updateUserRoom($roomId)
{
    global $conn;

    session_start();
    $obj = json_decode($_SESSION["user"]);

    $stmt = $conn->prepare('update users set room_id=? WHERE id=?');
    $stmt->bind_param('ss', $roomId, $obj->id);
    $stmt->execute();
}

function setGameOwner($roomId)
{
    global $conn;

    session_start();
    $obj = json_decode($_SESSION["user"]);

    $stmt = $conn->prepare('select id from users where room_id=? order by log_in_time asc limit 1');
    $stmt->bind_param('s', $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    $owner = $result->fetch_all(MYSQLI_ASSOC);

    $stmt = $conn->prepare('update rooms set owner_id=? WHERE id=?');
    $stmt->bind_param('ss', $owner[0]["id"], $roomId);
    $stmt->execute();

    if ((int)$owner[0]["id"] == $obj->id)
        $_SESSION["ownerOf"] = $roomId;
}

function getGameOwner()
{
    //$player = null;
    session_start();
    if (isset($_SESSION["ownerOf"]) && isset($_COOKIE["room"])) {
        if ((int)$_SESSION["ownerOf"] == (int)$_COOKIE["room"])
            return json_decode($_SESSION["user"])->id;
    } else {
        global $conn;
        $stmt = $conn->prepare('select id from users where room_id=? order by log_in_time asc limit 1');
        $stmt->bind_param('s', $roomId);
        $stmt->execute();
        $result = $stmt->get_result();
        $owner = $result->fetch_all(MYSQLI_ASSOC);
        return $owner[0]["id"];
    }
}

function startGame()
{
    //people in room + other requirements
    //playing now
    if (isset($_COOKIE["room"]) && empty($_COOKIE["room"]) || (!isset($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "roomisnotavailable."]);
        exit;
    }

    $playersTurn = getGameOwner();
    /* var_dump($playersTurn);
    exit; */
    $gameEnded = "0";

    global $conn;
    $stmt = $conn->prepare('insert into game_status(player_turn_id ,room_id ,game_ended) values (?,?,?);');
    $stmt->bind_param('sss', $playersTurn, $_COOKIE["room"], $gameEnded);
    $stmt->execute();
}
