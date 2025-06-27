<?php
// PHP code to generate bcrypt hash
$password = 'MacWilson007*';  // The password you want to hash
$hashed_password = password_hash($password, PASSWORD_BCRYPT);  // Generate the bcrypt hash

// Output the hashed password
echo "Generated bcrypt hash: " . $hashed_password;
?>
