<?php
include "./config.php";

$force_login_redirect = true;
include strval($manifest_config["auth"]["provider"]);

include "./authentication.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo htmlspecialchars($manifest_config["product_name"]); ?> - Manage Predator Ignore-lists</title>
        <script async defer data-domain="v0lttech.com" src="/js/plausible.js"></script>

        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
        <link rel="stylesheet" href="./styles/main.css">
    </head>
    <body class="truebody">
        <main class="mainbody centeredsection">
            <div class="navbar">
                <a class="button" href="index.php">Back</a>
            </div>
            <h1><?php echo htmlspecialchars($manifest_config["product_name"]); ?></h1>
            <h2>Ignore-List Management</h2>
            <p>This tool allows system users to manage license plate ignore-lists for Predator instances. It should be noted that this tool only affects Predator instances that have this source in their ignore list sources.</p>
            <p>To add this list as a source, simply add the following URL to the Predator configuration: 
            <?php
            $serve_url = "./serve.php?type=ignore";
            if ($username !== $manifest_config["files"]["hotlist"]["active_id"]) {
                $serve_url .= "&id=" . $username;
            }
            if (strlen($manifest_config["users"][$username]["settings"]["access_key"]) > 0) {
                $serve_url .= "&key=" . $manifest_config["users"][$username]["settings"]["access_key"];
            }
            echo "<a href='" . $serve_url . "'>Source</a>";
            ?>
            </p>
            <?php
            if (!file_exists($manifest_config["files"]["ignorelist"]["path"])) { // Check to see if the file doesn't already exists.
                file_put_contents($manifest_config["files"]["ignorelist"]["path"], "{}"); // Create a blank list file.
            }
            if (!is_writable($manifest_config["files"]["ignorelist"]["path"])) { // Check to see if the file isn't writable to PHP.
                echo "<p>Error: The file is not writable to PHP. Ignore lists can't be edited.</p>";
                exit();
            }

            $ignore_list = json_decode(file_get_contents($manifest_config["files"]["ignorelist"]["path"]), true); // Load the ignore list contents from the file.
            $ignore_list["meta"]["type"] = "ignorelist";

            if ($manifest_config["users"][$username]["permissions"]["list_capacity_ignore"] > -1) { // Check to see if an individual override is set for this user's list capacity.
                $users_max_list_size = $manifest_config["users"][$username]["permissions"]["list_capacity_ignore"];
            } else { // Otherwise, use the default list capacity.
                $users_max_list_size = $manifest_config["permissions"]["max_size"]["ignore"];
            }

            if (isset($ignore_list["lists"][$username]["default"]["contents"]) == false) { // Check to see if the ignore list contents need to be initialized.
                $ignore_list["lists"][$username]["default"]["contents"] = array();
            }

            if ($_POST["submit"] == "Add") {
                if (sizeof($ignore_list["lists"][$username]["default"]["contents"]) < $users_max_list_size) {
                    array_push($ignore_list["lists"][$username]["default"]["contents"], strtoupper($_POST["plate"]));
                    file_put_contents($manifest_config["files"]["ignorelist"]["path"], json_encode($ignore_list, JSON_PRETTY_PRINT)); // Save the modified list to disk.
                } else {
                    echo "<p>Your list already contains the maximum number of allowed entries. Please either remove existing list entries or upgrade your account.</p>";
                }
            } else if ($_POST["submit"] == "Remove") { // Check to see if a plate to remove was submitted.
                if (($key = array_search($_POST["plate"], $ignore_list["lists"][$username]["default"]["contents"])) !== false) {
                    unset($ignore_list["lists"][$username]["default"]["contents"][$key]);
                } else {
                    echo "<p>The specified plate does not exist in the ignore list.</p>";
                }
                file_put_contents($manifest_config["files"]["ignorelist"]["path"], json_encode($ignore_list, JSON_PRETTY_PRINT)); // Save the modified list to disk.
            }
            ?>
            <hr>
            <div class="basicform">
                <h3>Add Plate</h3>
                <?php
                    echo "<p>You have used <b>" . sizeof($ignore_list["lists"][$username]["default"]["contents"]) . "/" . $users_max_list_size . "</b> allowed list entries. Entries can be removed to make more space.</p>";
                ?>
                <form method="POST">
                    <label for="plate">Plate:</label> <input type="string" name="plate" id="plate" placeholder="Plate"><br>
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
                    if (sizeof($ignore_list["lists"][$username]["default"]["contents"]) > 0){
                        foreach ($ignore_list["lists"][$username]["default"]["contents"] as $plate) {
                            echo "<a class='button' href='?plate=" . $plate . "'>" . $plate . "</a><br><br style='margin-top:5px;'>";
                        }
                    } else {
                        echo "<p><i>This list is empty.</i></p>";
                    }
                ?>
            </div>
        </main>
    </body>
</html>
