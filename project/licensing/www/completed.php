<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();
    unset($_SESSION['survey_licensing']);
    session_destroy();
    // require 'header.inc.php';

?>
<!DOCTYPE HTML>
<HTML lang="eng">
    <HEAD>
        <TITLE>Thank you</TITLE>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
            integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="site.css">
    </HEAD>
    <BODY>
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>Thank you...</h1>
                    <p>...for completing this labeling task. </p>
                </div>
            </div>
        </div>
    <BODY>
</HTML>
