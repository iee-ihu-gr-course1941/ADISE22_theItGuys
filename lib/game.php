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
    if (is_null($room_id) || empty($room_id)) {
        print json_encode(['errormesg' => "Room not found."]);
        exit;
    }
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
    if (is_null($room_id) || empty($room_id)) {
        print json_encode(['errormesg' => "Room not found."]);
        exit;
    }
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
    if (is_null($room_id) || empty($room_id)) {
        print json_encode(['errormesg' => "Room not found."]);
        exit;
    }
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
    if (is_null($id) || empty($id)) {
        print json_encode(['errormesg' => "Room not found."]);
        exit;
    }
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
    session_start();
    if (!isset($_SESSION["user"])) {
        print json_encode(['errormesg' => "userNotFound."]);
        exit;
    }

    global $conn;

    $stmt = $conn->prepare('select id,name from users where room_id=? order by log_in_time asc');
    $stmt->bind_param('s', $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    $usersInRoom = $result->fetch_all(MYSQLI_ASSOC);

    $curUserPos = -1;
    for ($i = 0; $i < sizeof($usersInRoom); $i++) {
        if ((int)json_decode($_SESSION["user"])->id == $usersInRoom[$i]["id"])
            $curUserPos = $i;
    }
    if ($curUserPos != -1 && $curUserPos != 0) {
        $curUser = $usersInRoom[$curUserPos];
        unset($usersInRoom[$curUserPos]);
        array_unshift($usersInRoom, $curUser);
    }

    print json_encode($usersInRoom);
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
        $stmt->bind_param('s', $_COOKIE["room"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $owner = $result->fetch_all(MYSQLI_ASSOC);
        return $owner[0]["id"];
    }
}

function startGame()
{
    //people in room + other requirements
    if (isset($_COOKIE["room"]) && empty($_COOKIE["room"]) || (!isset($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "roomisnotavailable."]);
        exit;
    }
    $playersTurn = getGameOwner();
    $gameEnded = "0";

    global $conn;
    $check = $conn->prepare('SELECT * FROM game_status WHERE room_id=?');
    $check->bind_param('s', $_COOKIE["room"]);
    $check->execute();
    $result = $check->get_result();

    if (!empty($result->fetch_all(MYSQLI_ASSOC))) {
        print json_encode(['errormesg' => "Game has already started."]);
        exit;
    }

    $stmt = $conn->prepare('INSERT INTO game_status(player_turn_id ,room_id ,game_ended) VALUES (?,?,?);');
    $stmt->bind_param('sss', $playersTurn, $_COOKIE["room"], $gameEnded);
    $stmt->execute();

    createDeckOfCardsAndSplit();
}

function getOwnerInfo()
{
    $info = getGameOwner();
    if (isset($_SESSION["user"]))
        if (json_decode($_SESSION["user"])->id == (int)$info) {
            print "true";
            exit;
        } else {
            print "false";
            exit;
        }
    else {
        print json_encode(['errormesg' => "ownerdoesnotexist."]);
        exit;
    }
}

function createDeckOfCardsAndSplit()
{
    $suits = ["♦", "♥", "♣", "♠"];
    $values = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];
    $deckOfCards = array();

    $usersInGame = []; //"13", "4", "3", "6"

    //get users in room (temp)
    global $conn;
    $stmt = $conn->prepare('select id from users where room_id=? order by log_in_time asc');
    $stmt->bind_param('s', $_COOKIE["room"]);
    $stmt->execute();
    $result = $stmt->get_result();

    foreach ($result->fetch_all(MYSQLI_ASSOC) as $user) {
        array_push($usersInGame, $user["id"]);
    }

    for ($i = 0; $i < sizeof($suits); $i++)
        for ($j = 0; $j < sizeof($values); $j++)
            array_push($deckOfCards, array("value" => $values[$j], "suit" => $suits[$i], "user" => null));

    shuffle($deckOfCards);

    $usersInGameIndex = 0;
    for ($i = 0; $i < sizeof($deckOfCards); $i++) {
        if ($i % 13 == 0 && $i <= 42 && $i != 0)
            $usersInGameIndex++;

        $deckOfCards[$i]["user"] = $usersInGame[$usersInGameIndex];
    }
    //save to db
    addCardsToDb($deckOfCards);
}

function addCardsToDb($deckOfCards)
{
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "roomDoesNotExist."]);
        exit;
    }

    global $conn;

    foreach ($deckOfCards as $card) {
        $stmt = $conn->prepare('insert into bluff(card_number ,card_style ,user_id, room_id) values (?,?,?,?);');
        $stmt->bind_param('ssss', $card["value"], $card["suit"], $card["user"], $_COOKIE["room"]);
        $stmt->execute();
    }
}

function getMyCards($method)
{
    session_start();
    if (strcmp($method, "POST") == 0) {
        print json_encode(['errormesg' => "serverSide."]);
        exit;
    }
    if (!isset($_SESSION["user"])) {
        print json_encode(['errormesg' => "userNotFound."]);
        exit;
    }
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "roomDoesNotExist."]);
        exit;
    }

    global $conn;
    $stmt = $conn->prepare('select id, card_number, card_style from bluff where user_id=? AND room_id=? AND actions IS NULL order by card_style');
    $stmt->bind_param('ss', json_decode($_SESSION["user"])->id, $_COOKIE["room"]);
    $stmt->execute();
    $result = $stmt->get_result();

    print json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function playMyBluff($method, $valueOfCardsPlayed, $cardsPlayed)
{
    session_start();
    if (strcmp($method, "GET") == 0) {
        print json_encode(['errormesg' => "pathNotFound."]);
        exit;
    }
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "roomDoesNotExist."]);
        exit;
    }
    if (!isset($_SESSION["user"])) {
        print json_encode(['errormesg' => "userNotFound."]);
        exit;
    }

    $numOfCardsPlayed = sizeof($cardsPlayed);

    global $conn;
    $stmt = $conn->prepare('select id from users where room_id=? order by log_in_time asc');
    $stmt->bind_param('s', $_COOKIE["room"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);

    $curPlayingUser = null;
    for ($i = 0; $i < sizeof($users); $i++) {
        if ((int)json_decode($_SESSION["user"])->id === $users[$i]["id"])
            if ($i < 3)
                $curPlayingUser = $i;
            else
                $curPlayingUser = -1;
    }

    //update game status 
    $stmt = $conn->prepare('UPDATE game_status SET player_turn_id=?, num_of_cards_played=?, value_of_cards_played=?, played_by=? WHERE room_id=?');
    $stmt->bind_param('iisss', $users[$curPlayingUser + 1]["id"], $numOfCardsPlayed, $valueOfCardsPlayed, json_decode($_SESSION["user"])->id, $_COOKIE["room"]);
    $stmt->execute();

    //update bluff table
    foreach ($cardsPlayed as $card) {
        $cards_stmt = $conn->prepare('UPDATE bluff SET actions_timestamp=now(), actions="played" WHERE id=?');
        $cards_stmt->bind_param('s', $card);
        $cards_stmt->execute();
    }
}

function getGameInfo($method)
{
    if (strcmp($method, "POST") == 0) {
        print json_encode(['errormesg' => "pathNotFound."]);
        exit;
    }
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "roomDoesNotExist."]);
        exit;
    }
    global $conn;
    checkIfWinner($_COOKIE["room"]);
    $stmt = $conn->prepare('SELECT users.name AS "played_by", num_of_cards_played, value_of_cards_played, passes, first_winner_id FROM game_status JOIN users ON users.id = game_status.played_by WHERE game_status.room_id=?');
    $stmt->bind_param('s', $_COOKIE["room"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $lastBluffInfo = $result->fetch_all(MYSQLI_ASSOC);

    $stmt2 = $conn->prepare('SELECT users.name AS "playing_now" FROM game_status JOIN users ON users.id = game_status.player_turn_id WHERE game_status.room_id=?');
    $stmt2->bind_param('s', $_COOKIE["room"]);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $userPlayingNow = $result->fetch_all(MYSQLI_ASSOC)[0];

    $fullData = array_merge($lastBluffInfo, $userPlayingNow);

    if (empty($lastBluffInfo)) {
        print json_encode($fullData);
        exit;
    } else {
        $fullData = array_merge($lastBluffInfo[0], $userPlayingNow);
        print json_encode($fullData);
    }
}

function callBluff($method)
{
    session_start();
    if (strcmp($method, "POST") == 0) {
        print json_encode(['errormesg' => "Path Not Found."]);
        exit;
    }
    if (!isset($_SESSION["user"])) {
        print json_encode(['errormesg' => "userNotFound."]);
        exit;
    }
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "Room does not exist."]);
        exit;
    }

    global $conn;
    $stmt = $conn->prepare('SELECT played_by, num_of_cards_played, value_of_cards_played FROM game_status WHERE game_status.room_id=?');
    $stmt->bind_param('s', $_COOKIE["room"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $lastBluffInfo = $result->fetch_all(MYSQLI_ASSOC)[0];

    $cardsStmt = $conn->prepare('SELECT card_number, card_style FROM bluff WHERE actions="played" AND room_id=? AND user_id=? ORDER BY actions_timestamp');
    $cardsStmt->bind_param('si', $_COOKIE["room"], $lastBluffInfo["played_by"]);
    $cardsStmt->execute();
    $result = $cardsStmt->get_result();
    $playedCards = $result->fetch_all(MYSQLI_ASSOC);

    $hasBluffed = false;
    foreach ($playedCards as $card) {
        if (strcmp($card["card_number"], $lastBluffInfo["value_of_cards_played"]) != 0) {
            $hasBluffed = true;
            break;
        }
    }

    if (!$hasBluffed) {
        $cards_stmt = $conn->prepare('UPDATE game_status SET player_turn_id=? WHERE room_id=?');
        $cards_stmt->bind_param('is', $lastBluffInfo["played_by"], $_COOKIE["room"]);
        $cards_stmt->execute();
    }

    if ($hasBluffed)
        print json_encode(["result" => $hasBluffed, "cards" => $playedCards, "playerForBank" => $lastBluffInfo["played_by"]]);

    if (!$hasBluffed)
        print json_encode(["result" => $hasBluffed, "cards" => $playedCards, "playerForBank" => (int)json_decode($_SESSION["user"])->id]);
}

function getCardsFromCalledBluff($method, $userToCollectBank)
{
    if (strcmp($method, "GET") == 0) {
        print json_encode(['errormesg' => "Path Not Found."]);
        exit;
    }
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "Room does not exist."]);
        exit;
    }
    if (is_null($userToCollectBank) || empty($userToCollectBank)) {
        print json_encode(['errormesg' => "User to get cards is not set."]);
        exit;
    }
    global $conn;

    $stmt = $conn->prepare('UPDATE bluff SET user_id=? WHERE (actions="bank" OR actions="played") AND room_id=?');
    $stmt->bind_param('ss', $userToCollectBank, $_COOKIE["room"]);
    $stmt->execute();

    $CleanCardsStmt = $conn->prepare('UPDATE bluff SET actions=NULL, actions_timestamp=NULL WHERE user_id=? AND room_id=?');
    $CleanCardsStmt->bind_param('ss', $userToCollectBank, $_COOKIE["room"]);
    $CleanCardsStmt->execute();
    //remove return or change
    print json_encode(["success" => "User " . $userToCollectBank . " collected the cards"]);
}

function passAction($method)
{
    session_start();
    if (strcmp($method, "POST") == 0) {
        print json_encode(['errormesg' => "Path not found."]);
        exit;
    }
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "Room does not exist."]);
        exit;
    }
    if (!isset($_SESSION["user"])) {
        print json_encode(['errormesg' => "User not found."]);
        exit;
    }
    global $conn;

    $stmt = $conn->prepare('select id from users where room_id=? order by log_in_time asc');
    $stmt->bind_param('s', $_COOKIE["room"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);

    $curPlayingUser = null;
    for ($i = 0; $i < sizeof($users); $i++) {
        if ((int)json_decode($_SESSION["user"])->id === $users[$i]["id"])
            if ($i < 3)
                $curPlayingUser = $i;
            else
                $curPlayingUser = -1;
    }

    $check = $conn->prepare('SELECT passes FROM game_status WHERE room_id=?');
    $check->bind_param('s', $_COOKIE["room"]);
    $check->execute();
    $result = $check->get_result();
    $passCount = $result->fetch_all(MYSQLI_ASSOC)[0]["passes"];

    if ($passCount == 3) {
        print json_encode("Pass can not be submitted you can only play!");
        exit;
    }
    if ($passCount < 3) {
        $updatePass = $conn->prepare('UPDATE game_status SET player_turn_id=?, passes=passes+1 WHERE room_id=?');
        $updatePass->bind_param('is', $users[$curPlayingUser + 1]["id"], $_COOKIE["room"]);
        $updatePass->execute();
    }
}

function resetGamePasses($method)
{
    if (strcmp($method, "GET") == 0) {
        print json_encode(['errormesg' => "Path not found."]);
        exit;
    }
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "Room does not exist."]);
        exit;
    }

    global $conn;

    $clearPass = $conn->prepare('UPDATE game_status SET passes=0 WHERE room_id=?');
    $clearPass->bind_param('s', $_COOKIE["room"]);
    $clearPass->execute();
}

function addCardsToBank($method)
{
    if (strcmp($method, "GET") == 0) {
        print json_encode(['errormesg' => "Path not found."]);
        exit;
    }
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "Room does not exist."]);
        exit;
    }

    global $conn;

    $clearPass = $conn->prepare('UPDATE bluff SET actions="bank", actions_timestamp=NULL WHERE actions="played" AND room_id=?');
    $clearPass->bind_param('s', $_COOKIE["room"]);
    $clearPass->execute();
}

function checkIfWinner($roomId)
{
    global $conn;
    $stmt = $conn->prepare('SELECT id,name FROM users WHERE room_id=? ORDER BY log_in_time asc');
    $stmt->bind_param('s', $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    $usersInRoom = $result->fetch_all(MYSQLI_ASSOC);

    $checkWinner = $conn->prepare('SELECT count(*) AS "cards_number",user_id FROM bluff WHERE room_id=? AND user_id=?');
    foreach ($usersInRoom as $user) {
        $checkWinner->bind_param('ss', $roomId, $user["id"]);
        $checkWinner->execute();
        $result = $checkWinner->get_result();
        $cardsCount = $result->fetch_all(MYSQLI_ASSOC)[0]["cards_number"];
        if ($cardsCount == 0) {
            $setWinner = $conn->prepare('UPDATE game_status SET first_winner_id=? WHERE room_id=?');
            $setWinner->bind_param('ss', $user["id"], $roomId);
            $setWinner->execute();
        }
    }
}

function getGameWinner($method)
{
    if (strcmp($method, "GET") == 0) {
        print json_encode(['errormesg' => "Path not found."]);
        exit;
    }
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "Room does not exist."]);
        exit;
    }
    global $conn;
    $stmt = $conn->prepare('SELECT users.name AS "winner" FROM game_status JOIN users ON game_status.first_winner_id=users.id WHERE game_status.room_id=?;');
    $stmt->bind_param('s', $_COOKIE["room"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $winner = $result->fetch_all(MYSQLI_ASSOC)[0];

    print json_encode($winner);
}

function restoreRoomAndClean($method)
{
    session_start();
    if (strcmp($method, "GET") == 0) {
        print json_encode(['errormesg' => "Path not found."]);
        exit;
    }
    if (!isset($_COOKIE["room"]) || (isset($_COOKIE["room"]) && empty($_COOKIE["room"]))) {
        print json_encode(['errormesg' => "Room does not exist."]);
        exit;
    }
    if (!isset($_SESSION["user"])) {
        print json_encode(['errormesg' => "User not found.. Action can not be performed!"]);
        exit;
    }
    global $conn;

    $delete1 = $conn->prepare('DELETE FROM bluff WHERE room_id=?;');
    $delete1->bind_param('s', $_COOKIE["room"]);
    $delete1->execute();

    $delete2 = $conn->prepare('DELETE FROM game_status WHERE room_id=?;');
    $delete2->bind_param('s', $_COOKIE["room"]);
    $delete2->execute();

    $tidy = $conn->prepare('UPDATE rooms SET owner_id=NULL, users_online=0, status="empty" WHERE id=?;');
    $tidy->bind_param('s', $_COOKIE["room"]);
    $tidy->execute();

    $updateUsers = $conn->prepare('UPDATE users SET room_id=NULL WHERE room_id=?;');
    $updateUsers->bind_param('s', $_COOKIE["room"]);
    $updateUsers->execute();
}
