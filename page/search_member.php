<?php
// Check if query parameter is set
if (isset($_GET['query'])) {
    $query = $_GET['query'];
    
   
    // Query to search members and combine fname, mname, lname
    $stmt = $conn->prepare("
        SELECT CONCAT(fname, ' ', mname, ' ', lname) AS full_name 
        FROM member 
        WHERE CONCAT(fname, ' ', mname, ' ', lname) LIKE ? 
        LIMIT 10
    ");
    $likeQuery = "%$query%";
    $stmt->bind_param('s', $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row; // Add full_name to the array
    }

    echo json_encode($members); // Return as JSON
    $stmt->close();
    $conn->close();
}
?>
