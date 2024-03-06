<?php
$force_login_redirect = true;
include "../../authentication.php";

if ($username != "cvieira") {
    echo "<p>This tool is for use only by V0LT Administrators!</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Predator - Manage Hot List</title>
        <script async defer data-domain="v0lttech.com" src="/js/plausible.js"></script>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="/files/fonts/lato/latofonts.css">
        <link rel="stylesheet" href="/styles/main.css">
        <?php include "../../loadtheme.php"; ?>
    </head>
    <body class="truebody">
        <main class="mainbody centeredsection">
            <h1>Predator</h1>
            <h2>Hot-List Management</h2>
            <p>This tool allows system administrators to manage license plate hot lists for Predator instances. It should be noted that this tool only affects Predator instances that have this server in their remote alert list sources.</p>
            <?php
            $file = "../plates/emergency.json"; // This defines where the hot list is saved.

            if (!is_writable("./")) { // Check to see if the current directory isn't writable to PHP.
                echo "<p>Error: The directory is not writable to PHP. Hot lists can't be edited.</p>";
                exit();
            }
            if (!file_exists($file)) { // Check to see if the file doesn't already exists.
                file_put_contents($file, "{}"); // Create a blank list file.
            }
            if (!is_writable($file)) { // Check to see if the file isn't writable to PHP.
                echo "<p>Error: The file is not writable to PHP. Hot lists can't be edited.</p>";
                exit();
            }

            $hot_list = json_decode(file_get_contents($file), true); // Load the hot list contents from the file.

            $plate_to_add = strtoupper($_POST["plate"]); // Get the plate to add to the hot list from the POST data.
            $plate_to_remove = strtoupper($_GET["remove"]); // Get the plate to remove to the hot list from the URL.

            if ($plate_to_add != "" and $plate_to_add != null) { // Check to see if a plate to add was submitted.
                $hot_list[$plate_to_add]["name"] = $_POST["name"];
                $hot_list[$plate_to_add]["description"] = $_POST["description"];
                $hot_list[$plate_to_add]["author"] = $_POST["author"];
                $hot_list[$plate_to_add]["source"] = $_POST["source"];
                file_put_contents($file, json_encode($hot_list, JSON_PRETTY_PRINT)); // Save the modified list to disk.
            } else if ($plate_to_remove != "" and $plate_to_remove != null) { // Check to see if a plate to remove was submitted.
                unset($hot_list[$plate_to_remove]); // Remove the plate from the dictionary
                file_put_contents($file, json_encode($hot_list, JSON_PRETTY_PRINT)); // Save the modified list to disk.
                header("Location: ."); // Redirect the user after removing the plate.
            }
            ?>
            <hr>
            <div class="basicform">
                <h3>Add Plate</h3>
                <form method="POST">
                    <label for="plate">Plate:</label> <input type="string" name="plate" id="plate" placeholder="Plate"><br>
                    <label for="name">Name:</label> <input type="string" name="name" id="name" placeholder="Name"><br>
                    <label for="description">Description:</label> <input type="string" name="description" id="description" placeholder="Description"><br>
                    <label for="author">Author:</label> <input type="string" name="author" id="author" placeholder="Author" value="V0LT"><br>
                    <label for="source">Source:</label> <input type="string" name="source" id="source" placeholder="Source"><br>
                    <input type="submit" value="Add" class="formbutton">
                </form>
            </div>
            <hr>
            <div class="basicform">
                <h3>Remove Plate</h3>
                <?php
                    foreach ($hot_list as $key => $plate) {
                        echo "<a href='?remove=" . $key . "'>" . $key . "</a><br><br>";
                    }
                ?>
            </div>
        </main>
    </body>
</html>
