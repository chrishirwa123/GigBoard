<?php

require "../config/session.php";

require "../config/database.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];


    // Check empty fields

    if(
        empty($fullname) ||
        empty($username) ||
        empty($email) ||
        empty($phone) ||
        empty($password)
    ){

        die("Please fill all fields");

    }


    // Check password match

    if($password !== $confirm_password){

        die("Passwords do not match");

    }


    // Password strength check

    if(strlen($password) < 10){

        die("Password must contain at least 10 characters");

    }



    // Check existing user

    $check = $conn->prepare(
        "SELECT id FROM users 
        WHERE email = ? 
        OR username = ? 
        OR phone = ?"
    );


    $check->execute([
        $email,
        $username,
        $phone
    ]);


    if($check->rowCount() > 0){

        die("Username, email, or phone already exists");

    }



    // Hash password

    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );



    // Insert user

    $insert = $conn->prepare(

        "INSERT INTO users
        (
        fullname,
        username,
        email,
        phone,
        password,
        role
        )

        VALUES
        (?,?,?,?,?,?)"

    );


    $insert->execute([

        $fullname,
        $username,
        $email,
        $phone,
        $hashedPassword,
        $role

    ]);



    // Redirect based on role

    if ($role === 'worker') {

        header("Location: ../worker/onboarding.php");
        exit;

    }

    echo "Registration successful!";

    // Later we redirect to login page

}


?>