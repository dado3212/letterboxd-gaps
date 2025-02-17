# <img src="/assets/favicon/favicon-96x96.png?raw=true" height="32px" alt=""/> Letterbox Gaps

Expand your film horizons by analyzing your Letterboxd data! Discover countries and languages you're missing, all while highlighting films by female directors.

<img width="1522" alt="Screenshot 2025-02-17 at 1 51 11 PM" src="https://github.com/user-attachments/assets/c287aa1c-4c74-48c9-a2de-81f45c8ea7f4" />
<img width="33%" alt="Screenshot 2025-02-17 at 1 51 30 PM" src="https://github.com/user-attachments/assets/a052840c-e5fc-42b3-945a-ab2545ea4011" />
<img width="33%" alt="Screenshot 2025-02-17 at 1 51 39 PM" src="https://github.com/user-attachments/assets/f2be0fca-80b8-429e-b015-33b3a68832d7" />
<img width="33%" alt="Screenshot 2025-02-17 at 1 51 52 PM" src="https://github.com/user-attachments/assets/0c50c44a-ba02-41f0-bc91-a097c9c7cbb0" />

Drag and drop your Letterboxd output `.zip` file and explore:
- Watched list
  - See which countries you haven't seen any movies from (click to explore the full list on Letterboxd.com!)
  - See which languages you haven't watched any movies in (ditto!)
  - See what percentage of your watched movies are from female directors
- Watchlist/diary/custom lists:
  - See which movies in the list are from countries you haven't seen movies from
  - See whcih movies in the lsit are in languages you haven't seen movies in
  - See which movies in the list are from female directors

## Installation
Create `letterboxd` as a DB and the four sub-tables using `CREATE_DBS.sql`. Then create a `secret.php` file with the following info:
```
<?php
	define('TMDB_API_KEY', '<key>');
	define('PROCESS_KEY', '<key2>');
	define('SCRAPE_COUNTRIES_KEY', '<key3>');

	// Gets the database
	function getDatabase() {
		try {
			$PDO = new PDO("mysql:host=localhost;dbname=letterboxd;charset=latin1","<username>","<password>");
			$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		} catch (PDOException $e) {
			echo "PDO MySQL Failed to connect: " . $e->getMessage();
		}

		return $PDO;
	}
?>
```
Set up a crontab to scrape countries/language counts daily:
```
00 15 * * * php /var/www/alexbeals.com/public_html/projects/letterboxd/scrape_countries.php <key3>
```

## Home Page UI

The Home page gallery wall was created manually (hence the jank) using this code:
```
const imgs = document.querySelectorAll('.center img');
let offsetX = 0, offsetY = 0, draggingImg = null;
imgs.forEach(img => {

    img.addEventListener('mousedown', (e) => {
        e.preventDefault();
        draggingImg = img;
        const style = img.style;
        positionType = {
            top: style.top !== '',
            left: style.left !== '',
            bottom: style.bottom !== '',
            right: style.right !== ''
        };
        console.log(positionType);

        if (positionType.top) offsetY = e.clientY - img.offsetTop;
        if (positionType.left) offsetX = e.clientX - img.offsetLeft;

        if (positionType.bottom) offsetY = img.parentElement.clientHeight - (e.clientY + img.offsetHeight + parseFloat(style.bottom.slice(0, -2)));
        if (positionType.right) offsetX = img.parentElement.clientWidth - (e.clientX + img.offsetWidth + parseFloat(style.right.slice(0, -2)));

        img.style.cursor = 'grabbing';
    });

    
});

document.addEventListener('mousemove', (e) => {
  if (!draggingImg) return;
  const parent = draggingImg.parentElement;

  if (positionType.top) draggingImg.style.top = `${e.clientY - offsetY}px`;
  if (positionType.left) draggingImg.style.left = `${e.clientX - offsetX}px`;

  if (positionType.bottom) draggingImg.style.bottom = `${parent.clientHeight - (e.clientY + draggingImg.offsetHeight + offsetY)}px`;
  if (positionType.right) draggingImg.style.right = `${parent.clientWidth - (e.clientX + draggingImg.offsetWidth + offsetX)}px`;
});

document.addEventListener('mouseup', () => {
if (draggingImg) {
    draggingImg.style.cursor = 'grab';
    draggingImg = null;
}
});
document.addEventListener('keydown', (e) => {
    if (!draggingImg) return;
    // const step = 10; // Change in size for each keypress
    if (e.key.toLowerCase() === 'w') {
        // Increase size
        // currentWidth += step;
        draggingImg.style.width = `${draggingImg.width + 10}px`;
    } else if (e.key.toLowerCase() === 's') {
        // Decrease size
        // currentWidth = Math.max(step, currentWidth - step); // Prevent size from going below 10px
        draggingImg.style.width = `${draggingImg.width - 10}px`;
    }
});
```

Once done moving around/reisizing the images you can extract the array to write into `index.php` with this script:
```
let total = [];
const centerRect = document.querySelector('.center').getBoundingClientRect();
document.querySelectorAll('.center img').forEach(img => {
  const imgRect = img.getBoundingClientRect();
  let data = {i: parseInt(img.getAttribute('data-tmdb')), w: img.width};
  if (imgRect.y > centerRect.y + centerRect.height / 2) {
    data['b'] = Math.floor(centerRect.bottom - imgRect.bottom);
  } else {
    data['t'] = Math.floor(imgRect.top - centerRect.top);
  }
  if (imgRect.x > centerRect.x + centerRect.width / 2) {
    data['r'] = Math.floor(centerRect.right - imgRect.right);
  } else {
    data['l'] = Math.floor(imgRect.left - centerRect.left);
  }
  total.push(data);
});
JSON.stringify(total);
```

## Weird Letterboxd Links

For if you're trying to scrape Letterboxd here are some sample links

Normal:
* 'https://boxd.it/iEEq', 'Free Solo', '2021'
* 'https://boxd.it/aPvo', 'Frozen', '2021'

Different:
* 'https://boxd.it/2o4Y', 'The Vow', '2012'  // different format of photo
* 'https://boxd.it/s1Ym', 'The Queen\'s Gambit', '2020' // TV show
* 'https://boxd.it/yK2u', 'A Sensorial Ride', '2020' // no picture
* 'https://boxd.it/AP3G', 'Emilia Pérez', '2024' // accent in title
* 'https://letterboxd.com/film/sherlock-the-sign-of-three/', 'Sherlock', '2024' // has since been removed form TMDb

## TODO

Low pri cleanup
- Clean up Thanks page
- Properly separate out CSS and JS into separate files
- Remove unnecessary SVG map styling rules

Stretch
- Better animation for gender splits where the posters rearrange
- Ditto for country selection
- Live push images as they come?
- Fix jittering around animating top/left
- Consolidate polling and get_movie_info
- Handle some sort of "watched" affordance for breaking down lists
- Color sorting that matches perception (need to read some more papers around this)
