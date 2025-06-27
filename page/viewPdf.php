<?php

session_start();
// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['email']) && !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}



// Get the filename from the URL parameter
$filename = isset($_GET['filename']) ? $_GET['filename'] : 'Untitled'; // Default to 'Untitled' if filename parameter is not provided
$filepath = '../downloads/' . $filename; // Path to the PDF file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $filename; ?></title>

    <style>
        /* Styles for the viewer container */
        #pdf-viewer {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            height: 100vh; /* Full height of viewport */
        }
        .pdf-page {
            margin: 10px;
        }
        
        /* styles.css */
body {
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; /* Full height for centering */
    background-color: #f4f4f4;
}

.pdf-viewer {
    width: 100%; /* Take full width */
    height: auto; /* Height will adjust based on content */
    max-width: 800px; /* Optional max width */
    overflow: auto; /* Enable scrolling if the content overflows */
    background-color: white; /* Background color for contrast */
    border-radius: 8px; /* Optional: rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Optional: subtle shadow */
}

canvas {
    width: 100%; /* Canvas will scale with the viewer */
    height: auto; /* Maintain aspect ratio */
}

    </style>
</head>
<body>
    <div id="pdf-viewer"></div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
    <script>
        // Get the filename from the URL parameter
        var queryString = window.location.search;
        var urlParams = new URLSearchParams(queryString);
        var filename = urlParams.get('filename') || 'Untitled'; // Default to 'Untitled' if filename parameter is not provided
        var filepath = '../downloads/' + filename; // Path to the PDF file

        // PDF.js configuration options
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';

        // Load and render the PDF document
        pdfjsLib.getDocument(filepath).promise.then(function (pdf) {
            var pdfViewer = document.getElementById('pdf-viewer');
            for (var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                pdf.getPage(pageNumber).then(function (page) {
                    var scale = 1.5;
                    var viewport = page.getViewport({ scale: scale });
                    var canvas = document.createElement('canvas');
                    var context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    page.render(renderContext);
                    pdfViewer.appendChild(canvas);
                });
            }
        }).catch(function (reason) {
            console.error('Error loading PDF: ' + reason);
        });
    </script>
    <script>
    // Get the filename from the URL parameter
    var queryString = window.location.search;
    var urlParams = new URLSearchParams(queryString);
    var filename = urlParams.get('filename') || 'Untitled'; // Default to 'Untitled' if filename parameter is not provided
    var filepath = '../downloads/' + filename; // Path to the PDF file

    // PDF.js configuration options
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';

    // Load and render the PDF document
    pdfjsLib.getDocument(filepath).promise.then(function (pdf) {
        var pdfViewer = document.getElementById('pdf-viewer');
        for (var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
            pdf.getPage(pageNumber).then(function (page) {
                var scale = 2.0; // Adjust the scale for higher resolution
                var viewport = page.getViewport({ scale: scale });
                var canvas = document.createElement('canvas');
                var context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                var renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                page.render(renderContext);
                pdfViewer.appendChild(canvas);
            });
        }
    }).catch(function (reason) {
        console.error('Error loading PDF: ' + reason);
    });
</script>

</body>
</html>
