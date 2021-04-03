<?php
    session_start();
    function db_connect() {
        /* load JSON file with credential from server's local filesystem
        {
            "host": "HOSTNAME",
            "dbname": "DATABASE NAME",
            "user": "User Name for this application",
            "password": "Database Password"
        }
        */
        $creds = json_decode(file_get_contents("/var/www/html/surveys/licensing/.dbcredentials.json"), true);
        $dbconn = new mysqli($creds['host'], $creds['user'], $creds['password'], "datalabeling")
            or die('Could not connect: ' . $dbconn->connect_error);
        return $dbconn;
    }

    function db_query($query, $dbconn) {
        return $dbconn->query($query) or die('Query failed: ' . $dbconn->error);
    }

    function db_free_result($result) {
        $result->free();
    }

    function db_close($dbconn) {
        $dbconn->close();
    }

    $license_role = [
        "licensor" => "Licensor (this company grants license)",
        "licensee" => "Licensee (this company receives license)",
        "both" => "Both (this company grants and receives licenses)",
        "neither" => "Neither (this company is not involved in license agreement)",
        "not_company" => "N/A (this is not a company)"
    ];

    $license_type = [
        "cross" => "Cross",
        // "joint" => "Joint",
        "regular" => "Regular",
        "exclusive" => "Exclusive",
        "renew" => "Renew",
        "n/a" =>  "Not a license"
    ];
    
    $n_roles = count($license_role);
    $n_types = count($license_type);
?>
<!-- Nothing to see -->
