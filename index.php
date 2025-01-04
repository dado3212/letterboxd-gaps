
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Letterboxd Gaps</title>
        <style>
            @font-face {
                font-family: SharpGroteskSmBold21;
                font-display: block;
                src: url('./fonts/letterboxdLogo.woff2') format('woff2');
            }
            @font-face {
                font-family: SharpGroteskBook15;
                font-display: block;
                src: url('./fonts/subtext.woff2') format('woff2');
            }
            .title {
                color: #eff1f2;

                display: flex;
                flex-direction: column;
                align-items: center;

                -webkit-user-select: none;
                user-select: none;
            }
            .header {
                font-family: SharpGroteskSmBold21;
                font-size: 4em;
                position: relative;

                .progress {
                    position: absolute;
                    bottom: 0px;
                    width: 100%;
                    overflow: hidden;

                    transition: 0.5s ease;
                
                    .wrapper {
                        position: absolute;
                        bottom: 0px;
                    }
                }

                .green {
                    color: #00E054;
                }

                .blue {
                    color: #40BCF4;
                }

                .orange {
                    color: #FF8000;
                }
            }
            .subtext {
                font-family: SharpGroteskBook15;
                font-size: 2em;
                letter-spacing: 2em;

                /* To offset the last letter spacing */
                margin-right: -2em;
            }
            @keyframes fillAnimation {
                from {
                    height: 0%;
                }
                to {
                    height: 100%;
                }
                }
            html {
                background-color: #14181c;
                color: rgb(85, 102, 119);
                font-size: 16px;
                font-family: sans-serif;

                transition: 0.3s ease;
            }
            html.hover {
                background-color: #283039;
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
        <?php
            require_once("tmdb.php");

            error_reporting(E_ALL);
            ini_set('display_errors', 1);

            // Manually picked 50 notable posters
            $colors = [
                152601 => ['red', 'Her'],
                252171 => ['red', 'A girl walks home alone at night'],
                284 => ['red/orange', 'The Apartment'],
                426 => ['orange', 'Vertigo'],
                9806 => ['red', 'The Incredibles'],
                843 => ['red', 'In the mood for love'],
                592695 => ['pink', 'Pleasure'],
                1049638 => ['orange', 'Rye Lane'],
                835113 => ['orange', 'Woman of the hour'],
                194 => ['red', 'Amelie'],
                
            ];

            $PDO = getDatabase();
            $stmt = $PDO->prepare("SELECT poster, primary_color FROM movies WHERE tmdb_id IN (" . implode(',', array_keys($colors)) . ")"); // ORDER BY RAND()
            $stmt->execute();
            $posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($posters as $poster) {
                echo '<img style="width:100px;" src="' . $poster['poster'] .'" />';
            }
        ?>
        <div class="title">
            <div class="header">
                <div class="normal">Letterboxd</div>
                <div class="progress">
                    <div class="wrapper">
                        <span class="orange">Let</span><span class="green">ter</span><span class="blue">boxd</span>
                    </div>
                </div>
            </div>
            <div class="subtext">GAPS</div>
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
            const listening = true;
            const dropArea = document.querySelector('html');
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

            function pingHome() {
                fetch('get_movie_info.php', {
                    method: 'GET',
                })
                .then(response => response.text())
                .then(rawData => {
                    if (JSON.parse(rawData)['status'] != 'finished') {
                        pingHome();
                    } else {
                        console.log('finished');
                    }
                });
            }

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
                .then(rawData => {
                    const data = JSON.parse(rawData);
                    movies = data.movies;
                    console.log(data);

                    let numTotal = 0;
                    let numWomen = 0;
                    let countries = {};
                    let languages = {};

                    if (data.upload_count > 0) {
                        // Start the process
                        pingHome();
                        // 25% to 81%
                        const progressBar = document.querySelector('.progress');
                        progressBar.style.height = '25%';

                        const interval = setInterval(() => {
                            fetch('poll_status.php?id=' + data.upload_id)
                            .then(response => response.json())
                            .then(data => {
                                console.log(data);
                                progressBar.style.height = `${25 + (81 - 25) * (data.done / data.total)}%`;
                                if (data.done == data.total) {
                                    clearTimeout(interval);
                                }
                            });
                        }, 2000);
                    }

                    movies.forEach(movie => {
                        const movieDiv = document.createElement('div');
                        movieDiv.className = 'movie';
                        if (movie.poster) {
                            if (movie.poster.startsWith('/')) {
                                movieDiv.innerHTML = `
                                    <img src="https://image.tmdb.org/t/p/w92${movie.poster}" alt="${movie.movie_name} (${movie.year})">
                                `;
                            } else {
                                movieDiv.innerHTML = `
                                    <img src="${movie.poster}" alt="${movie.movie_name} (${movie.year})">
                                `;
                            }
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
                            if (movie.language) {
                                if (!(movie.language in languages)) {
                                    languages[movie.language] = 0;
                                }
                                languages[movie.language] += 1;
                            }
                            if (movie.countries) {
                                for (const country of movie.countries) {
                                    if (!(country in countries)) {
                                        countries[country] = 0;
                                    }
                                    countries[country] += 1;
                                }
                            }
                        }
                        container.appendChild(movieDiv);
                    });

                    // fetch('scrape_countries.php', {
                    //     method: 'GET',
                    // })
                    // .then(response => response.text())
                    // .then(data => {
                    //     const countryLanguageInfo = JSON.parse(data);
                    //     console.log(countryLanguageInfo);

                    //     // Handle stats setup
                    //     document.querySelector('#stats #gender #female').innerHTML = numWomen;
                    //     document.querySelector('#stats #gender #total').innerHTML = numTotal;

                    //     document.querySelector('#stats #total #numWatched').innerHTML = numTotal;

                    //     let countryHTML = '';
                    //     for (const country in countryLanguageInfo['countries']) {
                    //         countryHTML += '<a target="_blank" href="' + countryLanguageInfo['countries'][country]['url'] + '">' + countryLanguageInfo['countries'][country]['full'] + '(' + countryLanguageInfo['countries'][country]['count'] + ')';
                    //         if (country in countries) {
                    //             countryHTML += '✅: ' + countries[country] + '</a>';
                    //         } else {
                    //             countryHTML += '❌ </a>';
                    //         }
                    //     }
                    //     document.querySelector('#stats #countries #numWatched').innerHTML = countryHTML;

                    //     let languageHTML = '';
                    //     for (const language in countryLanguageInfo['languages']) {
                    //         languageHTML += '<a target="_blank" href="' + countryLanguageInfo['languages'][language]['url'] + '">' + countryLanguageInfo['languages'][language]['full'] + '(' + countryLanguageInfo['languages'][language]['count'] + ')';
                    //         if (language in languages) {
                    //             languageHTML += '✅: ' + languages[language] + '</a>';
                    //         } else {
                    //             languageHTML += '❌ </a>';
                    //         }
                    //     }
                    //     document.querySelector('#stats #language #numWatched').innerHTML = languageHTML;
                    // });
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        </script>
    </body>
</html>