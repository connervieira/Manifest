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
        <title>Manifest - Manage Predator Ignore-lists</title>
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
            <h2>Ignore-List Management</h2>
            <p>This tool allows system administrators to manage license plate ignore lists for Predator instances. It should be noted that this tool only affects Predator instances that have this server in their remote alert list sources.</p>
            <?php
            $list_id = $manifest_config["files"]["ignore"]["active_id"];

            if (!file_exists($manifest_config["files"]["ignore"]["path"])) { // Check to see if the file doesn't already exists.
                file_put_contents($manifest_config["files"]["ignore"]["path"], "{}"); // Create a blank list file.
            }
            if (!is_writable($manifest_config["files"]["ignore"]["path"])) { // Check to see if the file isn't writable to PHP.
                echo "<p>Error: The file is not writable to PHP. Ignore lists can't be edited.</p>";
                exit();
            }

            $ignore_list = json_decode(file_get_contents($manifest_config["files"]["ignore"]["path"]), true); // Load the ignore list contents from the file.
            $ignore_list["meta"]["type"] = "ignorelist";


            if ($_POST["submit"] == "Add") {
                array_push($ignore_list[$list_id]["contents"], strtoupper($_POST["plate"]));
                file_put_contents($manifest_config["files"]["ignore"]["path"], json_encode($ignore_list, JSON_PRETTY_PRINT)); // Save the modified list to disk.
            } else if ($_POST["submit"] == "Remove") { // Check to see if a plate to remove was submitted.
                if (($key = array_search($_POST["plate"], $ignore_list[$list_id]["contents"])) !== false) {
                    unset($ignore_list[$list_id]["contents"][$key]);
                } else {
                    echo "<p>The specified plate does not exist in the ignore list.</p>";
                }
                file_put_contents($manifest_config["files"]["ignore"]["path"], json_encode($ignore_list, JSON_PRETTY_PRINT)); // Save the modified list to disk.
            }
            ?>
            <hr>
            <div class="basicform">
                <h3>Add Plate</h3>
                <form method="POST">
                    <label for="plate">Plate:</label> <input type="string" name="plate" id="plate" placeholder="Plate"><br>
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
                    foreach ($ignore_list as $plate) {
                        echo "<a href='?plate=" . $plate . "'>" . $plate . "</a><br><br>";
                    }
                ?>
            </div>
        </main>
    </body>
</html>
