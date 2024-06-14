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
if (!in_array($selected_list, array_keys($ignorelist["lists"][$username]))) {
    echo "<p>The selected list does not exist.</p>";
    exit();
}

if ($manifest_config["users"][$username]["permissions"]["list_capacity"]["ignore"] > -1) { // Check to see if an individual override is set for this user's list capacity.
    $users_max_list_size = $manifest_config["users"][$username]["permissions"]["list_capacity"]["ignore"];
} else { // Otherwise, use the default list capacity.
    $users_max_list_size = $manifest_config["permissions"]["max_capacity"]["ignore"];
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo htmlspecialchars($manifest_config["product_name"]); ?> - Manage Predator Ignore-lists</title>
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
            <h2>Ignore-List Management</h2>
            <p style="color:#cc0000;">Currently editing: <b><?php echo $ignorelist["lists"][$username][$selected_list]["name"] . " (" . $selected_list . ")"; ?></b></p>
            <p>To add this list as a source, simply add the following URL to the Predator configuration: 
            <?php
            $serve_url = "./serve.php?type=ignore";
            if ($username !== $manifest_config["files"]["ignorelist"]["active_id"]) {
                $serve_url .= "&user=" . $username;
            }
            if ($selected_list !== "default") {
                $serve_url .= "&list=" . $selected_list;
            }
            if (strlen($ignorelist["lists"][$username][$selected_list]["access_key"]) > 0) {
                $serve_url .= "&key=" . $ignorelist["lists"][$username][$selected_list]["access_key"];
            }
            echo "<a href='" . $serve_url . "'>Source</a>";
            ?>
            </p>
            <hr>
            <?php
            if ($_POST["submit"] == "Add") {
                if (count_users_list_entries($username, $ignorelist) < $users_max_list_size) { // Check to see if this user's list is smaller than its max capacity.
                    
                    $plate_to_add = preg_replace('/\s+/', '', strtoupper($_POST["plate"]));; // Get the plate to add to the ignore list from the POST data.
                    if ($plate_to_add == preg_replace("/[^A-Z0-9]/", '', $plate_to_add)) {
                        if (strlen($plate_to_add) <= 12) {
                            array_push($ignorelist["lists"][$username][$selected_list]["contents"], $plate_to_add);
                            file_put_contents($manifest_config["files"]["ignorelist"]["path"], json_encode($ignorelist, JSON_PRETTY_PRINT)); // Save the modified list to disk.
                        } else {
                            echo "<p>The specified license plate is excessively long.</p>";
                        }
                    } else {
                        echo "<p>The specified license plate contains disallowed characters. License plates should only contain letters and numbers.</p>";
                    }
                } else {
                    echo "<p>Your list already contains the maximum number of allowed entries. Please either remove existing list entries or upgrade your account.</p>";
                }
            } else if ($_POST["submit"] == "Remove") { // Check to see if a plate to remove was submitted.
                $plate_to_remove = strtoupper($_POST["plate"]); // Get the plate to remove to the ignore list from the POST data.
                if (($key = array_search($plate_to_remove, $ignorelist["lists"][$username][$selected_list]["contents"])) !== false) {
                    unset($ignorelist["lists"][$username][$selected_list]["contents"][$key]);
                    file_put_contents($manifest_config["files"]["ignorelist"]["path"], json_encode($ignorelist, JSON_PRETTY_PRINT)); // Save the modified list to disk.
                } else {
                    echo "<p>The specified plate does not exist in the ignore list.</p>";
                }
            }
            ?>
            <div class="basicform">
                <h3>Add Plate</h3>
                <?php
                    echo "<p>You have used <b>" . count_users_list_entries($username, $ignorelist) . "/" . $users_max_list_size . "</b> allowed list entries. Entries can be removed to make more space.</p>";
                ?>
                <form method="POST">
                    <label for="plate">Plate:</label> <input type="string" name="plate" id="plate" maxlength="12" placeholder="Plate" value="<?php echo $_GET["plate"]; ?>"><br>
                    <input type="submit" name="submit" value="Add" class="button">
                </form>
            </div>
            <hr>
            <div class="basicform">
                <h3>Remove Plate</h3>
                <form method="POST">
                    <label for="plate">Plate:</label> <input type="string" name="plate" id="plate" maxlength="12" placeholder="Plate" value="<?php echo $_GET["plate"]; ?>"><br>
                    <input type="submit" name="submit" value="Remove" class="button">
                </form>
            </div>
            <hr>
            <div class="basicform">
                <h3>Select Plate</h3>
                <br>
                <?php
                    if (sizeof($ignorelist["lists"][$username][$selected_list]["contents"]) > 0){
                        foreach ($ignorelist["lists"][$username][$selected_list]["contents"] as $plate) {
                            echo "<a class='button' href='?plate=" . $plate. "'>" . $plate. "</a><br><br style='margin-top:5px;'>";
                        }
                    } else {
                        echo "<p><i>This list is empty.</i></p>";
                    }
                ?>
            </div>
        </main>
    </body>
</html>
