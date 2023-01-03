<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <link rel="stylesheet" href="css_external.css">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <!--  -->
    <title>Room</title>
</head>

<body>
    <?php
    session_start();

    if (isset($_SESSION['user'])) {
        $json = json_decode($_SESSION['user']);
        $name =  (string)$json->name;
    }

    if (isset($_COOKIE["room"])) {
        $room = $_COOKIE["room"];
    }

    ?>

    <div class="container-fluid bg-image">
        <div class="row">
            <div class="col-12 text-center text-white d-flex flex-row justify-content-between">
                <!-- bg-text -->
                <h3 id="roomTitle"></h3>

                <?php
                if (isset($_SESSION['user'])) {
                    echo '<h5>userName:  ' . $name . '</h5>';
                }
                ?>
            </div>
        </div>
    </div>
    <style>
        .bg-image {
            /* The image used */
            background-image: url("room.jpg");
            height: 100vh;
            background-size: cover;
        }

        .bg-text {
            background-color: rgba(0, 0, 0, 0.038);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.086);
            /* Black w/opacity/see-through */
            color: white;
            font-weight: bold;
            border: 3px solid #f1f1f1;
            position: absolute;
            top: 15%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            width: 80%;
            padding: 10px;
            text-align: center;
        }
    </style>

    <script>
        $(document).ready(function() {
            var deckOfCardsArray = shuffle(deckOfCards());
            var roomID = <?php if (isset($_COOKIE["room"]) && !is_null($_COOKIE["room"])) echo $_COOKIE["room"]; ?>;
            $.ajax({
                url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/getInfo",
                method: "POST",
                data: {
                    room_id: roomID,
                },
                success: function(response) {
                    var obj = jQuery.parseJSON(response);
                    $("#roomTitle").append(obj.name);
                    $("body").append("<h1>ID: " + obj.id + "</h1>" + "<h1>Name: " + obj.name + "</h1>" + "<h1>users online: " + obj.users_online + "</h1>" + "<h1>status: " + obj.status + "</h1>");
                },
            });

            //get rooms players
            function getRoomPlayers(roomID) {
                var resposeArray;
                $.ajax({
                    url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/getRoomPlayers",
                    type: "POST",
                    data: {
                        roomId: roomID,
                    },
                    success: function(response) {
                        resposeArray = JSON.parse(response);
                    },
                    error: function(response) {
                        console.log(response.error);
                    },
                });
            }

            //cards stuff
            function deckOfCards() {
                let suits = ["♦", "♥", "♣", "♠"];
                let values = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];

                let deckOfCardsArray = [];
                for (var i = 0; i < suits.length; i++)
                    for (var j = 0; j < values.length; j++) deckOfCardsArray.push(values[j] + suits[i]);

                return deckOfCardsArray;
            }

            var intervalRoomStatus = window.setInterval(function() {
                /* console.log($("#roomTitle").text()); */
                $.ajax({
                    url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/getGameStatus",
                    type: "POST",
                    data: {
                        id: roomID,
                    },
                    success: function(response) {
                        var obj = JSON.parse(response);
                        /* console.log(obj); */
                        if (obj.status === "pending" || obj.status === "empty") {
                            return;
                        } else {
                            //find players
                            getRoomPlayers(roomID);
                        }
                    },
                    error: function(response) {
                        console.log(response.error);
                    },
                });
            }, 2500);
        });

        //shuffle array of cards
        function shuffle(array) {
            let currentIndex = array.length,
                randomIndex;

            // While there remain elements to shuffle.
            while (currentIndex != 0) {
                // Pick a remaining element.
                randomIndex = Math.floor(Math.random() * currentIndex);
                currentIndex--;

                // And swap it with the current element.
                [array[currentIndex], array[randomIndex]] = [array[randomIndex], array[currentIndex]];
            }

            return array;
        }
    </script>
</body>

</html>