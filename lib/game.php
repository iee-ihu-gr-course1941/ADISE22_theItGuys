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

    //get info about this room -- getting them on load without dynamic id
    //$roomData = get_room_info($b);

    //return view
    header('location: ../../room.html');
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

    if ((int)$onlineUsers + 1 == 4) {
        //update online users and status to full
        $sql = 'UPDATE rooms SET status="full", users_online=? WHERE id=?';
        $st2 = $conn->prepare($sql);
        $onlineUsers++;
        $st2->bind_param('ii', $onlineUsers, $room_id);
        $st2->execute();
    }
    if ((int)$onlineUsers + 1 < 4) {
        //add only user and make room status pending
        $sql = 'UPDATE rooms SET status="pending", users_online=? WHERE id=?';
        $st2 = $conn->prepare($sql);
        $onlineUsers++;
        $st2->bind_param('ii', $onlineUsers, $room_id);
        $st2->execute();
    }
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

    $users = array();

    $stmt = $conn->prepare('select distinct user_id from bluff where room_id=?');
    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        array_push($users, $row['user_id']);
    }

    print json_encode($users);
}
