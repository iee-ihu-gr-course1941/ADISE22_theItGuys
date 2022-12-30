<?php
session_start();
if (isset($_COOKIE['room'])) {
    unset($_COOKIE['room']);
    setcookie('room', null, -1, '/');
}

if (isset($_SESSION['user'])) {
    $json = json_decode($_SESSION['user']);
    $name =  (string)$json->name;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <!-- bluff script -->
    <script src="js/bluff.js"></script>
    <title>Bluff game</title>
</head>

<body>
    <div class="container-fluid bg-image">
        <div id="showAvaibleRooms">
            <div class="row">
                <span class="d-flex justify-content-between">
                    <span class="d-flex gap-1">
                        <h2 class="text-center">Available Rooms:</h2>
                        <h2 id="totalRooms"></h2>
                    </span>
                    <?php
                    if (isset($_SESSION['user'])) {
                        echo '<h5>userName:  ' . $name . '</h5>';
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" tabindex="-1" id="usernameModal" style="background-color: rgba(0, 0, 0, 0.534)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Choose your username</h5>
                </div>
                <div class="modal-body">
                    <div id="chooseUsernameForm" class="d-flex flex-column gap-2">
                        <input type="text" placeholder="username..." id="pickedUsername" />
                        <button id="submitUsername" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--  -->

    <style>
        html,
        body {
            box-sizing: border-box;
            padding: 0;
            margin: 0;
            width: 100%;
        }

        .bg-image {
            /* The image used */
            background-image: url("homeBg.jpg");
            height: 100vh;
            /* Center and scale the image nicely */
            background-size: cover;
        }

        .box {
            width: 160px;
            height: 160px;
            border: 1px solid black;
            background-color: #008000;
        }
    </style>

    <script>
        $(document).ready(function() {
            //check if user needs to login
            var userName = <?php if (!empty($_SESSION['user'])) echo $_SESSION['user'];
                            else echo "null"; ?>;
            if (userName = '' || userName == null) {
                has_user_logged_in();
            }

            //get all rooms except full
            $.ajax({
                url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/bluff/",
                type: "POST",
                success: function(response) {
                    var count = 0;
                    //console.log(response.records.length);
                    for (var i = 1; i <= response.records.length; i++) {
                        if (i == 1 || (i - 1) % 3 == 0) $("#showAvaibleRooms").append('<div class="row"></div>');
                        //get last row of page
                        $(".row:last-child").append('<a href="http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/' + response.records[i - 1].id + '" ><div class="box"><h3 class="boxTitle">' + response.records[i - 1].name + "</h3></div></a>");
                    }
                    //set avaible rooms
                    $("#totalRooms").text(response.count);
                },
            });

            //set interval every 5 sec to reload avaible rooms
            var intervalId = window.setInterval(function() {
                if ($("#totalRooms").text() !== "6") {
                    $.ajax({
                        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/bluff/getTotalRooms",
                        type: "POST",
                        success: function(response) {
                            $("#totalRooms").empty();
                            $("#totalRooms").text(response);
                        },
                        error: function(response) {
                            console.log(response.error);
                        },
                    });
                }
            }, 5000);
            //
        });
    </script>

</body>

</html>