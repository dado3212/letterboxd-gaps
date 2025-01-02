
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
                width: 400px;
                height: 90px;
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
                    border-radius: 4px;
                    overflow: hidden;
                    box-sizing: border-box;

                    &.missing {
                        border: 1px solid;
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
            #stats {
                width: 400;
                margin: 0 auto;
                
                a {
                    display: block;
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

        <div id="stats">
            <div id="gender">
                <div id="female"></div>
                <div id="total"></div>
            </div>
            <div id="total">
                <div id="numWatched"></div>
            </div>
            <div id="countries">
                <div id="numWatched"></div>
            </div>
            <div id="language">
                <div id="numWatched"></div>
            </div>
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

            const container = document.getElementById('movies');
            container.innerHTML = '';

            fetch('get_watched_list.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const movies = JSON.parse(data);
                console.log(movies);

                let numTotal = 0;
                let numWomen = 0;
                let countries = {};
                let languages = {};

                movies.forEach(movie => {
                    const movieDiv = document.createElement('div');
                    movieDiv.className = 'movie';
                    if (movie.poster) {
                        movieDiv.innerHTML = `
                            <img src="https://image.tmdb.org/t/p/w92${movie.poster}" alt="${movie.movie_name} (${movie.year})">
                        `;
                    } else {
                        movieDiv.className += ' missing';
                        movieDiv.innerHTML = `
                            <span>${movie.movie_name} (${movie.year})</span>
                        `;
                    }
                    if (movie.tmdb_id) {
                        if (movie.has_female_director) {
                            numWomen += 1;

                            movieDiv.className += ' female';
                        }
                        numTotal += 1;
                        console.log(movie.language);
                        if (!(movie.language in languages)) {
                            languages[movie.language] = 0;
                        }
                        languages[movie.language] += 1;
                        for (const country of movie.countries) {
                            if (!(country in countries)) {
                                countries[country] = 0;
                            }
                            countries[country] += 1;
                        }
                    }
                    container.appendChild(movieDiv);
                });

                fetch('scrape_countries.php', {
                    method: 'GET',
                })
                .then(response => response.text())
                .then(data => {
                    const countryLanguageInfo = JSON.parse(data);
                    console.log(countryLanguageInfo);

                    // Handle stats setup
                    document.querySelector('#stats #gender #female').innerHTML = numWomen;
                    document.querySelector('#stats #gender #total').innerHTML = numTotal;

                    document.querySelector('#stats #total #numWatched').innerHTML = numTotal;

                    let countryHTML = '';
                    for (const country in countryLanguageInfo['countries']) {
                        countryHTML += '<a target="_blank" href="' + countryLanguageInfo['countries'][country]['url'] + '">' + countryLanguageInfo['countries'][country]['full'] + '(' + countryLanguageInfo['countries'][country]['count'] + ')';
                        if (country in countries) {
                            countryHTML += '✅: ' + countries[country] + '</a>';
                        } else {
                            countryHTML += '❌ </a>';
                        }
                    }
                    document.querySelector('#stats #countries #numWatched').innerHTML = countryHTML;

                    let languageHTML = '';
                    for (const language in countryLanguageInfo['languages']) {
                        languageHTML += '<a target="_blank" href="' + countryLanguageInfo['languages'][language]['url'] + '">' + countryLanguageInfo['languages'][language]['full'] + '(' + countryLanguageInfo['languages'][language]['count'] + ')';
                        if (language in languages) {
                            languageHTML += '✅: ' + languages[language] + '</a>';
                        } else {
                            languageHTML += '❌ </a>';
                        }
                    }
                    document.querySelector('#stats #language #numWatched').innerHTML = languageHTML;

                });
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        </script>
    </body>
</html>