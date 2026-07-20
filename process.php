<?php
$username = $_POST['username'];
$email = $_POST['email'];

if (empty($username) || empty($email)) {
    echo "<h3 style='color:red;'>Error: All fields are required.</h3>";
}
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<h3 style='color:red;'>Error: Invalid Email.</h3>";
}
else {
    echo "<h3 style='color:green;'>Success!</h3>";
    echo "Username: $username <br>";
    echo "Email: $email";
}
?>