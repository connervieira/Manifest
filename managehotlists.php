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

if ($manifest_config["users"][$username]["permissions"]["list_capacity"]["hot"] > -1) { // Check to see if an individual override is set for this user's list capacity.
    $users_max_list_size = $manifest_config["users"][$username]["permissions"]["list_capacity"]["hot"];
} else { // Otherwise, use the default list capacity.
    $users_max_list_size = $manifest_config["permissions"]["max_capacity"]["hot"];
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
            <p>To add this list as a source, add the following URL to the Predator configuration: 
            <?php
            $serve_url = "./serve.php?type=hot";
            if ($username !== $manifest_config["files"]["hotlist"]["active_id"]) {
                $serve_url .= "&user=" . $username;
            }
            if ($selected_list !== "default") {
                $serve_url .= "&list=" . $selected_list;
            }
            if (strlen($hotlist["lists"][$username][$selected_list]["access_key"]) > 0) {
                $serve_url .= "&key=" . $hotlist["lists"][$username][$selected_list]["access_key"];
            }
            echo "<a href='" . $serve_url . "'>Source</a>";
            ?>
            </p>
            <hr>
            <?php


            if ($_POST["submit"] == "Add") {
                if (count_users_list_entries($username, $hotlist) < $users_max_list_size) { // Check to see if this user's list is smaller than its max capacity.
                    $plate_to_add = preg_replace('/\s+/', '', strtoupper($_POST["plate"])); // Get the plate to add to the hot list from the POST data.
                    if ($plate_to_add == preg_replace("/[^A-Z0-9]/", '', $plate_to_add)) { // Check to make sure the provided plate does not have any disallowed characters.
                        $entry_name = preg_replace("/[^a-zA-Z0-9 _\-\']/", '', $_POST["name"]);
                        $entry_description = preg_replace("/[^a-zA-Z0-9 _\-\']/", '', $_POST["description"]);
                        $entry_make = preg_replace("/[^a-zA-Z0-9 \-]/", '', $_POST["make"]);
                        $entry_model = preg_replace("/[^a-zA-Z0-9 \-]/", '', $_POST["model"]);
                        $entry_year = intval($_POST["year"]);
                        $entry_author = preg_replace("/[^a-zA-Z0-9 _\-\']/", '', $_POST["author"]);
                        $entry_source = preg_replace("/[^a-zA-Z0-9 _\-\']/", '', $_POST["source"]);


                        $valid = true;

                        if (strlen($plate_to_add) <= 0) { echo "<p>No license plate was supplied.</p>"; $valid = false;
                        } else if (strlen($plate_to_add) > 12) { echo "<p>The supplied license plate was excessively long.</p>"; $valid = false; }

                        if (strlen($entry_name) > 20) { echo "<p>The supplied name is excessively long.</p>"; $valid = false; }
                        if (strlen($entry_description) > 100) { echo "<p>The supplied description is excessively long.</p>"; $valid = false; }
                        if (strlen($entry_make) > 30) { echo "<p>The supplied vehicle make excessively long.</p>"; $valid = false; }
                        if (strlen($entry_model) > 30) { echo "<p>The supplied vehicle make excessively long.</p>"; $valid = false; }
                        if ($entry_year < -1 or $entry_year > 3000) { echo "<p>The supplied vehicle year is outside of the expected range.</p>"; $valid = false; }
                        if (strlen($entry_author) > 30) { echo "<p>The supplied author excessively long.</p>"; $valid = false; }
                        if (strlen($entry_source) > 200) { echo "<p>The supplied source excessively long.</p>"; $valid = false; }


                        if ($valid == true) {
                            $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["name"] = $entry_name;
                            $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["description"] = $entry_description;
                            $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["vehicle"]["make"] = $entry_make;
                            $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["vehicle"]["model"] = $entry_model;
                            $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["vehicle"]["year"] = $entry_year;
                            $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["author"] = $entry_author;
                            $hotlist["lists"][$username][$selected_list]["contents"][$plate_to_add]["source"] = $entry_source;
                            file_put_contents($manifest_config["files"]["hotlist"]["path"], json_encode($hotlist, JSON_PRETTY_PRINT)); // Save the modified list to disk.
                            echo "<p>The list was updated succesfully.</p>";
                        } else {
                            echo "<p>The list was not updated.</p>";
                        }
                    } else {
                        echo "<p>The specified license plate contains disallowed characters. License plates should only contain letters and numbers.</p>";
                    }
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
                    echo "<p>You have used <b>" . count_users_list_entries($username, $hotlist) . "/" . $users_max_list_size . "</b> allowed list entries. Entries can be removed to make more space.</p>";

                    $autofill_author = $manifest_config["users"][$username]["settings"]["defaults"]["entries"]["author"];
                    $autofill_year = $manifest_config["users"][$username]["settings"]["defaults"]["entries"]["vehicle_year"];

                    if (isset($_GET["plate"])) {
                        $autofill_author = $hotlist["lists"][$username][$selected_list]["contents"][$_GET["plate"]]["author"];
                        $autofill_year = $hotlist["lists"][$username][$selected_list]["contents"][$_GET["plate"]]["vehicle"]["year"];
                    }
                ?>
                <form method="POST" action="?list=<?php echo $selected_list ?>">
                    <label for="plate">Plate:</label> <input type="text" name="plate" id="plate" maxlength="12" placeholder="Plate" value="<?php echo $_GET["plate"]; ?>" required><br>
                    <label for="name">Name:</label> <input type="text" name="name" id="name" maxlength="20" placeholder="Name" value="<?php echo $hotlist["lists"][$username][$selected_list]["contents"][$_GET["plate"]]["name"]; ?>"><br>
                    <label for="description">Description:</label> <input type="text" name="description" id="description" maxlength="100" placeholder="Description" value="<?php echo $hotlist["lists"][$username][$selected_list]["contents"][$_GET["plate"]]["description"]; ?>"><br>
                    <br>
                    <label for="year">Year:</label> <input type="text" name="year" id="year" min="0" max="3000" placeholder="Year" value="<?php echo $autofill_year; ?>"><br>
                    <label for="make">Make:</label> <input type="text" name="make" id="make" maxlength="30" placeholder="Make" value="<?php echo $hotlist["lists"][$username][$selected_list]["contents"][$_GET["plate"]]["vehicle"]["make"]; ?>"><br>
                    <label for="model">Model:</label> <input type="text" name="model" id="model" maxlength="30" placeholder="Model" value="<?php echo $hotlist["lists"][$username][$selected_list]["contents"][$_GET["plate"]]["vehicle"]["model"]; ?>"><br>
                    <br>
                    <label for="author">Author:</label> <input type="text" name="author" id="author" maxlength="30" placeholder="Author" value="<?php echo $autofill_author; ?>"><br>
                    <label for="source">Source:</label> <input type="text" name="source" id="source" maxlength="200" placeholder="Source" value="<?php echo $hotlist["lists"][$username][$selected_list]["contents"][$_GET["plate"]]["source"]; ?>"><br>
                    <input type="submit" name="submit" value="Add" class="button">
                </form>
            </div>
            <hr>
            <div class="basicform">
                <h3>Remove Plate</h3>
                <form method="POST" action="?list=<?php echo $selected_list ?>">
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
                            echo "<a class='button' href='?list=" . $selected_list . "&plate=" . $key . "'>" . $key . "</a><br><br style='margin-top:5px;'>";
                        }
                    } else {
                        echo "<p><i>This list is empty.</i></p>";
                    }
                ?>
            </div>
        </main>
    </body>
</html>
