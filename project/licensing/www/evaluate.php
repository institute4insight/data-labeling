<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    //session_start();

    require 'header.inc.php';

    // $_SESSION['survey_licensing']['selection'][$adj] stores selected values
    function get_selection($adj) {
        if (defined($_SESSION['survey_licensing'], $_SESSION['survey_licensing']['selection'],
                     $_SESSION['survey_licensing']['selection'][$adj])) {
            return $_SESSION['survey_licensing']['selection'][$adj];
        } else {
            return '';
        }
    }

    if (!isset($_SESSION['survey_licensing'])) {
        $_SESSION['survey_licensing'] = [];
    }

    // without user_id goto login page
    if (!isset($_SESSION['survey_licensing']['user_id'])) {
        header("Location: index.php");
        die();
    }

    $uid = $_SESSION['survey_licensing']['user_id'];

    $save_message = "";


    function process_response() {
        if(!isset($_REQUEST)) {
            return -1;
        }
        else {
            // process results here ...
                // //check if this is a response, and add to database
                // if (isset($_REQUEST, $_REQUEST['a'], $_REQUEST['r'])) {
                //     $conn = db_connect();
                //     $response_a = pg_escape_string(urldecode($_REQUEST['a']));
                //     $response_r = pg_escape_string(urldecode($_REQUEST['r']));
                //     $q = "
                //         INSERT INTO licensing_responses (assignment_id, label, ts)
                //         VALUES ('$response_a', '$response_r', NOW())
                //         ON CONFLICT (assignment_id) DO NOTHING
                //     ";
                //     $save_message = "<pre>$q</pre>";
                //     $res = pg_query($conn, $q);
                //     if (!$res) {
                //         echo "An error occurred.\n";
                //         exit;
                //     }
                //     db_free_result($res);
                //     db_close($conn);

                // }
            return 0;
        }
    }

    $res = process_response();

    function get_next_assignment($uid) {
        return array($assignment_id, $doc_id, $n_completed, $n_total);
    }

    //list($assignment_id, $doc_id, $n_completed, $n_total) = get_next_assignment($uid)

    // development
    list($assignment_id, $doc_id, $n_completed, $n_total) = array("nobody_nothing", "PRN0000020040420e04j00001", 0, 100);

    function get_sample_doc($doc_id) {
        // $assignment_id = "nobody_nothing";
        // $doc_id = "PRN0000020040420e04j00001";
        $content = htmlentities(file_get_contents("samples/content.txt"));
        $license_sents = htmlentities(file_get_contents("samples/license_sents.txt"));
        $key_sents = htmlentities(file_get_contents("samples/key_sents.txt"));
        $companies = [
            "ALLIANCE" => "Alliance Pharmaceutical Corp. ",
            "IL_YANG" => "IL YANG Pharmaceutical Co.",
            "OXYGENT" => "Oxygent(TM)"
        ];

        return array($content, $license_sents, $key_sents, $companies);
    }

    function get_doc($doc_id) {
        // get next assignment record
        $conn = db_connect();
        // $q = "
        //     WITH all_assignments AS (
        //         SELECT * FROM licensing_assignments
        //     ), tab AS (
        //         SELECT a.*, r.label, r.ts
        //         FROM all_assignments a
        //         LEFT JOIN licensing_responses r
        //         ON r.assignment_id=a.assignment_id
        //         WHERE userid='$uid'
        //     )
        //     SELECT *,
        //         (SELECT COUNT(1) FROM tab WHERE NOT label IS NULL) AS n_completed,
        //         (SELECT COUNT(1) FROM tab) AS n_total 
        //     FROM tab
        //     WHERE label IS NULL
        //     ORDER BY pos
        //     LIMIT 1
        // ";
        // $res = pg_query($conn, $q);
        // if (!$res) {
        //     echo "An error occurred.\n";
        //     exit;
        // }

        // // if nothing left to do go to completed page
        // if (pg_num_rows($res)==0) {
        //     // $_SESSION['survey_licensing']['stage'] = 'data';
        //     // header("Location: data.php");
        //     $_SESSION['survey_licensing']['stage'] = 'completed';
        //     header("Location: completed.php");
        //     die();
        // }

        // $row = pg_fetch_assoc($res);
        // // returned row: {
        // //     "assignment_id" => string(74) "4|Dis_similar_images/7331_3885_29.jpg|Dis_similar_images/3895_14035_72.jpg",
        // //     "pos" => string(1) "1",
        // //     "title" => string(5) "Fonts",
        // //     "question" => string(82) "Do you see text in the two images that use the same – or very similar – fonts?",
        // //     "idx"=> string(1) "4"
        // //     "a_img"]=> string(35) "Dis_similar_images/7331_3885_29.jpg",
        // //     "a_idx"]=> string(4) "2953",
        // //     "b_img"]=> string(36) "Dis_similar_images/3895_14035_72.jpg",
        // //     "b_idx"]=> string(5) "10103"
        // // }

        // $assignment_id = $row["assignment_id"];
        // $qn = (int)$row["idx"];
        // $picurl_left = "image_data/" . str_replace('"', '', $row["a_img"]);
        // $picurl_right = "image_data/" . str_replace('"', '', $row["b_img"]);
        // $n_completed = (int)$row["n_completed"];
        // $n_total = (int)$row["n_total"];

        // db_free_result($res);
        // db_close($conn);

        return array($content, $license_sents, $key_sents, $companies);
    }
  

    list($content, $license_sents, $key_sents, $companies) = get_sample_doc($doc_id);
    // list($content, $license_sents, $key_sents, $companies) = get_doc($doc_id)
?>

<HTML lang="eng">
    <HEAD>
        <TITLE>Survey</TITLE>
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
                    <p style="text-align: right;"><i>
                        <b>User:</b> <?= $uid ?>&nbsp;&nbsp;&nbsp;&nbsp;
                        <b>Progress:</b> <?= $n_completed ?>/<?= $n_total ?>&nbsp;(<?= round(100.0 * $n_completed / $n_total)?>%)
                        </i>
                        &nbsp;&nbsp;&nbsp;&nbsp;<a href="logout.php">(Log out)</a>
                    </p>
                </div>
            </div><!-- .row -->
            <div class="row"><!-- horizonal rule -->
                <div class="col-md-12" style="height:3px; border: 1px gray solid; margin: 5px 0px"></div>
            </div><!-- .row -->
            <div class="col-md-12"> 
                <h1>Labeling Licencing Announcements</h1>
                <p class="instructions">
                Review the text below to determine the type of licensing and the role of each of the parties involved.
                </p>
            </div>
            <div class="row" style="margin-top: 10px">
                <div class="col-md-10 offset-md-1 text-view" >
                    <?= $content ?>
                </div>
            </div><!-- .row -->
            <!-- form -->
            <div class="row" style="margin-top: 10px">
                <div class="col-md-12" >
                    <h4>Type of License</h4>
                    <p class="instructions">
                    Select the type of licences in this announcement.
                    </p>
                    <!-- <div class="form-check form-check-inline"> -->
                    <div class="form-row">
                    <?php
                    foreach ($license_type as $k => $v) {
                        $nam = "type_" . $k;
                        echo "<div class=\"col-md-2\">";
                        echo "<input class=\"form-check-input\" type=\"radio\" name=\"lic_type\" id=\"$nam\" value=\"$k\">";
                        echo "<label class=\"form-check-label\" for=\"$nam\">$v</label>";
                        echo "</div>";
                    }
                    ?>
                    </div><!-- form-row -->

                    <h4>Role of the  Involved Parties</h4>
                    <p class='instructions'>Please select the role of each of the companies that are listed below.</p>
                    <?php
                        foreach ($companies as $comp_id => $comp_name) {
                            $rol = "role_" . $comp_id;
                            echo "<div class=\"form-row\">";
                            echo "<div class=\"col-md-4\">";
                            echo "<label class=\"\" for=\"$rol\">$comp_name</label>";
                            echo "</div>";
                            echo "<div class=\"col-md-6\">";
                            echo "<select class=\"custom-select\" id=\"$rol\">";
                            echo "    <option selected>Choose...</option>";
                            foreach ($license_role as $k => $v) {
                                echo "<option value=\"$k\">$v</option>";
                            }
                            echo "</select>";
                            echo "</div></div><!-- form-row -->\n";

                        }   
                    ?>
                    
                </div>
            </div><!-- .row -->

        </div>

        <!-- <?= $save_message ?> -->

        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script> -->
        <script>
        
        $(document).ready(function(){
            console.log("document ready")
            console.log(`current assignment_id: "<?= $assignment_id ?>"`)
            $("button").click(function(){
                console.log("response...")
                let assignment_id = "<?= $assignment_id ?>";
                console.log(""+assignment_id)
                var url = `?a=${encodeURI(assignment_id)}&r=${encodeURI($(this).text().toLowerCase())}`
                console.log(`next: ${url}`)
                document.location =  url;
            })
        })
        </script>
    </BODY>
</HTML>

<?php
    //}
?>
