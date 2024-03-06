<?php
include "./config.php";

$force_login_redirect = true;
include strval($manifest_config["auth"]["provider"]);

if (in_array($username, $manifest_config["auth"]["admins"]) == false) { // Check to see if this user is authorized to be here.
    echo "<p>This tool is for use only by V0LT Administrators!</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Manifest</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="/files/fonts/lato/latofonts.css">
        <link rel="stylesheet" href="/styles/main.css">
        <?php include "../../loadtheme.php"; ?>
    </head>
    <body class="truebody">
        <main class="mainbody centeredsection">
            <h1>Manifest</h1>
            <p>This tool allows administrators to manage license plate lists for Predator instances. It should be noted that this tool only affects Predator instances that have this server in their remote alert list sources.</p>
            <hr>
            <div class="centered">
                <a class="button" href="./managehotlists.php">Hot Lists</a>
                <a class="button" href="./manageignorelists.php">Ignore Lists</a>
            </div>
        </main>
    </body>
</html>
