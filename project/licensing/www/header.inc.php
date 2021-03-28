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
        $dbconn = pg_connect("host=" . $creds['host'] . " dbname=datalabeling"
                          . " user=" . $creds['user'] . " password=" . $creds['password'])
            or die('Could not connect: ' . pg_last_error());
        return $dbconn;
    }

    function db_query($query, $dbconn) {
        return pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());
    }

    function db_free_result($result) {
        pg_free_result($result);
    }

    function db_close($dbconn) {
        pg_close($dbconn);
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
    $n_types = count($license_types);
?>
<!-- Nothing to see -->
