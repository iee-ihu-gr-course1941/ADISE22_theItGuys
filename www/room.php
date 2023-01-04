<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <link rel="stylesheet" href="./css_external.css">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="js/room.js"></script>
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
            <input type="hidden" id="awesome" value="<?php if (isset($_COOKIE["room"]) && !is_null($_COOKIE["room"])) echo $_COOKIE["room"]; ?>">
            <div class="col-12 text-center text-white d-flex flex-row justify-content-between">
                <h3 id="roomTitle"></h3>

                <?php
                if (isset($_SESSION['user'])) {
                    echo '<h5>Username:  ' . $name . '</h5>';
                }
                ?>
            </div>
        </div>
        <!-- emfanisi xristwn -->
        <div class="row">
            <div class="col-12">
                <div class="m-auto d-flex flex-column text-center" style="width:fit-content;">
                    <img src="userImg/user_icon_red.png" class="userIcon m-auto" alt="">
                    <p id="userThree" class="text-light"></p>
                </div>
            </div>
        </div>
        <div class="row" style="height:60vh">
            <div class="col-2 d-flex justify-content-center align-items-center">
                <div class="m-auto d-flex flex-column text-center" style="width:fit-content;">
                    <img src="userImg/user_icon_yellow.png" class="userIcon m-auto" alt="">
                    <p id="userFour" class="text-light"></p>
                </div>
            </div>
            <div class="col-8">

            </div>
            <div class="col-2 d-flex justify-content-center align-items-center">
                <div class="m-auto d-flex flex-column text-center" style="width:fit-content;">
                    <img src="userImg/user_icon_white.png" class="userIcon m-auto" alt="">
                    <p id="userTwo" class="text-light"></p>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12 bg-dark text-center" style="height: 25vh;">
                <div id="myCardsDisplay" class="justify-content-center">

                </div>
                <button class="btn btn-warning mt-4">Play your cards</button>
            </div>
        </div>
        <!-- -->
    </div>

    <style>
        .bg-image {
            background-image: url("room.jpg");
            height: 100vh;
            background-size: cover;
        }
        #myCardsDisplay{
            display: flex;
        }
    </style>



</body>

</html>