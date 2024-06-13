<?php
include "./config.php";

$force_login_redirect = true;
include strval($manifest_config["auth"]["provider"]);

include "./authentication.php";
include "./loadlists.php";

$selected_list = $_GET["list"];

if (isset($selected_list) == false or $selected_list == "" or $selected_list == null) { // Check to see if no list is selected.
    $selected_list = "default"; // Select the default list.
}
if (!in_array($selected_list, array_keys($hotlist["lists"][$username]))) {
    echo "<p>The selected list does not exist.</p>";
    exit();
}
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
                <a class="button" href="managelists.php">Back</a>
            </div>
            <h1><?php echo htmlspecialchars($manifest_config["product_name"]); ?></h1>
            <h2>Hot-List Management</h2>
            <p style="color:#cc0000;">Currently editing: <b><?php echo $hotlist["lists"][$username][$selected_list]["name"] . " (" . $selected_list . ")"; ?></b></p>
            <p>To add this list as a source, simply add the following URL to the Predator configuration: 
            <?php
            $serve_url = "./serve.php?type=hot";
            if ($username !== $manifest_config["files"]["hotlist"]["active_id"]) {
                $serve_url .= "&id=" . $username;
            }
            if ($selected_list !== "default") {
                $serve_url .= "&list=" . $selected_list;
            }
            if (strlen($manifest_config["users"][$username]["settings"]["access_key"]) > 0) {
                $serve_url .= "&key=" . $manifest_config["users"][$username]["settings"]["access_key"];
            }
            echo "<a href='" . $serve_url . "'>Source</a>";
            ?>
            </p>
            <hr>
            <?php


            if ($_POST["submit"] == "Add") {
                if (sizeof($hotlist["lists"][$username][$selected_list]["contents"]) < $users_max_list_size) { // Check to see if this user's list is smaller than its max capacity.
                    $plate_to_add = strtoupper($_POST["plate"]); // Get the plate to add to the hot list from the POST data.
                    $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["name"] = $_POST["name"];
                    $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["description"] = $_POST["description"];
                    $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["make"] = $_POST["make"];
                    $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["model"] = $_POST["model"];
                    $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["year"] = $_POST["year"];
                    $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["author"] = $_POST["author"];
                    $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["source"] = $_POST["source"];
                    file_put_contents($manifest_config["files"]["hotlist"]["path"], json_encode($hotlist, JSON_PRETTY_PRINT)); // Save the modified list to disk.
                } else {
                    echo "<p>Your list already contains the maximum number of allowed entries. Please either remove existing list entries or upgrade your account.</p>";
                }
            } else if ($_POST["submit"] == "Remove") { // Check to see if a plate to remove was submitted.
                unset($hotlist["lists"][$username][$selected_list]["contents"][$_POST["plate"]]); // Remove the plate from the dictionary
                file_put_contents($manifest_config["files"]["hotlist"]["path"], json_encode($hotlist, JSON_PRETTY_PRINT)); // Save the modified list to disk.
            }
            ?>
            <div class="basicform">
                <h3>Add Plate</h3>
                <?php
                    echo "<p>You have used <b>" . sizeof($hotlist["lists"][$username][$selected_list]["contents"]) . "/" . $users_max_list_size . "</b> allowed list entries. Entries can be removed to make more space.</p>"; // TODO: Count the size of all lists.
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
                    if (sizeof($hotlist["lists"][$username][$selected_list]["contents"]) > 0){
                        foreach ($hotlist["lists"][$username][$selected_list]["contents"] as $key => $plate) {
                            echo "<a class='button' href='?plate=" . $key . "'>" . $key . "</a><br><br style='margin-top:5px;'>";
                        }
                    } else {
                        echo "<p><i>This list is empty.</i></p>";
                    }
                ?>
            </div>
        </main>
    </body>
</html>
