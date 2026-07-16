<?php

require "../config/session.php";

require "../config/database.php";


// Check if employer is logged in

if(!isset($_SESSION['user_id'])){

    header("Location: ../login.php");
    exit();

}



if($_SERVER["REQUEST_METHOD"] == "POST"){


    $employer_id = $_SESSION['user_id'];

    $title = trim($_POST['title']);

    $description = trim($_POST['description']);

    $category = trim($_POST['category']);

    $location = trim($_POST['location']);

    $budget = trim($_POST['budget']);

    $job_type = $_POST['job_type'];



    // Validation

    if(
        empty($title) ||
        empty($description) ||
        empty($category)
    ){

        die("Please fill all required fields");

    }



    // Insert job


    $stmt = $conn->prepare(

        "INSERT INTO jobs

        (
        employer_id,
        title,
        description,
        category,
        location,
        budget,
        job_type
        )

        VALUES
        (?,?,?,?,?,?,?)"

    );



    $stmt->execute([

        $employer_id,
        $title,
        $description,
        $category,
        $location,
        $budget,
        $job_type

    ]);



    header("Location: dashboard.php?success=job_created");

    exit();


}

?>