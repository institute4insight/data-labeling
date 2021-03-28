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
        $creds = json_decode(file_get_contents("secret/dbcredentials.json"), true);
        $dbconn = pg_connect("host=" . $creds['host'] . " dbname=" . $creds['dbname']
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

    $questions = [
        [   
            "title" => "Background",
            "quest" => "Do these images have an identical or a very similar background? Answer 'No' if the backgrounds are plain- white or plain-black.",
            "yes" => "Yes, the backgrounds are similar",
            "no" => "No, the backgrounds are not similar",
            "stem" => "background",
            "pairs" => [ ["left" => "_1", "right" => "_2"],  ["left" => "_3", "right" => "_4"]],
            "positive" => "The background of the two logos are very similar.",
        ],
        [   
            "title" => "Color Difference",
            "quest" => "Do the two images share any identical or nearly identical objects, not including text, that differ only in color?",
            "yes" => "Yes, their are similar but differ in color",
            "no" => "No, they are not similar",
            "stem" => "objects",
            "pairs" => [ ["left" => "_1", "right" => "_2"] ],
            "positive" => "You see multiple objects in the image such as the speedometer which are identical in shape but different in color."

        ],
        [   
            "title" => "Rotation",
            "quest" => "Do you see a rotated version of the same object in the two logos?",
            "yes" => "Yes, they are the same in different rotations",
            "no" => "No, they are not the same",
            "stem" => "rotated",
            "pairs" => [ ["left" => "_1", "right" => "_2"] ],
            "positive" => "The object in one logo is the rotated version of an object in the other logo."
        ],
        [   
            "title" => "Scale",
            "quest" => "Do you see a larger or smaller version of one an object from one image in the other image?",
            "yes" => "Yes, they are similar but differ in scale",
            "no" => "No, they are not similar",
            "stem" => "scaled",
            "pairs" => [ ["left" => "_1", "right" => "_2"] ],
            "positive" => "Multiple objects such as the text or the alpha are scaled versions of the same objects in the other image."
        ],
        [   
            "title" => "Fonts",
            "quest" => "Do you see text in the two images that use the same – or very similar – fonts?",
            "yes" => "Yes, they use similar fonts",
            "no" => "No, the fonts are different",
            "stem" => "fonts",
            "pairs" => [ ["left" => "_1", "right" => "_2"] ],
            "positive" => "The letters L, P and H share almost identical fonts."
        ],
        [   
            "title" => "Same Objects",
            "quest" => "Do you see any objects in the two images that are identical or very similar?",
            "yes" => "Yes, there are similar objects in both images",
            "no" => "No, they don't have the same or similar objects",
            "stem" => "identical_objects",
            "pairs" => [ ["left" => "_1", "right" => "_2"] ],
            "positive" => "These two logos are considered sharing similar objects (the hand and the cat are common)."
        ],
        [   
            "title" => "Main Idea",
            "quest" => "In your opinion, are these two images based on the same central idea?",
            "yes" => "Yes, both logos represent the same idea",
            "no" => "No, their main idea is different",
            "stem" => "idea",
            "pairs" => [ ["left" => "_1", "right" => "_2"] ],
            "positive" => "The two logos share a similar idea: an alpha with a speedometer inside."
        ]
    ];
    $n_question = count($questions);
?>
<!-- Nothing to see -->
