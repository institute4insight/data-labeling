<?php
    session_start();
    unset($_SESSION['survey_licensing']);
    session_destroy();
    echo "Session cleared.";
?>
