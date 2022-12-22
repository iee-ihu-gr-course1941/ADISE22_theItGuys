<?php


require_once "../lib/DBConnection.php";

function show_rooms()
{
    global $conn;
    //check_abort();
    $sql = 'select * from rooms';
    $st = $conn->prepare($sql);

    $st->execute();
    $res = $st->get_result();

    header('Content-type: application/json');
    print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}
