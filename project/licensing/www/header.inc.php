<?php
    session_start();
    function db_connect() {
        /* load JSON file with credential from server's local filesystem
        {
            "host": "HOSTNAME",
            "dbname": "DATABASE NAME",
            "user": "User Name for this application",
            "password": "Databaswe Password"
        }
        */
        $creds = json_decode(file_get_contents(".dbcredentials.json"), true);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $dbconn = new mysqli($creds['host'], $creds['user'], $creds['password'], "datalabeling");
            or die('Could not connect: ' . $dbconn->connect_error);
        return $dbconn;
    }

    function db_query($query, $dbconn) {
        return dbconn->query($query) or die('Query failed: ' . $dbconn->error);
    }

    function db_free_result($result) {
        $result->free();
    }

    function db_close($dbconn) {
        $dbconn->close();
    }

    $license_role = [
        "licensor" => "Licensor (party grants license)",
        "licensee" => "Licensee (party receives license)",
        "both" => "Both (party grants and receives licenses)",
        "n/a" => "Neither (party is not involved in license agreement)"
    ];

    $license_type = [
        "cross" => "Cross",
        "joint" => "Joint",
        "regular" => "Regular",
        "exclusive" => "Exclusive",
        "renew" => "Renew",
        "n/a" =>  "Not a license"
    ];
    
    $n_roles = count($license_role);
    $n_types = count($license_type);
?>
<!-- Nothing to see -->
