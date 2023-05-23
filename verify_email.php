<?php 

    header("Access-Control-Allow-Origin: http://localhost:8100");
    
    // Set database connection variables
    $hostname = "127.0.0.1";
    $username = "root";
    $password = "David910139";
    $databasename = "FuseGap_Login";

    // Connect to the database
    $conn = mysqli_connect($hostname, $username, $password, $databasename);

    // Check if the connection was successful
    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }

    // Receive email from the frontend
    $email = $_GET['email'];

    // Query the database to check if the email exists
    $sql = "SELECT * FROM UserDetails WHERE UserDetails_email = '$email'";
    $result = $conn->query($sql);

    // Create a JSON response with the email and verification boolean
    $response = array();
    $response['email'] = $email;
    $response['exists'] = ($result->num_rows > 0);

    // Return the JSON response
    echo json_encode($response);

    // Close the database connection
    $conn->close();


?>