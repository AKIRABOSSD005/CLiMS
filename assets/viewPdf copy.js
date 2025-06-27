// PDF.js configuration options
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';
var pdfUrl = '../downloads/BY-LAWS-OF-BASPCC.pdf'; // Make sure the filename is correct

// Load the PDF document
pdfjsLib.getDocument(pdfUrl).promise.then(function (pdf) {
    // Loop through each page
    for (var pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
        // Fetch the page
        pdf.getPage(pageNumber).then(function (page) {
            var scale = 1.5;
            var viewport = page.getViewport({ scale: scale });

            // Prepare the canvas using PDF.js
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            // Render the page to the canvas
            var renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            page.render(renderContext);

            // Append the canvas to the viewer container
            var pdfViewer = document.getElementById('pdf-viewer');
            pdfViewer.appendChild(canvas);
        }).catch(function (reason) {
            console.error('Error rendering page: ' + reason);
        });
    }
}).catch(function (reason) {
    console.error('Error loading PDF: ' + reason);
});
