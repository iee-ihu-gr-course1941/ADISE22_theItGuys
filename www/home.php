<?php
session_start();
if (isset($_SESSION['user'])) {
    $json = json_decode($_SESSION['user']);
    $name =  (string)$json->name;
}

include_once "./inc/notifications.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css_external.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <!-- bluff script -->
    <script src="js/alerts.js"></script>
    <script src="js/bluff.js"></script>
    <title>Bluff game</title>
</head>

<body>
    <div class="container-fluid home-bg-image">
        <div id="showAvaibleRooms">
            <div class="row">
                <div class="col-12">
                    <div class="p-3 mb-2 infoHeading text-white">
                        <span class="d-flex justify-content-between">
                            <span class="d-flex gap-1">
                                <h2 class="text-center">Available Rooms:</h2>
                                <h2 id="totalRooms"></h2>
                            </span>
                            <?php
                            if (isset($_SESSION['user'])) {
                                echo '<h5>Username:  <b><i>' . $name . '</h5></b></i>';
                            }
                            ?>
                        </span>
                    </div>
                </div>
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
    <script>
        $(document).ready(function() {
            var userName = <?php if (!empty($_SESSION['user'])) echo $_SESSION['user'];
                            else echo "null"; ?>;
            if (userName = '' || userName == null) {
                has_user_logged_in();
            }
            $.ajax({
                url: "bluff.php/bluff/",
                type: "POST",
                success: function(response) {
                    var count = 0;
                    for (var i = 1; i <= response.records.length; i++) {
                        if (i == 1 || (i - 1) % 4 == 0) $("#showAvaibleRooms").append('<div class="row d-flex gap-3"></div>');
                        $(".row:last-child").append('<a style="width:fit-content" href="http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/' + response.records[i - 1].id + '" ><div class="box"><h3 class="boxTitle"><h3 style="color:white;text-align:center;">' + response.records[i - 1].name + "</h3></div></a>");
                    }
                    $("#totalRooms").text(response.count);
                },
            });
            var intervalId = window.setInterval(function() {
                if ($("#totalRooms").text() !== "6") {
                    $.ajax({
                        url: "bluff.php/bluff/getTotalRooms",
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
        });
    </script>
</body>

</html>