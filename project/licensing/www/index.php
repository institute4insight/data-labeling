<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require 'header.inc.php';

    // function get_selection($adj) {
    //     if (defined($_SESSION["opts"], $_SESSION["opts"]["val"])) {
    //         return $_SESSION["opts"]["val"];
    //     } else {
    //         return 0;
    //     }
    // }


    function login_form($msg, $username) {
        if (strlen($username)>0) {
            $maybe_value = " value=\"$username\"";
        } else {
            $maybe_value = "";
        }
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
                        <!-- <div class="col"></div> -->
                        <div class="col align-self-center">
                        <h1>Labeling Task: License Agreements</h1>
                        <?php
                            if (strlen($msg)>0) {
                                echo "<h4 style=\"color: red;\">$msg</h4>\n";
                            }
                        ?>
                        </div>
                        <!-- <div class="col"></div> -->
                    </div>
                    <div class="row>">
                        <div class="col">
                            <?php // readfile("consent-form.html"); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <p>
                            Please enter your user name or email address to start.
                            </p>
                            <form method=post>
                                <!-- <input type="checkbox" name="consent" /> I agree to participate in this survey
                                <br/> -->
                                <span>User name or email address:</span>&nbsp;
                                <input type="text" name="u" <?php echo $maybe_value; ?>/>
                                &nbsp;&nbsp;&nbsp;
                                <input type="submit" value="Continue" />
                            </form>
                        </div><!-- .col -->
                    </div><!-- .row -->
                </div><!-- .container -->
            </BODY>
        </HTML>

<?php
    }

    $error_message = '';

    if (!isset($_SESSION['survey_licensing'])) {
        $_SESSION['survey_licensing'] = [];
    }


    if (isset($_SESSION['survey_licensing']['user_id'])) {
        header('Location: evaluate.php');
    } else {
        if (isset($_REQUEST['u'])) {
            # validate user id
            $uid = preg_split("/@/", pg_escape_string($_REQUEST['u']))[0];
            $n_required = 0;

            $conn = db_connect();
            $q = "
            SELECT COUNT(*) AS n
            FROM licensing_assignments
            WHERE user_id='$uid'
            ";
            
            if( $res = $conn->query($q) ) {
                $row = $res->fetch_assoc();
                $n_required = $row['n'];
                $res->close();
            }
            $conn->close();

            if ($n_required>0) {
                $_SESSION['survey_licensing']['user_id'] = $uid;
               
                header("Location: evaluate.php");
            } else {
                $error_message = "The User ID is not valid. Please, try again";
                login_form($error_message, "");
            }
        } else {
            if (isset($_REQUEST['u'])) {
                login_form("", $_REQUEST['u']);
            } else {
                login_form("", "");
            }
        }
    }
?>
