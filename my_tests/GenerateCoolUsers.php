<?php

//Connect DB
include_once 'db.class.php';
$db = new DB();
$db->connect();


// Insert Users
$i = 1000;
$db->insertCoolUser(
    $i, //id
    $name,// Name
    $surname,// Surname
    $email,// Email
    $industry, // Industry
    $goal, // Goal
    $foursquare, // Foursquare
    $place, // Place
    $summar, // Summary
    $photo, // Photo
    $phone, // Phone
    $lat, // Lat
    $lng, // Lng
    $skype, // Skype
    $rating, // Rating
    $experience, // Experience
    $city, // City
    $city_name // City name
);

// Add Jobs
$db->addJob(
    $user_id, // ID
    $name, // Position name
    $company, // Company
    $current, // Current
    $start_time, // Start Time
    $end_time, // End Time
    0
);

// Add Education
$db->addJob(
    $user_id, // ID
    $name, // Specialization
    $company, // Univer
    $current, // Current
    $start_time, // Start Time
    $end_time, // End Time
    1
);


mysql_close($db);
