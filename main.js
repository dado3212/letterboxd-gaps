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
            if (img.getAttribute('top')) {
                const top = parseInt(img.getAttribute('top'));
                img.style.top = (top + (top - 97) * 0.05) + 'px';
            } else {
                const bottom = parseInt(img.getAttribute('bottom'));
                img.style.bottom = (bottom + (bottom - 97) * 0.05) + 'px';
            }
            if (img.getAttribute('left')) {
                const left = parseInt(img.getAttribute('left'));
                img.style.left = (left + (left - 253) * 0.05) + 'px';
            } else {
                const right = parseInt(img.getAttribute('right'));
                img.style.right = (right + (right - 253) * 0.05) + 'px';
            }
        });
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, () => {
        dropArea.classList.remove('hover');
        document.querySelectorAll('.center img').forEach(img => {
            if (img.getAttribute('top')) {
                img.style.top = img.getAttribute('top') + 'px';
            } else {
                img.style.bottom = img.getAttribute('bottom') + 'px';
            }
            if (img.getAttribute('left')) {
                img.style.left = img.getAttribute('left') + 'px';
            } else {
                img.style.right = img.getAttribute('right') + 'px';
            }
        });
    });
});

// Handle file drop and/or select
const afterAttempt = (files) => {
    if (files.length !== 1) {
        alert('Please upload a valid .zip file.');
        return;
    }
    if (files[0].type === 'application/zip') {
        if (files[0].name.startsWith('letterboxd-')) {
            uploadFile(files[0]);
        } else {
            alert(`${files[0].name} is not an unmodified Letterboxd export file.`);
        }
    } else {
        alert(`${files[0].name} is not a zip file.`);
    }
}
dropArea.addEventListener('drop', event => {
    afterAttempt(event.dataTransfer.files);
});

document.querySelector('#zipInput').addEventListener('change', event => {
    afterAttempt(event.target.files);
});

function scrapePendingMovies(uploadId, cb) {
    fetch(`get_movie_info.php?id=${uploadId}`, {
        method: 'GET',
    })
    .then(response => response.text())
    .then(rawData => {
        const parsedData = JSON.parse(rawData);
        if (parsedData['status'] != 'finished') {
            if (parsedData['status'] == 'failed') {
                console.log(parsedData['error']);
            }
            scrapePendingMovies(uploadId, cb);
        } else {
            // Include all of the images and pictures, and rearrange them
            console.log('finished');
            cb();
        }
    });
}

const help = document.querySelector('#help');
help.addEventListener('click', (e) => {
    if (!document.querySelector('#help .modal').contains(e.target)) {
        help.style.display = 'none';
    }
})
function showHelp() {
    help.style.display = 'flex';
}

function hideHelp() {
    help.style.display = 'none';
}

let currentTab = 'none';
function clickButton(tab) {
    // If countries/languages is open then we need to do something.
    // No matter what we're closing the open tab.
    if (currentTab == 'countries') {
        document.querySelector('#countryInfo').style.position = 'absolute';
    } else if (currentTab == 'languages') {
        document.querySelector('#languageInfo').style.position = 'absolute';
    }
    // Reset the highlighting
    document.querySelectorAll('.movie.faded').forEach(poster => {
        poster.classList.remove('faded');
    });
    // Clear the female director info
    document.querySelector('#numMovies .filter').style.display = 'none';
    showingFemaleDirectors = false;
    // If we're swapping over to a new tab, then open that
    if (tab != currentTab) {
        if (tab == 'countries') {
            document.querySelector('#countryInfo').style.position = 'inherit';
        } else if (tab == 'languages') {
            document.querySelector('#languageInfo').style.position = 'inherit';
        }
    }
    // Update which tab we're on
    if (tab == currentTab) {
        currentTab = 'none';
    } else {
        currentTab = tab;
    }
}

let showingFemaleDirectors = false;
function femaleDirectors() {
    showingFemaleDirectors = !showingFemaleDirectors;
    if (showingFemaleDirectors) {
        // We can filter down the currently highlighted movies in case you 
        // want to, say, highlight Iranian female-directed movies. But when
        // you untoggle we will clear all highlighting.
        let numHighlighted = 0;
        document.querySelectorAll('.movie:not(.faded)').forEach(poster => {
            if (poster.getAttribute('data-female')) {
                numHighlighted += 1;
            } else {
                poster.classList.add('faded');
            }
        });
        const numFilter = document.querySelector('#numMovies .filter');
        numFilter.innerHTML = `${numHighlighted} out of `;
        numFilter.style.display = 'initial';
    } else {
        document.querySelectorAll('.movie.faded').forEach(poster => {
            poster.classList.remove('faded');
        });
        document.querySelector('#numMovies .filter').style.display = 'none';
    }
}

let isShowingCountries = false;
function countries() {
    isShowingCountries = !isShowingCountries;
    const countryInfo = document.querySelector('#countryInfo');
    if (isShowingCountries) {
        countryInfo.style.position = 'inherit';
    } else {
        countryInfo.style.position = 'absolute';
    }
}

let isShowingLanguages = false;
function languages() {
    isShowingLanguages = !isShowingLanguages;
    const languageInfo = document.querySelector('#languageInfo');
    if (isShowingLanguages) {
        languageInfo.style.position = 'inherit';
    } else {
        languageInfo.style.position = 'absolute';
    }
}

function transitionToAnalysis() {
    document.querySelector('.made').style.display = 'none';
    // Fade out the imgs
    document.querySelectorAll('.center img').forEach(img => {
        // Offset them so it's a staggered effect
        const randomOffset = Math.random() * 0.5;
        img.style.transition = `top 0.3s, left 0.3s, opacity ${2 - randomOffset}s`;
        img.style.transitionDelay = `${randomOffset}s`;
        img.style.transitionProperty = 'opacity';
        img.style.opacity = '0%';
    });
    document.querySelector('.center p').style.opacity = '0%';

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
    }, 1500); // takes 2s for the images to fade
}

let allData = [];
let diaryLists = new Set();
let allCountries = {};
let allLanguages = {};

// Use this for a one-time scroll on the countries screen just to convey
// that you will need to scroll down
let hasScrolledCountries = false;

function swapList(index) {
    const data = allData[index];

    const countryButton = document.querySelector('button.countries');
    if (diaryLists.has(index)) {
        countryButton.disabled = true;
        countryButton.title = 'All diary movies have been watched, so there are no countries you haven\'t seen movies from.';
    } else {
        countryButton.disabled = false;
        countryButton.title = '';
    }

    const languageButton = document.querySelector('button.languages');
    if (diaryLists.has(index)) {
        languageButton.disabled = true;
        languageButton.title = 'All diary movies have been watched, so there are no languages you haven\'t seen movies in.';
    } else {
        languageButton.disabled = false;
        languageButton.title = '';
    }

    movies = data.movies;

    const container = document.getElementById('movies');
    container.innerHTML = '';

    // Set up map
    // If you're viewing your watched list, this is all countries you haven't seen
    let movieCountData;
    let movieLanguageCountData;
    let watchedCountries = new Set();
    let watchedLanguages = new Set();
    if (data['name'] == 'Watched') {
        movieCountData = {...allCountries};
        movieLanguageCountData = {...allLanguages};
        movies.forEach(movie => {
            if (movie.countries) {
                movie.countries.forEach(country => {
                    if (country in movieCountData) {
                        delete movieCountData[country];
                    }
                });
            }
            if (movie.language in movieLanguageCountData) {
                delete movieLanguageCountData[movie.language];
            }
        });
    } else {
        // Get all of the countries/languages that you've already seen from the watchlist
        allData[0].movies.forEach(movie => {
            if (movie.countries) {
                movie.countries.forEach(country => {
                    watchedCountries.add(country);
                });
            }
            if (movie.language) {
                watchedLanguages.add(movie.language);
            }
        });
        // Filter your current list to countries/languages you haven't seen. 
        movieCountData = {};
        movieLanguageCountData = {};
        movies.forEach(movie => {
            if (movie.countries) {
                movie.countries.forEach(country => {
                    if (watchedCountries.has(country)) {
                        return;
                    }
                    if (country in movieCountData) {
                        movieCountData[country]['num_movies'] += 1;
                    } else {
                        movieCountData[country] = {
                            'num_movies': 1
                        };
                    }
                });
            }
            if (movie.language) {
                if (watchedLanguages.has(movie.language)) {
                    return;
                }
                if (movie.language in movieLanguageCountData) {
                    movieLanguageCountData[movie.language]['num_movies'] += 1;
                } else {
                    movieLanguageCountData[movie.language] = {
                        'num_movies': 1
                    };
                }
            }
        });
    }

    if (Object.keys(movieCountData).length == 0) {
        countryButton.disabled = true;
        countryButton.title = 'You\'ve seen movies from all countries represented by this list.';
    }

    if (Object.keys(movieLanguageCountData).length == 0) {
        languageButton.disabled = true;
        languageButton.title = 'You\'ve seen movies in all languages represented by this list.';
    }

    const svg = document.getElementById('svgMap');
    svg.innerHTML = '';
    const svgObject = new svgMap({
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

    const countryList = document.querySelector('#countryList');
    countryList.innerHTML = '';
    movieCountData = Object.fromEntries(
        Object.entries(movieCountData).sort(([, a], [, b]) => b.num_movies - a.num_movies)
    );

    if (data['name'] == 'Watched') {
        document.querySelector('#countryInfo p').innerHTML = `
        You haven't seen any movies from these countries.
        Click a country on the map or in the list on the right to go to a full 
        list of movies from that country. Add some to your watchlist!`;
    } else {
        document.querySelector('#countryInfo p').innerHTML = `
        This shows movies in this list that are from countries you've never seen anything from.
        Click a country on the map or in the list on the right to highlight
        the movies from that country.`;
    }

    let currentSelectedCountry = null;
    clickCountry = (clickedCountry) => {
        if (data['name'] == 'Watched') {
            window.open(allCountries[clickedCountry]['url'], '_blank');
            return;
        }

        if (clickedCountry == currentSelectedCountry || !(clickedCountry in movieCountData)) {
            document.querySelectorAll('.movie.faded').forEach(poster => {
                poster.classList.remove('faded');
            });
            currentSelectedCountry = null;
        } else {
            currentSelectedCountry = clickedCountry;
            document.querySelectorAll('.movie').forEach(poster => {
                const dataCountries = poster.getAttribute('data-countries');
                if (dataCountries && dataCountries.includes(clickedCountry)) {
                    poster.classList.remove('faded');
                } else {
                    poster.classList.add('faded');
                }
            });
            // Just indicate to users how this works
            if (!hasScrolledCountries) {
                document.getElementById('movies').scrollIntoView({ behavior: 'smooth' });
                hasScrolledCountries = true;
            }
        }
    };

    for (country in movieCountData) {
        countryList.innerHTML += `<li>
            <a onclick="clickCountry('${country}');">${allCountries[country]['full_name']}
                <span>${movieCountData[country].num_movies.toLocaleString()}</span>
            </a>
        </li>`;
    }

    var svgCountries = svg.querySelector('.svgMap-map-image').querySelectorAll('.svgMap-country');
    for (var i = 0; i < svgCountries.length; i++) {
        const country = svgCountries[i];

        country.addEventListener('click', function(e) {
            e.preventDefault();

            const clickedCountry = country.getAttribute('data-id');
            clickCountry(clickedCountry);
        });
    }

    // Language setup
    const languageList = document.querySelector('#languageList');
    languageList.innerHTML = '';
    movieLanguageCountData = Object.fromEntries(
        Object.entries(movieLanguageCountData).sort(([, a], [, b]) => b.num_movies - a.num_movies)
    );

    if (data['name'] == 'Watched') {
        document.querySelector('#languageInfo p').innerHTML = `
        You haven't seen any movies in these languages.
        Click a language in the list to go to a full list 
        of movies in that language. Add some to your watchlist!`;
    } else {
        document.querySelector('#languageInfo p').innerHTML = `
        This shows movies in this list that are in languages you've never seen anything in.
        Click a language in the list to highlight the movies in that language.`;
    }

    let currentlySelectedLanguage = null;
    clickLanguage = (clickedLanguage) => {
        if (data['name'] == 'Watched') {
            window.open(allLanguages[clickedLanguage]['url'], '_blank');
            return;
        }

        if (clickedLanguage == currentlySelectedLanguage || !(clickedLanguage in movieLanguageCountData)) {
            document.querySelectorAll('.movie').forEach(poster => {
                poster.classList.remove('faded');
            });
            currentlySelectedLanguage = null;
        } else {
            currentlySelectedLanguage = clickedLanguage;
            document.querySelectorAll('.movie').forEach(poster => {
                const dataLanguage = poster.getAttribute('data-language');
                if (dataLanguage == clickedLanguage) {
                    poster.classList.remove('faded');
                } else {
                    poster.classList.add('faded');
                }
            });
        }
    };

    for (language in movieLanguageCountData) {
        if (language in allLanguages) {
            languageList.innerHTML += `<li>
                <a onclick="clickLanguage('${language}');">${allLanguages[language]['full_name']}
                    <span>${movieLanguageCountData[language].num_movies.toLocaleString()}</span>
                </a>
            </li>`;
        }
    }

    // Reset UIs
    document.querySelector('#countryInfo').style.position = 'absolute';
    document.querySelector('#languageInfo').style.position = 'absolute';
    document.querySelector('#numMovies .filter').style.display = 'none';
    currentTab = 'none';
    showingFemaleDirectors = false;

    // Clear tippy
    const tippyRoot = document.querySelector('div[data-tippy-root')?.remove();

    document.querySelector('.nav #numMovies .total').innerHTML = movies.length.toLocaleString();

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

    const width = window.innerWidth;
    const height = window.innerHeight - 120.5; // nav height
    const ratio = 138/92;

    let { imageWidth, _numRows, _numCols } = calculateBestFit(width, height, movies.length, ratio);
    imageWidth = Math.max(Math.min(imageWidth, 200), 12);
    imageWidth = imageWidth - 4;
    const imageHeight = imageWidth * ratio;

    let textContent = `
        #movies .movie {
            width: ${imageWidth}px;
            height: ${imageHeight}px;
        }
        `;
    if (imageWidth == 8) { // min, adjust the gap
        textContent += `
        #movies {
          gap: 2px;
        }
        `;
    }
    let styleSheet = document.querySelector('#movieStyleSheet');
    if (styleSheet) {
        styleSheet.textContent = textContent;
    } else {
        styleSheet = document.createElement('style');
        styleSheet.id = 'movieStyleSheet';
        styleSheet.textContent = textContent;
        document.head.appendChild(styleSheet);
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
        // Set attributes for filtering later
        if (movie.has_female_director) {
            movieDiv.setAttribute('data-female', '1');
        }
        if (movie.language) {
            movieDiv.setAttribute('data-language', movie.language);
        }
        if (movie.countries) {
            movieDiv.setAttribute('data-countries', movie.countries.join(','));
        }
        container.appendChild(movieDiv);
        // Add in the tooltip if the image is too small
        if (imageWidth < 70) {
            let hoverHTML = `<div class="name">${movie.movie_name} `
            if (movie.has_female_director) {
                hoverHTML += `<span>(${movie.year})<img class="director" src="assets/female_director.svg"/></span></div>`;
            } else {
                hoverHTML += `(${movie.year})</div>`;
            }
            if (movie.countries) {
                hoverHTML += '<div class="countries">';
                const fontSize = Math.min(25, 100 / movie.countries.length);
                for (country of movie.countries) {
                    if (country in svgObject.emojiFlags) {
                        hoverHTML += `<span title="${allCountries[country]['full_name']}" style="font-size: ${fontSize}px;">${svgObject.emojiFlags[country]}</span>`;
                    }
                }
                hoverHTML += '</div>';
            }
            if (movie.language && movie.language in allLanguages) {
                hoverHTML += `<div class="language">${allLanguages[movie.language]['full_name']}</div>`;
            }
            hoverHTML += `${movieDiv.innerHTML}`;
            
            tippy(movieDiv, {
                animation: 'scale',
                content: hoverHTML,
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

let bodyClickListener = null;
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

        allCountries = data.countries;
        allLanguages = data.languages;
        const uploadId = data.upload_id;
        const shouldUpload = data.should_upload;
        data = data.movies;
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
        diaryLists = new Set();

        // Two, the dropdown menu that unfolds
        for (var i = 0; i < data.length; i++) {
            if (data[i].type == 'group') {
                innerHTML += `
                <div class="group"><span class="name">${data[i].name}</span>
                    <div class="sublist">`;
                for (var j = 0; j < data[i].sublists.length; j++) {
                    innerHTML += `<div data-list="${dataIndex}"><span class="name">${data[i].sublists[j].name}</span><span class="number">${data[i].sublists[j].movies.length}</span></div>`;
                    allData.push(data[i].sublists[j]);
                    if (data[i].name == 'Diary') {
                        diaryLists.add(dataIndex);
                    }
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
                swapList(Number(list.dataset.list));
            };
        });
        document.querySelectorAll('.dropdown .group').forEach(group => {
            group.onclick = () => {
                group.classList.toggle('opened');
            };
        });
        if (bodyClickListener != null) {
            document.body.removeEventListener('click', bodyClickListener);
        }
        bodyClickListener = (e) => {
            if (listSelect.classList.contains('opened') && !listSelect.contains(e.target)) {
                listSelect.classList.remove('opened');
            }
        };
        document.body.addEventListener('click', bodyClickListener);
        swapList(0);

        const progressBar = document.querySelector('.nav .progress');
        progressBar.style.height = '0%';

        if (uploadId) {
            if (shouldUpload) {
                scrapePendingMovies(uploadId, () => {
                    tryToUpload(formData);
                });
            }
            // 30% to 81%
            progressBar.style.height = '30%';
            const header = document.querySelector('.nav .header');

            const interval = setInterval(() => {
                fetch('poll_status.php?id=' + uploadId)
                .then(response => response.json())
                .then(data => {
                    progressBar.style.height = `${30 + (81 - 30) * (data.done / data.total)}%`;
                    header.title = `Processed ${data.done.toLocaleString()} out of ${data.total.toLocaleString()} movies.`;
                    if (data.done == data.total) {
                        progressBar.style.height = '0%'; // once it's done, reset it
                        clearTimeout(interval);
                    }
                });
            }, 2000);
        }
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
    document.querySelector('#numMovies .filter').style.display = 'none';
    showingFemaleDirectors = false;

    transitionToAnalysis();

    tryToUpload(formData);
}