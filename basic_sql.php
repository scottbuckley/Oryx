<?php
    define('OPT_PAGE_TITLE', 'SQL Query');

    function rqst($key) {
        if (isset($_REQUEST[$key]))
            return $_REQUEST[$key];
        return null;
    }

    if (rqst("queryparam")) {
        
    }

?>

<html>
    <head>
        <title><?php echo OPT_PAGE_TITLE; ?></title>
    </head>
    <body>
        <form method="GET">
            <input type="text" name="queryparam"/>
            <input type="submit" value="Query"/>
        </form>
        <?php echo $postContent; ?>
    </body>
</html>