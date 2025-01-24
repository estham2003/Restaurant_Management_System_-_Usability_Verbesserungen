<?php
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', "root");
define('DB_PASSWORD', "");
define('DB_DATABASE', 'africa');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a global database connection variable
$globalDB = null;

// Function to get database connection
function getDB()
{
    global $globalDB;
    // Use the global variable to store the connection

    if ($globalDB === null || $globalDB->ping() === false) {
        $globalDB = new mysqli( DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

        if ($globalDB->connect_error) {
            die("Connection failed: " . $globalDB->connect_error);
        }

        // Set the connection to use exceptions for error reporting
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }
    return $globalDB;
}

// Ensure the connection is ready when required
getDB();


