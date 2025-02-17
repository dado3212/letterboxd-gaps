<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Letterboxd Gaps</title>

        <link rel="icon" type="image/png" href="/projects/letterboxd/assets/favicon/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="/projects/letterboxd/assets/favicon/favicon.svg" />
        <link rel="shortcut icon" href="/projects/letterboxd/assets/favicon/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="/projects/letterboxd/assets/favicon/apple-touch-icon.png" />
        <link rel="manifest" href="/projects/letterboxd/assets/favicon/site.webmanifest" />

        <meta property="og:title" content="Letterboxd Gaps">
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://alexbeals.com/projects/letterboxd/">
        <meta property="og:site_name" content="Letterboxd Gaps">
        <meta property="og:image" content="/projects/letterboxd/assets/og.png">
        <meta property="og:description" content="Expand your film horizons by analyzing your Letterboxd data! Discover countries and languages you're missing, all while highlighting films by female directors.">

        <link href="https://cdn.jsdelivr.net/gh/StephanWagner/svgMap@v2.10.1/dist/svgMap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="main.css">
    </head>
    <body>
        <!-- The main center -->
        <div class="center-wrapper">
            <div class="center">
                <div class="title">
                    <div class="header">
                        <div class="normal">Letterboxd</div>
                    </div>
                    <div class="subtext">GAPS</div>
                </div>
                <p>
                    Expand your film horizons by analyzing your Letterboxd data! Discover countries and languages you're missing, all while highlighting films by female directors.<br><br>
                    <?php if (preg_match("/(iPhone|iPod|iPad|Android|BlackBerry|Mobile)/i", $_SERVER['HTTP_USER_AGENT'])) { ?>
                    This currently doesn't support mobile devices.<br>Please visit this website from a desktop computer.
                    <?php } else { ?>
                    To use go to <a href="https://letterboxd.com/settings/data/" target="_blank">https://letterboxd.com/settings/data/</a> and click "Export&nbsp;Your&nbsp;Data".<br>
                    Then <label for="zipInput">drag and drop</label> the .zip file in this window. 
                    <input type="file" name="zipInput" id="zipInput" />
                    <?php } ?>
                </p>
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
                        73723 => ['orange', 'The lorax'],
                        194 => ['red', 'Amelie'],
                        110 => ['red', 'Red'],
                        // 994108 => ['orange?', 'All of us strangers'],
                        693134 => ['orange', 'DUne 2'],
                        // 290098 => ['orange-yellow', 'The Handmaiden'],
                        3086 => ['yellow', 'The Lady Eve'],
                        814340 => ['yellow', 'Cha Cha Real Smooth'],
                        // 212778 => ['yellow', 'Chef'],
                        89 => ['yellow', 'indiana jones and the last crusade'],
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
                    var positions = [{"i":252171,"w":100,"t":-247,"l":121},{"i":843,"w":90,"t":-243,"r":131},{"i":284,"w":60,"t":-74,"l":142},{"i":194,"w":50,"t":-235,"l":288},{"i":9806,"w":100,"t":-136,"l":263},{"i":426,"w":90,"t":-99,"l":5},{"i":1049638,"w":60,"t":-211,"l":16},{"i":38757,"w":80,"t":-53,"l":-242},{"i":693134,"w":100,"t":51,"l":-77},{"i":73723,"w":120,"t":-142,"l":-141},{"i":89,"w":90,"t":30,"l":-346},{"i":10315,"w":90,"b":-120,"l":-89},{"i":866398,"w":110,"t":86,"l":-225},{"i":3086,"w":70,"b":-48,"l":26},{"i":814340,"w":110,"t":188,"l":-352},{"i":773,"w":80,"b":41,"l":-75},{"i":389,"w":100,"b":-55,"l":-215},{"i":86838,"w":100,"b":-219,"l":24},{"i":91854,"w":60,"b":-213,"l":168},{"i":85350,"w":60,"b":-70,"l":285},{"i":60308,"w":90,"b":-109,"l":151},{"i":965150,"w":80,"b":-92,"r":150},{"i":995771,"w":50,"b":-192,"r":172},{"i":1386881,"w":90,"b":-228,"l":269},{"i":149870,"w":70,"b":-44,"r":22},{"i":394117,"w":90,"b":33,"r":-87},{"i":12,"w":100,"b":-216,"r":22},{"i":398818,"w":100,"b":-142,"r":-102},{"i":328387,"w":80,"t":80,"r":-341},{"i":372058,"w":90,"b":-37,"r":-217},{"i":10681,"w":80,"b":40,"r":-331},{"i":313369,"w":130,"t":70,"r":-238},{"i":20139,"w":100,"t":47,"r":-76},{"i":424781,"w":90,"t":-78,"r":-259},{"i":121986,"w":120,"t":-155,"r":-143},{"i":354275,"w":90,"t":-100,"r":-1},{"i":152601,"w":60,"t":-215,"r":20},{"i":110,"w":70,"t":-87,"r":137}];
                    for (const position of positions) {
                        const item = document.querySelector(`.center img[data-tmdb="${position.i}"]`);
                        item.style.width = `${position.w}px`;
                        if ('t' in position) {
                            item.style.top = `${position.t}px`;
                            item.setAttribute('top', position.t);
                        }
                        if ('b' in position) {
                            item.style.bottom = `${position.b}px`;
                            item.setAttribute('bottom', position.b);
                        }
                        if ('l' in position) {
                            item.style.left = `${position.l}px`;
                            item.setAttribute('left', position.l);
                        }
                        if ('r' in position) {
                            item.style.right = `${position.r}px`;
                            item.setAttribute('right', position.r);
                        }
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
                <button class="countries" onclick="clickButton('countries')">Countries</button>
                <button class="languages" onclick="clickButton('languages')">Languages</button>
                <button class="help" onclick="showHelp()">?</button>
            </div>
        </div>
        <div class="made">
           Made by Alex Beals | <a href="./thanks">Thanks</a> | <a href="https://github.com/dado3212/letterboxd-gaps" target="_blank">Github</a> | <a href="https://letterboxd.com/dado3212/list/letterboxd-gaps/">Posters</a>
        </div>

        <div id="help">
            <div class="modal">
                <button class="help" onclick="hideHelp()">X</button>
                <div class="text">
                    <h2>FAQ</h2>
                    <p>I have only tested this on my own devices, so there are probably a lot of bugs. Check to make sure they're not already covered in this FAQ section, and then <a href="https://github.com/dado3212/letterboxd-gaps/issues/new?template=bug_report.md" target="_blank">file a GitHub issue.</a></p>
                    <p><b>Q: Why doesn't Letterboxd Gaps also show which countries or languages I <i>have</i> seen movies for?</b><br>
                        A: While Letterboxd Gaps doesn't use the formal Letterboxd API it implicitly uses it through scraping. They (rightfully) deny
                        access for "any usage that recreates current or planned features of our paid subscription tiers". Instead if you want
                        this functionality you can get a <a class="pro" href="https://letterboxd.com/pro/" target="_blank">Pro</a> or <a class="patron" href="https://letterboxd.com/pro/" target="_blank">Patron</a> subscription.
                    </p>
                    <p><b>Q: Why doesn't the size of my list match Letterboxd's?</b><br>
                        A: To avoid messing up the list appearance the tool automatically removes duplicate movies from the list. This can
                        lower the count, especially when looking at diary lists.
                    </p>
                    <p><b>Q: Why don't you include breakdowns of Black directors or AAPI directors or other underrecognized groups?</b><br>
                        A: All data is pulled from TMDb, which only has gender. If they add race or ethnicity to their API I would love to add it here.
                    </p>
                    <p><b>Q: How do I find more films to watch from female directors?</b><br>
                        A: Here's some lists:
                        <ul>
                            <li><a href="https://letterboxd.com/jack/list/women-directors-the-official-top-250-narrative/" target="_blank">Women Directors: The Official Top 250 Narrative Feature Films</a></li>
                            <li><a href="https://letterboxd.com/thaizy/list/directed-by-women/" target="_blank">Directed by Women</a></li>
                            <li><a href="https://letterboxd.com/michaelhaneke/list/films-directed-by-women/" target="_blank">Films Directed by Women</a></li>
                        </ul>
                    </p>
                </div>
            </div>
        </div>

        <div id="languageInfo">
            <h2>Missing Languages</h2>
            <p></p>
            <ol id="languageList"></ol>
         </div>

        <div id="countryInfo">
            <h2>Missing Countries</h2>
            <p></p>
            <div id="svgMap"></div>
            <ol id="countryList"></ol>
         </div>

        <script src="./svgMap.min.js"></script>
        <div id="movies">
        </div>

        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>
        <script src="./main.js"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-M15SP790QM');
        </script>
    </body>
</html>