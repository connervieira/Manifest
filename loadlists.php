<?php
// Initialize the hot-lists.
if (!file_exists($manifest_config["files"]["hotlist"]["path"])) { // Check to see if the file doesn't already exist.
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

if (isset($hotlist["lists"][$username]["default"]["contents"]) == false) { // Check to see if the hotlist contents need to be initialized.
    $hotlist["lists"][$username]["default"]["name"] = "Default List";
    $hotlist["lists"][$username]["default"]["description"] = "This is " . $username . "'s default list.";
    $hotlist["lists"][$username]["default"]["access_key"] = "";
    $hotlist["lists"][$username]["default"]["contents"] = array();
}



// Initialize the ignore-lists.
if (!file_exists($manifest_config["files"]["ignorelist"]["path"])) { // Check to see if the file doesn't already exist.
    file_put_contents($manifest_config["files"]["ignorelist"]["path"], "{}"); // Create a blank list file.
}
if (!is_writable($manifest_config["files"]["ignorelist"]["path"])) { // Check to see if the file isn't writable to PHP.
    echo "<p>Error: The ignore-list file is not writable to PHP. Ignore lists can't be edited.</p>";
    exit();
}

$ignorelist = json_decode(file_get_contents($manifest_config["files"]["ignorelist"]["path"]), true); // Load the ignore list contents from the file.
$ignorelist["meta"]["type"] = "ignorelist";

if ($manifest_config["users"][$username]["permissions"]["list_capacity_ignore"] > -1) { // Check to see if an individual override is set for this user's list capacity.
    $users_max_list_size = $manifest_config["users"][$username]["permissions"]["list_capacity_ignore"];
} else { // Otherwise, use the default list capacity.
    $users_max_list_size = $manifest_config["permissions"]["max_size"]["ignore"];
}

if (isset($ignorelist["lists"][$username]["default"]["contents"]) == false) { // Check to see if the ignore list contents need to be initialized.
    $ignorelist["lists"][$username]["default"]["name"] = "Default List";
    $ignorelist["lists"][$username]["default"]["description"] = "This is " . $username . "'s default list.";
    $ignorelist["lists"][$username]["default"]["access_key"] = "";
    $ignorelist["lists"][$username]["default"]["contents"] = array();
}
?>
