<?php


require_once "../lib/DBConnection.php";

function show_rooms($type)
{
    global $conn;
    //check_abort();
    $sql = 'SELECT * FROM rooms WHERE status != "full"';
    $st = $conn->prepare($sql);

    $st->execute();
    $res = $st->get_result();

    if (strcmp($type, "full") == 0)
        $response = array("count" => getTotalRooms(), "records" => $res->fetch_all(MYSQLI_ASSOC));
    if (strcmp($type, "numOfRooms") == 0)
        $response = getTotalRooms();

    header('Content-type: application/json');
    print json_encode($response);
}

function getTotalRooms()
{
    global $conn;
    //check_abort();
    $sql = 'SELECT count(*) AS count FROM rooms WHERE status != "full"';
    $st = $conn->prepare($sql);

    $st->execute();
    $res = $st->get_result();
    $countObj = $res->fetch_object();
    return $countObj->count;
}
