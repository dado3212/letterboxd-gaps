
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Letterboxd Gaps</title>
        <link rel="stylesheet" type="text/css" href="main.css">
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
                    <div class="progress">
                        <div class="wrapper">
                            <span class="orange">Let</span><span class="green">ter</span><span class="blue">boxd</span>
                        </div>
                    </div>
                </div>
                <div class="subtext">GAPS</div>
            </div>
            <div class="menu">
                <div id="list-select"></div>
                <div id="numMovies"><span class='filter'>0 out of </span><span class='total'>0</span> movies</div>
                <button class="gender" onclick="femaleDirectors()">Female Directors</button>
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
        <div id="svgMap"></div>
        <link href="https://cdn.jsdelivr.net/gh/StephanWagner/svgMap@v2.10.1/dist/svgMap.min.css" rel="stylesheet">
        <script src="./svgMap.min.js"></script>
        <style>
            #svgMap {
                width: 950px;
                margin: 0 auto;
            }
            .svgMap-map-wrapper {
                background: #14181c;
                border: 1px solid #303840;
                border-radius: 4px;
                color: #9ab;
            }
            .svgMap-map-wrapper .svgMap-country {
                cursor: pointer;
                stroke: rgba(0, 0, 0, .25);
                stroke-width: 1.5;
                stroke-linejoin: round;
                vector-effect: non-scaling-stroke;
                transition: fill .2s, stroke .2s;
            }
            .svgMap-map-wrapper .svgMap-country:hover {
                stroke: rgba(0, 0, 0, .25);
                fill: #40bcf4;
            }
            .svgMap-tooltip {
                background: #456;
                border-radius: 4px;
                border-bottom: none;
                box-shadow: 0 2px 10px rgba(0, 0, 0, .2);
                color: #9ab;
            }
            .svgMap-tooltip .svgMap-tooltip-content-container {
                padding: 8px 12px;
                position: relative;
            }
            .svgMap-tooltip .svgMap-tooltip-title {
                color: #cde;
                font-family: Graphik-Semibold-Web, sans-serif;
                font-size: 14px;
                font-weight: 400;
                line-height: 1;
                padding: 2px 0;
                text-align: center;
                white-space: nowrap;
            }
            .svgMap-tooltip .svgMap-tooltip-content {
                color: #9ab;
                font-size: 12px;
                line-height: 1;
                margin: 0;
                text-align: center;
                white-space: nowrap;
            }
            .svgMap-tooltip .svgMap-tooltip-content .svgMap-tooltip-no-data {
                color: #9ab;
                padding: 2px 0;
            }
            .svgMap-tooltip .svgMap-tooltip-content table td span {
                color: #9ab;
            }
            .svgMap-tooltip .svgMap-tooltip-pointer:after {
                background: #456;
                border: none;
                border-radius: 3px;
                bottom: 6px;
                content: "";
                height: 20px;
                left: 50%;
                position: absolute;
                transform: translateX(-50%) rotate(45deg);
                width: 20px;
            }
            
            .svgMap-map-controls-wrapper {
                border-radius: 2px;
                bottom: 10px;
                box-shadow: 0 0 0 2px rgba(0,0,0,.1);
                display: flex;
                left: 10px;
                overflow: hidden;
                position: absolute;
                z-index: 1
            }

            .svgMap-map-wrapper .svgMap-map-controls-move, .svgMap-map-wrapper .svgMap-map-controls-zoom {
                background: #2c3440;
                display: flex;
                margin-right: 5px;
                overflow: hidden
            }

            .svgMap-map-controls-move:last-child,.svgMap-map-controls-zoom:last-child {
                margin-right: 0
            }

            .svgMap-control-button {
                cursor: pointer;
                height: 30px;
                position: relative;
                width: 30px
            }

            .svgMap-control-button.svgMap-zoom-button:after,.svgMap-control-button.svgMap-zoom-button:before {
                background: #9ab;
                content: "";
                left: 50%;
                position: absolute;
                top: 50%;
                transform: translate(-50%,-50%);
                transition: background-color .2s
            }

            .svgMap-control-button.svgMap-zoom-button:before {
                height: 3px;
                width: 11px
            }

            .svgMap-control-button.svgMap-zoom-button:hover:after,.svgMap-control-button.svgMap-zoom-button:hover:before {
                background: #fff
            }

            .svgMap-control-button.svgMap-zoom-button.svgMap-disabled:after,.svgMap-control-button.svgMap-zoom-button.svgMap-disabled:before {
                background: #ccc
            }

            .svgMap-control-button.svgMap-zoom-in-button:after {
                height: 11px;
                width: 3px
            }
        </style>

        <div id="movies">
        </div>

        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>
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

            function pingHome(cb) {
                fetch('get_movie_info.php', {
                    method: 'GET',
                })
                .then(response => response.text())
                .then(rawData => {
                    if (JSON.parse(rawData)['status'] != 'finished') {
                        pingHome(cb);
                    } else {
                        // Include all of the images and pictures, and rearrange them
                        console.log('finished');
                        cb();
                    }
                });
            }

            let showingFemaleDirectors = false;
            function femaleDirectors() {
                if (!showingFemaleDirectors) {
                    // Highlight them
                    document.querySelectorAll('.movie:not([data-female])').forEach(poster => {
                        poster.style.opacity = 0.2;
                    });
                    const numFilter = document.querySelector('#numMovies .filter');
                    numFilter.innerHTML = `${document.querySelectorAll('.movie[data-female]').length} out of `;
                    numFilter.style.display = 'initial';
                } else {
                    // Remove highlighting
                    document.querySelectorAll('.movie:not([data-female])').forEach(poster => {
                        poster.style.opacity = 1.0;
                    });
                    const numFilter = document.querySelector('#numMovies .filter');
                    numFilter.style.display = 'none';
                }
                showingFemaleDirectors = !showingFemaleDirectors;
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
                    }, 0);
                }, 0); // takes 2s for the images to fade
            }

            let allData = [];
            let allCountries = {};

            function swapList(data) {
                console.log(data);
                movies = data.movies;

                const container = document.getElementById('movies');
                container.innerHTML = '';

                // Set up map
                // If you're viewing your watched list, this is all countries you haven't seen
                let movieCountData;
                let watchedCountries = new Set();
                if (data['name'] == 'Watched') {
                    movieCountData = {...allCountries};
                    movies.forEach(movie => {
                        if (movie.countries) {
                            movie.countries.forEach(country => {
                                if (country in movieCountData) {
                                    delete movieCountData[country];
                                }
                            });
                        }
                    });
                } else {
                    // Get all of the countries that you've already seen from the watchlist
                    allData[0].movies.forEach(movie => {
                        if (movie.countries) {
                            movie.countries.forEach(country => {
                                watchedCountries.add(country);
                            });
                        }
                    });
                    // Filter your current list to countries you haven't seen. 
                    movieCountData = {};
                    movies.forEach(movie => {
                        if (movie.countries) {
                            movie.countries.forEach(country => {
                                if (watchedCountries.has(country)) {
                                    return;
                                }
                                if (country in movieCountData) {
                                    movieCountData[country]['num_movies'] += 1;
                                } else {
                                    movieCountData[country]= {
                                        'num_movies': 1
                                    };
                                }
                            });
                        }
                    });
                }

                const svg = document.getElementById('svgMap');
                svg.innerHTML = '';
                new svgMap({
                    targetElementID: 'svgMap',
                    colorMin: '#007733',
                    colorMax: '#00E054',
                    colorNoData: '#303C44',
                    hideFlag: true,
                    initialZoom: 1,
                    minZoom: 1,
                    noDataText: (country) => {
                        if (data['name'] == 'Watched') {
                            return 'Already seen';
                        } else {
                            if (watchedCountries.has(country)) {
                                return 'Already seen';
                            } else {
                                return 'No movies in list';
                            }
                        }
                    },
                    data: {
                        data: {
                            num_movies: {
                                name: 'Films:',
                                format: '{0}',
                                thousandSeparator: ','
                            }
                        },
                        applyData: 'num_movies',
                        values: movieCountData,
                    },
                });

                var svgCountries = svg.querySelector('.svgMap-map-image').querySelectorAll('.svgMap-country');
                let currentSelectedCountry = null;
                for (var i = 0; i < svgCountries.length; i++) {
                    const country = svgCountries[i];

                    country.addEventListener('click', function(e) {
                        e.preventDefault();

                        const clickedCountry = country.getAttribute('data-id');
                        if (data['name'] == 'Watched') {
                            window.open(allCountries[clickedCountry]['url'], '_blank');
                            return;
                        }
                        if (!(clickedCountry in movieCountData)) {
                            return;
                        }

                        if (clickedCountry == currentSelectedCountry) {
                            document.querySelectorAll('.movie').forEach(poster => {
                                poster.style.opacity = 1.0;
                            });
                            clickedCountry = null;
                        } else {
                            currentSelectedCountry = clickedCountry;
                            document.querySelectorAll('.movie').forEach(poster => {
                                const dataCountries = poster.getAttribute('data-countries');
                                if (dataCountries && dataCountries.includes(clickedCountry)) {
                                    poster.style.opacity = 1.0;
                                } else {
                                    poster.style.opacity = 0.2;
                                }
                            });
                        }
                    });
                }

                // Clear gender selector
                const numFilter = document.querySelector('#numMovies .filter');
                numFilter.style.display = 'none';
                showingFemaleDirectors = false;

                // Clear tippy
                const tippyRoot = document.querySelector('div[data-tippy-root')?.remove();

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
                imageWidth = Math.min(imageWidth, 200);
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
                    pingHome(() => {
                        tryToUpload(formData);
                    });
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
                    const movieName = `${movie.movie_name} (${movie.year})`;
                    const movieDiv = document.createElement('div');
                    movieDiv.className = 'movie';
                    movieDiv.onclick = () => {
                        window.open(movie.letterboxd_url, '_blank');
                    };
                    if (movie.poster) {
                        if (movie.poster.startsWith('/')) {
                            movieDiv.innerHTML = `
                                <img src="https://image.tmdb.org/t/p/w92${movie.poster}" alt="${movieName}">
                            `;
                        } else {
                            movieDiv.innerHTML = `
                                <img src="${movie.poster}" alt="${movieName}">
                            `;
                        }
                    } else {
                        movieDiv.className += ' missing';
                        movieDiv.innerHTML = `
                            <div style="font-size: ${3 * imageWidth / movieName.length}px">
                                <span>${movieName}</span>
                            </div>
                        `;
                    }
                    // shorthand for "we have the tmdb info"
                    if (movie.tmdb_id) {
                        if (movie.has_female_director) {
                            numWomen += 1;
                            movieDiv.setAttribute('data-female', '1');
                        }
                        numTotal += 1;
                        if (movie.language) {
                            movieDiv.setAttribute('data-language', movie.language);
                            if (!(movie.language in languages)) {
                                languages[movie.language] = 0;
                            }
                            languages[movie.language] += 1;
                        }
                        if (movie.countries) {
                            movieDiv.setAttribute('data-countries', movie.countries.join(','));
                            for (const country of movie.countries) {
                                if (!(country in countries)) {
                                    countries[country] = 0;
                                }
                                countries[country] += 1;
                            }
                        }
                    }
                    container.appendChild(movieDiv);
                    // Add in the tooltip if the image is too small
                    if (imageWidth < 70) {
                        tippy(movieDiv, {
                            animation: 'scale',
                            content: `<b>${movie.movie_name} (${movie.year})</b><br>${movieDiv.innerHTML}`,
                            allowHTML: true,
                            followCursor: true,
                            duration: 0,
                            maxWidth: 170, // image width + 20 for borders
                        });
                    }
                });

                // And make it so the new images can't be dragged
                document.querySelectorAll('img').forEach(img => {
                    img.ondragstart = function() { return false; };
                });
            }

            function tryToUpload(formData) {
                const container = document.getElementById('movies');
                container.innerHTML = '';

                // Clear tippy
                const tippyRoot = document.querySelector('div[data-tippy-root')?.remove();

                // Set up the list selection (hide it by default until we know how many we're dealing with)
                const listSelect = document.getElementById('list-select');
                listSelect.innerHTML = '';
                listSelect.style.display = 'none';
                
                fetch('get_watched_list.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(rawData => {
                    let data = JSON.parse(rawData);
                    // This is a .zip with multiple movies
                    allCountries = data.countries;
                    data = data.movies;
                    console.log(allCountries);
                    console.log(data);
                    // Set up the list selector, which is composed of two pieces
                    const listSelect = document.getElementById('list-select');
                    listSelect.innerHTML = '';
                    // One, the "currently selected view"
                    listSelect.style.display = 'block';
                    let innerHTML = `
                        <div class="selected">
                            <span class="name">${data[0].name}</span><span class="number">${data[0].movies.length}</span></div>
                        </div>
                        <div class="dropdown">
                    `;
                    let dataIndex = 0;
                    allData = [];
                    // Two, the dropdown menu that unfolds
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].type == 'group') {
                            innerHTML += `
                            <div class="group"><span class="name">${data[i].name}</span>
                                <div class="sublist">`;
                            for (var j = 0; j < data[i].sublists.length; j++) {
                                innerHTML += `<div data-list="${dataIndex}"><span class="name">${data[i].sublists[j].name}</span><span class="number">${data[i].sublists[j].movies.length}</span></div>`;
                                allData.push(data[i].sublists[j]);
                                dataIndex += 1;
                            }
                            innerHTML += '</div></div>';
                        } else {
                            innerHTML += `<div data-list="${dataIndex}"><span class="name">${data[i].name}</span><span class="number">${data[i].movies.length}</span></div>`;
                            allData.push(data[i]);
                            dataIndex += 1;
                        }
                    }
                    listSelect.innerHTML = innerHTML + '</div>';
                    const selected = document.querySelector('#list-select .selected');

                    selected.onclick = () => {
                        listSelect.classList.toggle('opened');
                    };
                    document.querySelectorAll('.dropdown div:not(.group):not(.sublist)').forEach(list => {
                        list.onclick = () => {
                            listSelect.classList.remove('opened');
                            selected.innerHTML = list.innerHTML;
                            swapList(allData[Number(list.dataset.list)]);
                        };
                    });
                    document.querySelectorAll('.dropdown .group').forEach(group => {
                        group.onclick = () => {
                            group.classList.toggle('opened');
                        };
                    });
                    swapList(data[0]);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            // File upload
            function uploadFile(file) {
                const formData = new FormData();
                formData.append('file', file);

                // Make sure that we hide the gender breakdown
                const numFilter = document.querySelector('#numMovies .filter');
                numFilter.style.display = 'none';
                showingFemaleDirectors = false;

                transitionToAnalysis();

                tryToUpload(formData);
            }

            transitionToAnalysis();
        </script>
    </body>
</html>