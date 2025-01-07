
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
            @font-face {
                font-family: GraphikSemiBold;
                font-display: block;
                src: url('./fonts/Graphik-Semibold-Web.woff') format('woff');
            }
            .center-wrapper {
                width: 100%;
                height: 100%;

                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            .center {
                position: relative;

                -webkit-user-select: none;
                user-select: none;
            }
            .center .title {
                text-align: center; 
                padding: 35px 60px;
            }
            .title {
                color: #eff1f2;
            }
            .header {
                font-family: SharpGroteskSmBold21;
                font-size: 4em;
                position: relative;
                top: 0px;
                left: 0px;

                transition: top 2s, left 1s;

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
            .nav {
                display: none;
            }
            .nav .title {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-left: -80px;
            }
            .nav .subtext {
                margin-left: 50px;
                letter-spacing: 1em;
            }
            .menu {
                display: flex;
                align-items: center;
                padding: 5px 0;
                justify-content: center;
            }
            .menu button {
                all: unset;
                cursor: pointer;
                padding: 9px 12px 8px;
                border-radius: 3px;
                margin-left: 34px;
                background: #00ac1c;
                box-shadow: inset 0 1px 0 hsla(0, 0%, 100%, .3);
                color: #f4fcf0;
                text-transform: uppercase;
                -webkit-font-smoothing: antialiased;

                font-family: GraphikSemiBold, sans-serif;

                -webkit-user-select: none;
                -moz-user-select: none;
                -o-user-select: none;
                user-select: none;

                font-size: 0.8em;
                font-weight: 400;
                letter-spacing: .075em;
                line-height: 12px;
            }
            .menu button:hover {
                background: #009d1a;
                color: #fff;
            }
            .menu #numMovies .total {
                font-weight: bold;
            }
            .menu #numMovies .filter {
                display: none;
            }
            .subtext {
                font-family: SharpGroteskBook15;
                font-size: 2em;
                letter-spacing: 2em;

                /* To offset the last letter spacing */
                margin-right: -2em;

                position: relative;
                top: 0px;
                left: 0px;
                transition: top 2s, left 1s, letter-spacing 2s;
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
                color: #d8e0e8;
                font-size: 16px;
                font-family: sans-serif;
                height: 100%;

                transition: 0.3s ease;
            }
            body {
                width: 100%;
                height: 100%;
                margin: 0;
            }
            html.hover {
                background-color: #1c2127;
            }
            .center img {
                border-radius: 4px;
                position: absolute;

                transition: top 0.3s, left 0.3s, opacity 2s;
            }
            
            #movies {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
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
        <!-- The main center -->
        <div class="center-wrapper">
            <div class="center">
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
                <?php
                    require_once("tmdb.php");

                    // Manually picked 50 notable posters
                    $colors = [
                        152601 => ['red', 'Her'],
                        252171 => ['red', 'A girl walks home alone at night'],
                        284 => ['red/orange', 'The Apartment'],
                        426 => ['orange', 'Vertigo'],
                        9806 => ['red', 'The Incredibles'],
                        843 => ['red', 'In the mood for love'],
                        // 592695 => ['pink', 'Pleasure'],
                        1049638 => ['orange', 'Rye Lane'],
                        835113 => ['orange', 'Woman of the hour'],
                        194 => ['red', 'Amelie'],
                        110 => ['red', 'Red'],
                        // 994108 => ['orange?', 'All of us strangers'],
                        693134 => ['orange', 'DUne 2'],
                        // 290098 => ['orange-yellow', 'The Handmaiden'],
                        3086 => ['yellow', 'The Lady Eve'],
                        814340 => ['yellow', 'Cha Cha Real Smooth'],
                        // 212778 => ['yellow', 'Chef'],
                        773 => ['yellow', 'little miss sunshine'],
                        389 => ['yellow', '12 angry men'],
                        86838 => ['lime green', 'seven psychopaths'],
                        91854 => ['green', 'seawall'],
                        85350 => ['green', 'boyhood'],
                        60308 => ['green', 'moneyball'],
                        1386881 => ['green', 'james acaster hecklers welcome'],
                        995771 => ['light blue', 'la frontera'],
                        965150 => ['blue', 'aftersun'],
                        149870 => ['blue', 'the wind rises'],
                        394117 => ['blue', 'the florida project'],
                        12 => ['blue', 'finding nemo'],
                        398818 => ['blue', 'call me by your name'],
                        372058 => ['blue', 'your name'],
                        38757 => ['yellow', 'tangled'],
                        // 1160164 => ['pink', 'eras tour'],
                        328387 => ['pink/blue', 'nerve'],
                        424781 => ['purple', 'sorry to bother you'],
                        121986 => ['pink', 'frances ha'],
                        354275 => ['purple', 'right now, wrong then'],
                        313369 => ['dark blue/purple', 'la la land'],
                        20139 => ['purple', 'childrens hour'],
                        10315 => ['orange', 'fantastic mr fox'],
                        866398 => ['orange yellow', 'the beekeeper'],
                        10681 => ['purply', 'walle']
                    ];

                    $PDO = getDatabase();
                    $stmt = $PDO->prepare("SELECT poster, primary_color, id, tmdb_id FROM movies WHERE tmdb_id IN (" . implode(',', array_keys($colors)) . ")");
                    $stmt->execute();
                    $posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    usort($posters, function($a, $b) {
                        return (float)json_decode($a['primary_color'] ?? "{'h': 0}", true)['h'] <=> (float)json_decode($b['primary_color'] ?? "{'h': 0}", true)['h'];
                    });

                    foreach ($posters as $poster) {
                        echo '<img style="width:100px;" src="' . $poster['poster'] .'" data-tmdb="' . $poster['tmdb_id'] . '" />';
                    }
                ?>
                <script>
                    // Manually created gallery wall using JS code in READMe
                    var positions = [{"id":252171,"width":70,"left":107,"top":-254},{"id":843,"width":80,"left":312,"top":-254},{"id":284,"width":60,"left":117,"top":-108},{"id":194,"width":60,"left":218,"top":-259},{"id":9806,"width":100,"left":195,"top":-153},{"id":426,"width":80,"left":16,"top":-123},{"id":1049638,"width":70,"left":7,"top":-248},{"id":835113,"width":90,"left":-112,"top":-204},{"id":38757,"width":70,"left":-204,"top":-154},{"id":693134,"width":80,"left":-89,"top":-47},{"id":10315,"width":90,"left":-109,"top":234},{"id":866398,"width":110,"left":-229,"top":-28},{"id":3086,"width":70,"left":11,"top":184},{"id":814340,"width":80,"left":-328,"top":44},{"id":773,"width":70,"left":-90,"top":105},{"id":389,"width":100,"left":-228,"top":163},{"id":86838,"width":80,"left":-2,"top":312},{"id":91854,"width":60,"left":122,"top":347},{"id":85350,"width":60,"left":239,"top":200},{"id":60308,"width":90,"left":112,"top":196},{"id":965150,"width":50,"left":350,"top":362},{"id":995771,"width":100,"left":328,"top":195},{"id":1386881,"width":90,"left":224,"top":318},{"id":149870,"width":70,"left":444,"top":173},{"id":394117,"width":90,"left":528,"top":91},{"id":12,"width":90,"left":444,"top":295},{"id":398818,"width":70,"left":548,"top":243},{"id":328387,"width":60,"left":748,"top":-94},{"id":372058,"width":100,"left":635,"top":152},{"id":10681,"width":90,"left":750,"top":35},{"id":313369,"width":100,"left":632,"top":-22},{"id":20139,"width":90,"left":515,"top":-64},{"id":424781,"width":100,"left":620,"top":-189},{"id":121986,"width":80,"left":515,"top":-211},{"id":354275,"width":90,"left":404,"top":-137},{"id":152601,"width":50,"left":423,"top":-235},{"id":110,"width":60,"left":318,"top":-104}];
                    for (const position of positions) {
                        const item = document.querySelector(`.center img[data-tmdb="${position.id}"]`);
                        item.style.width = `${position.width}px`;
                        item.style.top = `${position.top}px`;
                        item.style.left = `${position.left}px`;
                        item.setAttribute('top', position.top);
                        item.setAttribute('left', position.left);
                    }
                </script>
            </div>
        </div>

        <!-- After uploading, top bar -->
        <div class="nav">
            <div class="title">
                <div class="header">
                    <div class="normal">Letterboxd</div>
                </div>
                <div class="subtext" onclick="transitionToAnalysis()">GAPS</div>
            </div>
            <div class="menu">
                <div id="numMovies"><span class='filter'>0 out of </span><span class='total'>0</span> movies</div>
                <button class="gender" onclick="femaleDirectors()">♀ Female Directors</button>
                <button class="countries">Countries</button>
            </div>
        </div>

        <!-- <div id="stats">
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

        -->
        <div id="movies">
        </div>

        <script>
            const listening = true;
            const dropArea = document.querySelector('html');
            // Prevent default behaviors for drag events
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, e => e.preventDefault());
            });

            // Don't drag the images
            document.querySelectorAll('img').forEach(img => {
                img.ondragstart = function() { return false; };
            });

            // Highlight area on dragover
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => {
                    dropArea.classList.add('hover');
                    document.querySelectorAll('.center img').forEach(img => {
                        const top = parseInt(img.getAttribute('top'));
                        const left = parseInt(img.getAttribute('left'));
                        img.style.top = (top + (top - 97) * 0.05) + 'px';
                        img.style.left = (left + (left - 253) * 0.05) + 'px';
                    });
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => {
                    dropArea.classList.remove('hover');
                    document.querySelectorAll('.center img').forEach(img => {
                        img.style.top = img.getAttribute('top') + 'px';
                        img.style.left = img.getAttribute('left') + 'px';
                    });
                });
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

            function femaleDirectors() {
                document.querySelectorAll('.movie:not(.female)').forEach(poster => {
                    poster.style.opacity = 0.2;
                });
                const numFilter = document.querySelector('#numMovies .filter');
                numFilter.innerHTML = `${document.querySelectorAll('.movie.female').length} out of `;
                numFilter.style.display = 'initial';
            }

            function transitionToAnalysis() {
                // Fade out the imgs
                // TODO: Offset them
                document.querySelectorAll('.center img').forEach(img => img.style.opacity = '0%');

                setTimeout(() => {
                    // Animate the title up to the top
                    const header = document.querySelector('.center .header');
                    header.style.top = (-header.getBoundingClientRect().top) + 'px';
                    header.style.left = '-127px';
                    // Animate gaps to the top as well
                    const subtext = document.querySelector('.center .subtext');
                    subtext.style.top = (-subtext.getBoundingClientRect().top + 20.25) + 'px';
                    subtext.style.letterSpacing = '1em';
                    subtext.style.left = '179px';

                    setTimeout(() => {
                        document.querySelector('.center-wrapper').style.display = 'none';
                        document.querySelector('.nav').style.display = 'initial';
                    }, 2000);
                }, 2000); // takes 2s for the images to fade
            }

            // File upload
            function uploadFile(file) {
                const formData = new FormData();
                formData.append('file', file);

                const container = document.getElementById('movies');
                container.innerHTML = '';

                transitionToAnalysis();

                fetch('get_watched_list.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(rawData => {
                    const data = JSON.parse(rawData);
                    movies = data.movies;

                    let numTotal = 0;
                    let numWomen = 0;
                    let countries = {};
                    let languages = {};

                    document.querySelector('.nav #numMovies .total').innerHTML = movies.length.toLocaleString('en-US');

                    function calculateBestFit(w, h, n, ratio) {
                        let bestWidth = 0;
                        let bestColumns = 0;
                        let bestRows = 0;

                        for (let c = 1; c <= n; c++) {
                            let r = Math.ceil(n / c); // Calculate rows for current column count
                            const x = Math.min(w / (c * 1), h / (r * ratio)); // Calculate the scale-independent width x
                            const y = x * ratio; // Corresponding height
                            if (c * x <= w && r * y <= h) { // Check if the configuration fits
                                if (x > bestWidth) { // Update if this scale is better
                                    bestWidth = x;
                                    bestColumns = c;
                                    bestRows = r;
                                }
                            }
                        }

                        return {
                            imageWidth: bestWidth,
                            numRows: bestRows,
                            numCols: bestColumns,
                        };
                    }

                    let centerX, centerY, radiusScale;
                    const width = window.innerWidth;
                    const height = window.innerHeight - 120.5; // nav height
                    const ratio = 138/92;

                    let { imageWidth, numRows, numCols } = calculateBestFit(width, height, movies.length, ratio);
                    imageWidth = imageWidth - 4;
                    const imageHeight = imageWidth * ratio;

                    var styleSheet = document.createElement("style");
                    styleSheet.textContent = `
                    #movies .movie {
                        width: ${imageWidth}px;
                        height: ${imageHeight}px;
                    }
                    `;
                    document.head.appendChild(styleSheet);

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

                    // And make it so the new images can't be dragged
                    document.querySelectorAll('img').forEach(img => {
                        img.ondragstart = function() { return false; };
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