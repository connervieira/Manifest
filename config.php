<?php
$manifest_config_database_name = "./config.json";


if (is_writable(".") == false) {
    echo "<p class=\"error\">The directory '" . realpath(".") . "' is not writable to PHP.</p>";
    exit();
}

// Load and initialize the database.
if (file_exists($manifest_config_database_name) == false) { // Check to see if the database file doesn't exist.
    $manifest_configuration_database_file = fopen($manifest_config_database_name, "w") or die("Unable to create configuration database file."); // Create the file.

    $manifest_config["auth"]["admins"] = ["cvieira"]; // TODO: Replace
    $manifest_config["auth"]["provider"] = "../dropauth/authentication.php";
    $manifest_config["auth"]["access"]["mode"] = "whitelist";
    $manifest_config["auth"]["access"]["whitelist"] = [];
    $manifest_config["auth"]["access"]["blacklist"] = [];
    $manifest_config["files"]["ignore"]["path"] = "./listignore.json";
    $manifest_config["files"]["ignore"]["active_id"] = "publicignorelist";
    $manifest_config["files"]["hotlist"]["path"] = "./listhot.json";
    $manifest_config["files"]["hotlist"]["active_id"] = "emergencyhotlist";
    $manifest_config["product_name"] = "Manifest";

    fwrite($manifest_configuration_database_file, json_encode($manifest_config, (JSON_UNESCAPED_SLASHES))); // Set the contents of the database file to the placeholder configuration.
    fclose($manifest_configuration_database_file); // Close the database file.
}

if (file_exists($manifest_config_database_name) == true) { // Check to see if the item database file exists. The database should have been created in the previous step if it didn't already exists.
    $manifest_config = json_decode(file_get_contents($manifest_config_database_name), true); // Load the database from the disk.
} else {
    echo "<p class=\"error\">The configuration database failed to load</p>"; // Inform the user that the database failed to load.
    exit(); // Terminate the script.
}



?>
