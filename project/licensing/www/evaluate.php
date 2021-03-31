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

    if (isset($_SESSION, $_SESSION['survey_licensing'], $_SESSION['survey_licensing']['user_id'])) {
        $uid = $_SESSION['survey_licensing']['user_id'];
    } else {
        $uid = "nobody";
    }
    

    $save_message = "";


    // assignment_id: bwr0000020010710dx4q025ce^nmarin1
    // user_id: nmarin1
    // n_total: 1438
    // n_completed: 0
    // lic_role: Choose...
    // lic_role: Choose...
    // lic_role: Choose...
    // lic_role: Choose...
    // lic_role: Choose...
    // submit: skip

    //      Field           Type            Null    Key	Default	Extra
    // 0	submit_time     varchar(64)     YES     None	
    // 1	user_id         varchar(64)     YES     None	
    // 2	assignment_id   varchar(129)    YES     None	
    // 3	license_type    varchar(256)    YES     None	
    // 4	license_roles   text            YES     None	
    // 5	valid_response  tinyint(1)      YES     None	
    

    function process_response() {
        if (isset($_REQUEST, $_REQUEST['user_id'], $_REQUEST['assignment_id'], $_REQUEST['submit'])) {
            $conn = db_connect();
            $response_assignment_id = pg_escape_string(urldecode($_REQUEST['assignment_id']));
            $response_user_id = pg_escape_string(urldecode($_REQUEST['user_id']));
            switch ($_REQUEST['submit']) {
                case 'skip':
                    $q = "
                        INSERT INTO
                            licensing_responses (submit_time, user_id, assignment_id, license_type, license_roles, valid_response)
                        VALUES (
                            NOW(),
                            '$response_user_id',
                            '$response_assignment_id',
                            NULL,
                            NULL,
                            0
                        )
                        ON CONFLICT (assignment_id) DO NOTHING
                    ";
                    break;
                default:
                    $response_license_type = pg_escape_string(urldecode($_REQUEST['lic_type']));
                    ## $response_license_role = pg_escape_string(urldecode($_REQUEST['lic_role']));
                    $response_license_role = implode($_REQUEST['lic_role'], ',');
                    // $response_license_role = '';
                    // $sep = '';
                    // foreach($_REQUEST['lic_role'] as $v) {
                    //     $response_license_role .= $sep . $v;
                    //     $sep = ',';
                    // }
                    $q = "
                    INSERT INTO
                        licensing_responses (submit_time, user_id, assignment_id, license_type, license_roles, valid_response)
                    VALUES (
                        NOW(),
                        '$response_user_id',
                        '$response_assignment_id',
                        '$response_license_type',
                        '$response_license_role',
                        1
                    )
                    ON CONFLICT (assignment_id) DO NOTHING
                    ";
                    break;
            }
            $save_message = "<pre>$q</pre>";
            echo $save_message;
            $res = $conn->query($q);
            if (!$res) {
                echo $conn->error;
            //     exit;
            }
            // $res->close();
            $conn->close();
        } else {
            $save_message = "<div>Nothing to do...</div>";
        }
    }

    $res = process_response();

    function get_next_assignment($uid) {

        $assignment_id = '';
        $doc_id = '';
        $n_total = 0;
        $n_completed = 0;
        $conn = db_connect();
        // query number of assignments and completed assignments
        $q1 = "
        WITH compstats AS (
                SELECT a.user_id
                     , SUM(CASE WHEN r.submit_time IS NOT NULL THEN 1 ELSE 0 END) AS n_completed
                     , COUNT(a.assignment_ID) AS n_total
                FROM licensing_assignments a
                LEFT JOIN licensing_responses r
                on a.assignment_id=r.assignment_id
                GROUP BY a.user_id
            )
            SELECT user_id, n_completed, n_total
            FROM compstats
            WHERE user_id='$uid'
        ";
        if ($res1 = $conn->query($q1)) {
            $row1 = $res1->fetch_assoc();
            $res1->close();

            $n_completed = $row1['n_completed'];
            $n_total = $row1['n_total'];
            if ($n_completed<$n_total) {
                $q = "
                SELECT a.*
                FROM licensing_assignments a
                LEFT JOIN licensing_responses r
                on a.assignment_id=r.assignment_id
                WHERE r.submit_time IS NULL AND a.user_id='$uid'
                ORDER BY a.sort_order
                LIMIT 1
                ";
                if ($res = $conn->query($q)) {
                    $row = $res->fetch_assoc();
                    $res->close();
                    $assignment_id = $row['assignment_id'];
                    $doc_id = $row['doc_id'];
                }
            }
        }
        $conn->close();
        return array($assignment_id, $doc_id, $n_completed, $n_total);
    }


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
        // returns $content, $license_sents, $key_sents, $companies
        $content = "";
        $license_sents = "";
        $key_sents = "";
        $companies = [];

        // get next assignment record
        $conn = db_connect();
        
        $q = "
        SELECT doc_id, content, license_sents, key_sents
        FROM licensing_documents
        WHERE doc_id='$doc_id'
        ";
        if ($res = $conn->query($q)) {
            $row = $res->fetch_assoc();
            $res->close();
            $content = $row['content'];
            $license_sents = $row['license_sents'];
            $key_sents = $row['key_sents'];

            $q2 = "
            SELECT doc_id, company_name, company_id
            FROM licensing_doc_company
            WHERE doc_id='$doc_id'
            ";
            if ($res2 = $conn->query($q2)) {
                while ($row2 = $res2->fetch_assoc()) {
                    $companies[$row2['company_id']] = $row2['company_name'];
                }
                $res2->close();
            }
            
        }

        $conn->close();

        return array($content, $license_sents, $key_sents, $companies);
    }
  
    if (isset($_REQUEST, $_REQUEST['doc'])) {
        $doc_id = $_REQUEST['doc'];
        $assignment_id = "$doc_id^$uid";
        $n_total = 0;
        $n_completed = 0;
        list($content, $license_sents, $key_sents, $companies) = get_doc($doc_id);
    } else {
        list($assignment_id, $doc_id, $n_completed, $n_total) = get_next_assignment($uid);
        list($content, $license_sents, $key_sents, $companies) = get_doc($doc_id);
    }


    // //list($content, $license_sents, $key_sents, $companies) = get_sample_doc($doc_id);
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
                        <b>Progress:</b> <?= $n_completed ?>/<?= $n_total ?>
                        <?php
                            if ($n_total>0) { echo "&nbsp;(". round(100.0 * $n_completed / $n_total) . "%)";}
                        ?>
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
                Review the text in the box below to determine the type of licensing and the role of each of the companies involved.
                You may scroll within the text box to read the entire document.
                </p>
            </div>
            <div class="row" style="margin-top: 2px">
                <div class="col-md-2 instructions-left">
                    Text that includes “license/licensing”
                </div>
                <!-- <div class="col-md-10 offset-md-1 text-view" > -->
                <div class="col-md-10 text-view" >
                    <?= $license_sents ?>
                </div>
            </div><!-- .row -->
            <div class="row" style="margin-top: 10px">
                <div class="col-md-2 instructions-left">
                    Text that helps to identify the role of each company
                </div>
                <!-- <div class="col-md-10 offset-md-1 text-view" > -->
                <div class="col-md-10 text-view" >
                    <?= $key_sents ?>
                </div>
            </div><!-- .row -->
            <!-- form -->
            <div class="row" style="margin-top: 10px">
                <form class="col-md-12" method="POST"> 
                    <input type="hidden" name="assignment_id" value="<?= $assignment_id?>" />
                    <input type="hidden" name="user_id" value="<?= $uid?>" />
                    <input type="hidden" name="n_total" value="<?= $n_total?>" />
                    <input type="hidden" name="n_completed" value="<?= $n_completed?>" />
                    <div class="col-md-12" >
                        <!-- <h4>Type of License</h4> -->
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

                        <!-- <div class="form-row"> -->
                            <p class='instructions'>Select the role of each of the companies that are listed below.</p>
                            <?php
                                foreach ($companies as $comp_id => $comp_name) {
                                    $rol = "role_" . $comp_id;
                                    echo "<div class=\"form-row\">";
                                    echo "<div class=\"col-md-4\">";
                                    echo "<label class=\"\" for=\"$rol\">$comp_name</label>";
                                    echo "</div>";
                                    echo "<div class=\"col-md-6\">";
                                    echo "<select class=\"custom-select\" id=\"$rol\" name=\"lic_role[]\">";
                                    echo "    <option selected>Choose...</option>";
                                    foreach ($license_role as $k => $v) {
                                        echo "<option value=\"$comp_id=$k\">$v</option>";
                                    }
                                    echo "</select>";
                                    echo "</div></div><!-- form-row -->\n";

                                }   
                            ?>
                        <!-- </div>< ! - - form-row -->
                        <div class="form-row">
                            <div class="col-md-6">
                                <p class="instructions">
                                Please make sure to complete the form before submitting.
                                </p>
                                <button name="submit" value="submit" type="submit" class="btn btn-primary">Submit</button>
                            </div>
                            <div class="col-md-6" style="text-align: right;">
                                <p class="instructions">
                                Use the "Skip this sample" button to proceed if his page looks incomplete or distorted,
                                 or you are unable to make a valid selection.
                                </p>
                                <button name="submit" value="skip" type="submit" class="btn btn-danger">Skip this sample</button>
                            </div>
                        </div><!-- form-row -->
                    </div>
                </form>
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
<!-- values from radio buttons https://stackoverflow.com/questions/596351/how-can-i-know-which-radio-button-is-selected-via-jquery -->

