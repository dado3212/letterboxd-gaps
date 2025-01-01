
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Letterboxd Stats</title>
        <style>
            html {
                background-color: rgb(20, 24, 28);
                color: rgb(85, 102, 119);
                font-size: 16px;
                font-family: sans-serif;
            }
            #drop-area {
                border: 2px dashed #ccc;
                border-radius: 10px;
                width: 300px;
                height: 200px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 50px auto;
                text-align: center;
            }
            #drop-area.hover {
                border-color: #666;
                background-color: #f7f7f7;
            }
            #movies {
                display: flex;
                max-width: 800px;
                flex-wrap: wrap;
                gap: 4px;
                margin: 0 auto;

                .movie {
                    width: 30px;
                    height: 44px;
                    border: 1px solid;
                    border-radius: 4px;
                    overflow: hidden;
                    box-sizing: border-box;

                    &[data-id] {
                        border: none;
                    }

                    span {
                        font-size: 0.4em;
                        display: block;
                        white-space: nowrap;
                        transform-origin: 0;
                        transform: rotate(61deg);
                        margin: -1px 0 0 4px;
                    }

                    img {
                        width: 100%;
                    }
                }
            }
        </style>
    </head>
    <body>
        <p>
            Here are some letterboxd stats!

            Go to <a href="https://letterboxd.com/settings/data/" target="_blank">https://letterboxd.com/settings/data/</a> and export your data. Drag and drop the .zip here.
        </p>
        <div id="drop-area">
            Drag & Drop your .csv or .zip file here
        </div>

        <div id="movies">
        </div>

        <script>
            const dropArea = document.getElementById('drop-area');
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
            if (files[0].type === 'application/zip' || files[0].type === 'text/csv') {
                uploadFile(files[0]);
            } else {
                alert(files[0].type + ' is not .csv or .zip');
            }
            });

        // File upload
        function uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);

            fetch('get_watched_list.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                const movies = JSON.parse(data);

                const container = document.getElementById('movies');
                movies.forEach(movie => {
                    const movieDiv = document.createElement('div');
                    movieDiv.className = 'movie';
                    movieDiv.setAttribute('data-name', movie.Name);
                    movieDiv.setAttribute('data-year', movie.Year);
                    movieDiv.innerHTML = `
                        <span>${movie.Name} (${movie.Year})</span>
                    `;
                    container.appendChild(movieDiv);
                });
                fetchPostersAndInfo();
                // alert(`Server Response: ${data}`);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function fetchPostersAndInfo() {
            // For each poster we'll start a request to get
            // - picture
            // - country
            // - language
            // - female director
            const movies = document.querySelectorAll('.movie');

            // Create divs for each movie
            const fetchPromises = Array.from(movies).map(movie => {
                // if (movie.getAttribute('data-name') !== 'Snowpiercer') {
                //     return;
                // }
                return fetch('get_movie_info.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        name: movie.getAttribute('data-name'),
                        year: movie.getAttribute('data-year'),
                    }).toString(),
                })
                .then(response => response.text())
                .then(data => {
                    const info = JSON.parse(data);
                    movie.setAttribute('data-id', info.id);
                    movie.setAttribute('data-femaledirector', info.hasFemaleDirector);
                    movie.innerHTML = '<img src="' + info['poster'] + '" alt="' + movie.getAttribute('data-name') + '(' + movie.getAttribute('data-year')+')">'; 
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
            Promise.all(fetchPromises)
            .then(() => {
                console.log('All fetch operations completed');
            })
            .catch(error => {
                console.error('Error in parallel fetch:', error);
            });
        }
        </script>
    </body>
</html>