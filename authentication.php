<?php
if ($force_login_redirect == true) { // Only check against the blacklist/whitelist if authentication is required.
    if (!in_array($username, $manifest_config["auth"]["admins"])) { // Only check against the blacklist/whitelist if this user is not an administrator.
        if ($manifest_config["auth"]["access"]["mode"] == "whitelist") {
            if (!in_array($username, $manifest_config["auth"]["access"]["whitelist"])) { // Check to see if this user is not in the whitelist.
                echo "<p>You are not authorized to access this service.</p>";
                exit();
            }
        } else if ($manifest_config["auth"]["access"]["mode"] == "blacklist") {
            if (in_array($username, $manifest_config["auth"]["access"]["blacklist"])) { // Check to see if this user is in the blacklist.
                echo "<p>You are not authorized to access this service.</p>";
                exit();
            }
        }
    }
}
?>
