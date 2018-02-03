<?php error_reporting(E_ALL); ini_set('display_errors', 1); ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Logged Out</title>
    </head>
    <?php
        session_start();

        if (isset($_SESSION['username'])) {
          destroy_session_and_data();
        }

        function destroy_session_and_data() {
          $_SESSION = array();
          setcookie(session_name(), '', time() - 2592000, '/');
          session_destroy();
        }

    ?>
    <body>
        <h1>Logged Out</h1>
        <p>
            You are now logged out of the website.
        </p>
        <p>
            <a href="login.php">Log in</a> again.
        </p>
    </body>
</html>
