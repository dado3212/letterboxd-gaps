
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Letterboxd Stats</title>
        <style>
            .drop-area {
                border: 2px dashed #ccc;
                border-radius: 10px;
                width: 300px;
                height: 200px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 50px auto;
                text-align: center;
                color: #333;
            }
            .drop-area.hover {
                border-color: #666;
                background-color: #f7f7f7;
            }
        </style>
    </head>
    <body>
        <p>
            Here are some letterboxd stats!

            Go to <a href="https://letterboxd.com/settings/data/" target="_blank">https://letterboxd.com/settings/data/</a> and export your data. Drag and drop the .zip here.
        </p>
        <div class="drop-area">
            Drag & Drop your .zip file here
        </div>

        <div class="drop-area">
            Drag & Drop your .csv file here
        </div>

        <script>
const dropAreas = document.querySelectorAll('.drop-area');

dropAreas.forEach(dropArea => {
            // Prevent default behaviors for drag events
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, e => e.preventDefault());
            });

            // Highlight area on dragover
            ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.add('hover'));
            });

            ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.remove('hover'));
            });

            // Handle file drop
            dropArea.addEventListener('drop', event => {
            const files = event.dataTransfer.files;
            if (files.length !== 1) {
                alert('Please upload a valid .zip or .csv file.');
                return;
            }
            if (files[0].type === 'application/zip') {
                uploadFile(files[0]);
            } else if (files[0].type === 'text/csv') {
                uploadFile(files[0]);
            } else {
                alert(files[0].type + ' is not .csv or .zip');
            }
            });
        });

        // File upload
        function uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);

            fetch('process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                // alert(`Server Response: ${data}`);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        </script>
    </body>
</html>