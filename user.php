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
session_start();
$_SESSION["email"] = $email;
$email_session = $_SESSION["email"];


// Query the database to check if the email exists
$sql = "SELECT * FROM UserDetails WHERE UserDetails_email = '$email'";
$result = $conn->query($sql);

// Create a JSON response with the email and verification boolean
$response = array();
$response['email'] = $email;
$response['exists'] = ($result->num_rows > 0);

// Generate a random 6-digit code
$otp = rand(100000, 999999);

// Set the OTP expiration time to 3 minutes from now
$otpExpiration = date('Y-m-d H:i:s', strtotime('+3 minutes'));

// Prepare the SQLstatement to insert the OTP into the database
$stmt = $conn->prepare("INSERT INTO UserLogin (Userlogin_email, Userlogin_otp, Userlogin_otp_expiration) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $otp, $otpExpiration);

// Insert the OTP into the database
$result = $stmt->execute();

// Check if the insert was successful
if (!$result) {
    die("Error: " . mysqli_error($conn));
}

// Prepare the SQL statement to get the last match email's OTP from the database
$stmt = $conn->prepare("SELECT Userlogin_otp FROM UserLogin WHERE Userlogin_email = ? ORDER BY Userlogin_id DESC LIMIT 1");
$stmt->bind_param("s", $email);

// Execute the SQL statement to get the last match email's OTP from the database
$result = $stmt->execute();

// Check if the execute was successful
if (!$result) {
    die("Error: " . mysqli_error($conn));
}

// Bind the result variables to the prepared statement
$stmt->bind_result($otpFromDB);

// Fetch the results and create the JSON response
$response['otp'] = "";
$response['otp_available'] = false;

if ($stmt->fetch()) {
    // Check if the OTP is still valid (not expired)
    if (strtotime($otpExpiration) > time()) {
        $response['otp'] = $otpFromDB;
        $response['otp_available'] = true;
    }
}

// Return the JSON response
echo json_encode($response);

// Close the databaseconnection
$conn->close();

?>