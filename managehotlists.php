<?php
include "./config.php";

$force_login_redirect = true;
include strval($manifest_config["auth"]["provider"]);

if (in_array($username, $manifest_config["auth"]["admins"]) == false) { // Check to see if this user is authorized to be here.
    echo "<p>This tool is for use only by administrators!</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Manifest - Manage Predator Hot-lists</title>
        <script async defer data-domain="v0lttech.com" src="/js/plausible.js"></script>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="/files/fonts/lato/latofonts.css">
        <link rel="stylesheet" href="/styles/main.css">
        <?php include "../../loadtheme.php"; ?>
    </head>
    <body class="truebody">
        <main class="mainbody centeredsection">
            <a class="button" href="index.php">Back</a>
            <h1>Predator</h1>
            <h2>Hot-List Management</h2>
            <p>This tool allows system administrators to manage license plate hot lists for Predator instances. It should be noted that this tool only affects Predator instances that have this server in their remote alert list sources.</p>
            <?php
            $list_id = $manifest_config["files"]["hotlist"]["active_id"];

            if (!file_exists($manifest_config["files"]["hotlist"]["path"])) { // Check to see if the file doesn't already exists.
                file_put_contents($manifest_config["files"]["hotlist"]["path"], "{}"); // Create a blank list file.
            }
            if (!is_writable($manifest_config["files"]["hotlist"]["path"])) { // Check to see if the file isn't writable to PHP.
                echo "<p>Error: The file is not writable to PHP. Hot lists can't be edited.</p>";
                exit();
            }

            $hotlist = json_decode(file_get_contents($manifest_config["files"]["hotlist"]["path"]), true); // Load the hot list contents from the file.

            $hotlist["meta"]["type"] = "hotlist";


            if ($_POST["submit"] == "Add") {
                $plate_to_add = strtoupper($_POST["plate"]); // Get the plate to add to the hot list from the POST data.
                $hotlist[$list_id]["contents"][$plate_to_add]["name"] = $_POST["name"];
                $hotlist[$list_id]["contents"][$plate_to_add]["description"] = $_POST["description"];
                $hotlist[$list_id]["contents"][$plate_to_add]["author"] = $_POST["author"];
                $hotlist[$list_id]["contents"][$plate_to_add]["source"] = $_POST["source"];
                file_put_contents($manifest_config["files"]["hotlist"]["path"], json_encode($hotlist, JSON_PRETTY_PRINT)); // Save the modified list to disk.
            } else if ($_POST["submit"] == "Remove") { // Check to see if a plate to remove was submitted.
                unset($hotlist[$list_id]["contents"][$_POST["plate"]]); // Remove the plate from the dictionary
                file_put_contents($manifest_config["files"]["hotlist"]["path"], json_encode($hotlist, JSON_PRETTY_PRINT)); // Save the modified list to disk.
            }
            ?>
            <hr>
            <div class="basicform">
                <h3>Add Plate</h3>
                <form method="POST">
                    <label for="plate">Plate:</label> <input type="string" name="plate" id="plate" placeholder="Plate" value="<?php echo $_GET["plate"]; ?>"><br>
                    <label for="name">Name:</label> <input type="string" name="name" id="name" placeholder="Name" value="<?php echo $hotlist[$list_id]["contents"][$_GET["plate"]]["name"]; ?>"><br>
                    <label for="description">Description:</label> <input type="string" name="description" id="description" placeholder="Description" value="<?php echo $hotlist[$list_id]["contents"][$_GET["plate"]]["description"]; ?>"><br>
                    <label for="author">Author:</label> <input type="string" name="author" id="author" placeholder="Author" value="V0LT" value="<?php echo $hotlist[$list_id]["contents"][$_GET["plate"]]["author"]; ?>"><br>
                    <label for="source">Source:</label> <input type="string" name="source" id="source" placeholder="Source" value="<?php echo $hotlist[$list_id]["contents"][$_GET["plate"]]["source"]; ?>"><br>
                    <input type="submit" name="submit" value="Add" class="formbutton">
                </form>
            </div>
            <hr>
            <div class="basicform">
                <h3>Remove Plate</h3>
                <form method="POST">
                    <label for="plate">Plate:</label> <input type="string" name="plate" id="plate" placeholder="Plate" value="<?php echo $_GET["plate"]; ?>"><br>
                    <input type="submit" name="submit" value="Remove" class="formbutton">
                </form>
            </div>
            <hr>
            <div class="basicform">
                <h3>Select Plate</h3>
                <?php
                    foreach ($hotlist[$list_id]["contents"] as $key => $plate) {
                        echo "<a href='?plate=" . $key . "'>" . $key . "</a><br><br>";
                    }
                ?>
            </div>
        </main>
    </body>
</html>
