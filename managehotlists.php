<?php
include "./config.php";

$force_login_redirect = true;
include strval($manifest_config["auth"]["provider"]);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo htmlspecialchars($manifest_config["product_name"]); ?> - Manage Predator Hot-lists</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
        <link rel="stylesheet" href="./styles/main.css">
    </head>
    <body class="truebody">
        <main class="mainbody centeredsection">
            <div class="navbar">
                <a class="button" href="index.php">Back</a>
            </div>
            <h1><?php echo htmlspecialchars($manifest_config["product_name"]); ?></h1>
            <h2>Hot-List Management</h2>
            <p>This tool allows system users to manage license plate hot-lists for Predator instances. It should be noted that this tool only affects Predator instances that have this source in their alert list sources.</p>
            <p>To add this list as a source, simply add the following URL to the Predator configuration: 
            <?php
            $serve_url = "./serve.php?type=hot";
            if ($username !== $manifest_config["files"]["hotlist"]["active_id"]) {
                $serve_url .= "&id=" . $username;
            }
            if (strlen($manifest_config["users"][$username]["settings"]["access_key"]) > 0) {
                $serve_url .= "&key=" . $manifest_config["users"][$username]["settings"]["access_key"];
            }
            echo "<a href='" . $serve_url . "'>Source</a>";
            ?>
            </p>
            <hr>
            <?php
            if (!file_exists($manifest_config["files"]["hotlist"]["path"])) { // Check to see if the file doesn't already exists.
                file_put_contents($manifest_config["files"]["hotlist"]["path"], "{}"); // Create a blank list file.
            }
            if (!is_writable($manifest_config["files"]["hotlist"]["path"])) { // Check to see if the file isn't writable to PHP.
                echo "<p>Error: The file is not writable to PHP. Hot lists can't be edited.</p>";
                exit();
            }

            $hotlist = json_decode(file_get_contents($manifest_config["files"]["hotlist"]["path"]), true); // Load the hot list contents from the file.
            $hotlist["meta"]["type"] = "hotlist";


            if ($manifest_config["users"][$username]["permissions"]["list_capacity_hot"] > -1) { // Check to see if an individual override is set for this user's list capacity.
                $users_max_list_size = $manifest_config["users"][$username]["permissions"]["list_capacity_hot"];
            } else { // Otherwise, use the default list capacity.
                $users_max_list_size = $manifest_config["permissions"]["max_size"]["hot"];
            }


            if ($_POST["submit"] == "Add") {
                if (sizeof($hotlist["lists"][$username]["contents"]) < $users_max_list_size) { // Check to see if this user's list is smaller than its max capacity.
                    $plate_to_add = strtoupper($_POST["plate"]); // Get the plate to add to the hot list from the POST data.
                    $hotlist["lists"][$username]["contents"][$plate_to_add]["name"] = $_POST["name"];
                    $hotlist["lists"][$username]["contents"][$plate_to_add]["description"] = $_POST["description"];
                    $hotlist["lists"][$username]["contents"][$plate_to_add]["make"] = $_POST["make"];
                    $hotlist["lists"][$username]["contents"][$plate_to_add]["model"] = $_POST["model"];
                    $hotlist["lists"][$username]["contents"][$plate_to_add]["year"] = $_POST["year"];
                    $hotlist["lists"][$username]["contents"][$plate_to_add]["author"] = $_POST["author"];
                    $hotlist["lists"][$username]["contents"][$plate_to_add]["source"] = $_POST["source"];
                    file_put_contents($manifest_config["files"]["hotlist"]["path"], json_encode($hotlist, JSON_PRETTY_PRINT)); // Save the modified list to disk.
                } else {
                    echo "<p>Your list already contains the maximum number of allowed entries. Please either remove existing list entries or upgrade your account.</p>";
                }
            } else if ($_POST["submit"] == "Remove") { // Check to see if a plate to remove was submitted.
                unset($hotlist["lists"][$username]["contents"][$_POST["plate"]]); // Remove the plate from the dictionary
                file_put_contents($manifest_config["files"]["hotlist"]["path"], json_encode($hotlist, JSON_PRETTY_PRINT)); // Save the modified list to disk.
            }
            ?>
            <div class="basicform">
                <h3>Add Plate</h3>
                <?php
                    echo "<p>You have used <b>" . sizeof($hotlist["lists"][$username]["contents"]) . "/" . $users_max_list_size . "</b> allowed list entries. Entries can be removed to make more space.</p>";
                ?>
                <form method="POST">
                    <label for="plate">Plate:</label> <input type="string" name="plate" id="plate" placeholder="Plate" value="<?php echo $_GET["plate"]; ?>"><br>
                    <label for="name">Name:</label> <input type="string" name="name" id="name" placeholder="Name" value="<?php echo $hotlist["lists"][$username]["contents"][$_GET["plate"]]["name"]; ?>"><br>
                    <label for="description">Description:</label> <input type="string" name="description" id="description" placeholder="Description" value="<?php echo $hotlist["lists"][$username]["contents"][$_GET["plate"]]["description"]; ?>"><br>
                    <br>
                    <label for="make">Make:</label> <input type="string" name="make" id="make" placeholder="Make" value="<?php echo $hotlist["lists"][$username]["contents"][$_GET["plate"]]["make"]; ?>"><br>
                    <label for="model">Model:</label> <input type="string" name="model" id="model" placeholder="Model" value="<?php echo $hotlist["lists"][$username]["contents"][$_GET["plate"]]["model"]; ?>"><br>
                    <label for="year">Year:</label> <input type="string" name="year" id="year" placeholder="Year" value="<?php echo $hotlist["lists"][$username]["contents"][$_GET["plate"]]["year"]; ?>"><br>
                    <br>
                    <label for="author">Author:</label> <input type="string" name="author" id="author" placeholder="Author" value="V0LT" value="<?php echo $hotlist["lists"][$username]["contents"][$_GET["plate"]]["author"]; ?>"><br>
                    <label for="source">Source:</label> <input type="string" name="source" id="source" placeholder="Source" value="<?php echo $hotlist["lists"][$username]["contents"][$_GET["plate"]]["source"]; ?>"><br>
                    <input type="submit" name="submit" value="Add" class="button">
                </form>
            </div>
            <hr>
            <div class="basicform">
                <h3>Remove Plate</h3>
                <form method="POST">
                    <label for="plate">Plate:</label> <input type="string" name="plate" id="plate" placeholder="Plate" value="<?php echo $_GET["plate"]; ?>"><br>
                    <input type="submit" name="submit" value="Remove" class="button">
                </form>
            </div>
            <hr>
            <div class="basicform">
                <h3>Select Plate</h3>
                <br>
                <?php
                    foreach ($hotlist["lists"][$username]["contents"] as $key => $plate) {
                        echo "<a class='button' href='?plate=" . $key . "'>" . $key . "</a><br><br style='margin-top:5px;'>";
                    }
                ?>
            </div>
        </main>
    </body>
</html>
