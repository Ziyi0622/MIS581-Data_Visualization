<?php
// Connection parameters
$servername = "localhost"; // Your MySQL server address
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$database = "Movie"; // Your MySQL database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Open the directory containing the images
$imageDirectory = 'imdb_photos/';
$images = scandir($imageDirectory);

// Prepare SQL statement for inserting images
$stmt = $conn->prepare("INSERT INTO Images1 (movie, image) VALUES (?, ?)");

// Loop through each image file
foreach ($images as $image) {
    // Check if it's a valid image file
    if (in_array(pathinfo($image, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])) {
        // Extract movie title from the file name
        $movie = pathinfo($image, PATHINFO_FILENAME);

        // Read the image file
        $imageData = file_get_contents($imageDirectory . $image);

        // Bind parameters and execute the statement
        $stmt->bind_param("ss", $movie, $imageData);
        $stmt->execute();
    }
}

echo "Images inserted successfully.";

// Close statement and connection
$stmt->close();
$conn->close();
?>