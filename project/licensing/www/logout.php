<?php
    //   _     ___   ____    ___  _   _ _____ 
    //  | |   / _ \ / ___|  / _ \| | | |_   _|
    //  | |  | | | | |  _  | | | | | | | | |  
    //  | |__| |_| | |_| | | |_| | |_| | | |  
    //  |_____\___/ \____|  \___/ \___/  |_|  
                                           
    session_start();
    unset($_SESSION['survey_licensing']);
    session_destroy();
    echo "Session logged out. To login again click <a href='index.php'>here</a>.";
?>
