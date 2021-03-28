<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    //session_start();

    require 'header.inc.php';

    function get_selection($adj) {
        if (defined($_SESSION["opts"], $_SESSION["opts"]["val"])) {
            return $_SESSION["opts"]["val"];
        } else {
            return 0;
        }
    }

    if (!isset($_SESSION['survey_similarity'])) {
        $_SESSION['survey_similarity'] = [];
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

<?php



    $pos = -1;
    $max_pos = -1;

    $cnt = 100;
    $uid = '';
    // check if this is a response, and add to database
    if (isset($_REQUEST)) {
        if (isset($_REQUEST['cnt'])) { $cnt = (int)$_REQUEST['cnt']; }
        if (isset($_REQUEST['pos'])) { $pos = (int)$_REQUEST['pos']; }
        if (isset($_REQUEST['uid'])) { $uid = $_REQUEST['uid']; }
        
    }

    // get next assignment record
    $conn = db_connect();
    $uid_clause = strlen($uid)>0 ? " AND userid='$uid'" : "";
    $q = "
        SELECT a.*, r.label, r.ts
        FROM assignments_similar a
        LEFT JOIN responses r
        ON r.assignment_id=a.assignment_id
       
        WHERE pos > $pos  $uid_clause
        ORDER BY pos
        LIMIT $cnt
    ";
    $res = pg_query($conn, $q);
    if (!$res) {
        echo "An error occurred.\n";
        exit;
    }

    // if nothing left to do go to completed page
    if (pg_num_rows($res)==0) {
        // $_SESSION['survey_similarity']['stage'] = 'data';
        // header("Location: data.php");
        $_SESSION['survey_similarity']['stage'] = 'completed';
        header("Location: completed.php");
        die();
    }

    while ($row = pg_fetch_assoc($res)) {
        // returned row: {
        //     "assignment_id" => string(74) "4|Dis_similar_images/7331_3885_29.jpg|Dis_similar_images/3895_14035_72.jpg",
        //     "pos" => string(1) "1",
        //     "title" => string(5) "Fonts",
        //     "question" => string(82) "Do you see text in the two images that use the same – or very similar – fonts?",
        //     "idx"=> string(1) "4"
        //     "a_img"]=> string(35) "Dis_similar_images/7331_3885_29.jpg",
        //     "a_idx"]=> string(4) "2953",
        //     "b_img"]=> string(36) "Dis_similar_images/3895_14035_72.jpg",
        //     "b_idx"]=> string(5) "10103"
        // }
        
        $p = (int)$row["pos"];
        if ($p > $max_pos) { $max_pos = $p; }
        $picurl_left = "image_data/" . $row["a_img"];
        $picurl_right = "image_data/" . $row["b_img"];

    ?>
            <div style="display: inline; border: 1px solid black; padding: 4px; margin: 4px;">
                <img width="120" src="<?= $picurl_left ?>" />
                <img width="120" src="<?= $picurl_right ?>" />
            </div>
<?php
    } // end of while

    db_free_result($res);
    db_close($conn);
  
?>


            <div style="text-align: center">
                <a href="?pos=<?= $max_pos ?>">Next</a>
            </div>
        
        </div>

        <!-- <?= $save_message ?> -->

        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script> -->
        <script>
        
        $(document).ready(function(){
            console.log("document ready")
            
        })
        </script>
    </BODY>
</HTML>

<?php
    //}
?>
