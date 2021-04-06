<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    //session_start();

    require 'header.inc.php';

    // $_SESSION['survey_licensing']['selection'][$adj] stores selected values
    // function get_selection($adj) {
    //     if (defined($_SESSION['survey_licensing'], $_SESSION['survey_licensing']['selection'],
    //                  $_SESSION['survey_licensing']['selection'][$adj])) {
    //         return $_SESSION['survey_licensing']['selection'][$adj];
    //     } else {
    //         return '';
    //     }
    // }


    $progress_cols = ['user_id', 'n_completed', 'n_total', 'first_submission', 'last_submission'];

    function html_progress($progress) {
        echo "<table class=\"table\">";
        echo "<tr>";
        foreach($progress[0] as $k => $v) {
            echo "<th>$k</th>";
        }
        echo "</tr>";
        foreach($progress as $pr) {
            echo "<tr>";
            foreach($pr as $k => $v) {
                echo "<td>$v</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }

    function get_progress() {
        // returns $content, $license_sents, $key_sents, $companies
        $progress = [];

        // get next assignment record
        $conn = db_connect();
        
        $q = "
            WITH compstats AS (
                SELECT a.user_id
                     , SUM(CASE WHEN r.submit_time IS NOT NULL THEN 1 ELSE 0 END) AS n_completed
                     , COUNT(a.assignment_ID) AS n_total
                     , MIN(r.submit_time) AS first_submission
                     , MAX(r.submit_time) AS last_submission
                FROM licensing_assignments a
                LEFT JOIN licensing_responses r
                on a.assignment_id=r.assignment_id
                GROUP BY a.user_id
            )
            SELECT user_id, n_completed, n_total, first_submission, last_submission
            FROM compstats
            ORDER BY user_id
        ";
        if ($res = $conn->query($q)) {
            while ($row = $res->fetch_assoc()) {
                $progress[] = $row;
            }
            $res->close();
        }
        $conn->close();
        return $progress;
    }
  
    

    // //list($content, $license_sents, $key_sents, $companies) = get_sample_doc($doc_id);
    // list($content, $license_sents, $key_sents, $companies) = get_doc($doc_id)
?>

<HTML lang="eng">
    <HEAD>
        <TITLE>Progress</TITLE>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
            integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="site.css">
    </HEAD>
    <BODY>
        <div class="container">
            <div class="row">
                <div class="col-md-12">       
                    <h1>Progress</h1>
                    <!-- <?php passthru("/var/www/html/surveys/licensing/bin/summary_text.py"); ?> -->
                    <div id="summary-text"></div>
                    <?php
                        $progress = get_progress();
                        html_progress($progress)
                    ?>
                </div>
            </div><!-- .row -->
            
        </div>

        <!-- <?= $response_message ?> -->

        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script> -->
        <script>
        
        $(document).ready(function(){
            $("#summary-text").load("http://arc.insight.gsu.edu/cgi-bin/summary_text.cgi")
            console.log("document ready")
        })
        </script>
    </BODY>
</HTML>

<?php
    //}
?>
<!-- values from radio buttons https://stackoverflow.com/questions/596351/how-can-i-know-which-radio-button-is-selected-via-jquery -->

