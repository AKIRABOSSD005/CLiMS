<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if a file was uploaded without errors
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        // Define allowed file types
        $allowed_types = array("jpg", "jpeg", "png", "gif", "pdf", "docx", "doc", "xlsx", "xls");

        // Get file details
        $filename = basename($_FILES["file"]["name"]);
        $file_type = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            echo "Sorry, only JPG, JPEG, PNG, GIF, and PDF files are allowed.";
        } else {
            // Specify the directory where you want to store the uploaded files
            $target_dir = "../downloads/";

            // Ensure the target directory exists
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            // Set the target file path
            $target_file = $target_dir . $filename;

            // Move the uploaded file to the specified directory
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                // File upload success, now store information in the database
                $filesize = $_FILES["file"]["size"];
                $filetype = $_FILES["file"]["type"];

                // Database connection
                require_once '../conn/dbcon.php';

                // Insert the file information into the database using prepared statements
                $sql = "INSERT INTO download_file (name, file_size, file_type) VALUES (?, ?, ?,)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("sds", $filename, $filesize, $filetype);
                    if ($stmt->execute()) {
                        echo "The file " . htmlspecialchars($filename) . " has been uploaded and the information has been stored in the database.";
                    } else {
                        echo "Sorry, there was an error storing file information in the database: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    echo "Sorry, there was an error preparing the SQL statement: " . $conn->error;
                }

                $conn->close();
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        echo "No file was uploaded or an error occurred.";
    }
}
?>
