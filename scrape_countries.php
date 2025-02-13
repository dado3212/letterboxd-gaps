<?php
require_once("secret.php");
require_once("tmdb.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

//
$PDO = getDatabase();
$stmt = $PDO->prepare("SELECT tmdb_id FROM movies WHERE status=\"pending\" LIMIT 500");
$stmt->execute();
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
$updates = [];
$ids = [];
foreach ($movies as $movie) {
  if ($movie['tmdb_id'] === 0) {
    continue;
  }
  $additionalURL = "https://api.themoviedb.org/3/movie/".$movie['tmdb_id']."?append_to_response=credits&api_key=".TMDB_API_KEY;
  if (($data = @file_get_contents($additionalURL)) === false) {
    $additionalURL = "https://api.themoviedb.org/3/tv/".$movie['tmdb_id']."?append_to_response=credits&api_key=".TMDB_API_KEY;
    if (($data = @file_get_contents($additionalURL)) === false) {
      echo "Skip - " . $movie['tmdb_id'] . "<br>";
      continue;
    }
  }
  $additionalData = json_decode($data, true);
  $countries = 
    json_encode(array_values(
      array_filter(
        array_unique(
          array_map(
            function($company) { return $company['iso_3166_1']; },
            $additionalData['production_countries']
          )
        ),
        function ($country) { return $country !== ''; }
      )
    )
    );
  $ids[] = $movie['tmdb_id'];
  $updates[] = [$movie['tmdb_id'], $countries];
}
$sql = "
  UPDATE movies
  SET status = 'done',
  countries = CASE tmdb_id
      " . implode("\n", array_map(fn($u) => "WHEN :id_{$u[0]}_1 THEN :countries_{$u[0]}", $updates)) . "
    END
  WHERE tmdb_id IN (" . implode(', ', array_map(fn($id) => ":id_{$id}_2", $ids)) . ")";

  // var_export($ids);
  // var_export($updates);
  // echo $sql;

  // Prepare and bind values
$stmt = $PDO->prepare($sql);

foreach ($updates as $u) {
  $stmt->bindValue(":countries_{$u[0]}", $u[1], PDO::PARAM_STR);
}
for ($i = 1; $i <= 2; $i++) {
  foreach ($ids as $id) {
    $stmt->bindValue(":id_{$id}_{$i}", (int)$id, PDO::PARAM_INT);
  }
}

$stmt->execute();


return;

// TODO: Properly set up a cron job to run this sporadically
if (true) {
  header('Content-Type: application/json');
  echo json_encode([]);
}

// From TMDB -> Letterboxd
$mapping = [
  'countries' => [
    'Burma' => 'Myanmar',
    'Cocos  Islands' => 'Cocos (Keeling) Islands',
    'Cote D\'Ivoire' => 'Ivory Coast',
    'Czech Republic' => 'Czechia',
    'Micronesia' => 'Federated States of Micronesia',
    'Faeroe Islands' => 'Faroe Islands',
    'United Kingdom' => 'UK',
    'Guadaloupe' => 'Guadeloupe',
    'Heard and McDonald Islands' => 'Heard Island and McDonald Islands',
    'Kyrgyz Republic' => 'Kyrgyzstan',
    'St. Kitts and Nevis' => 'Saint Kitts and Nevis',
    'St. Lucia' => 'Saint Lucia',
    'Libyan Arab Jamahiriya' => 'Libya',
    'Moldova' => 'Republic of Moldova',
    'Macedonia' => 'North Macedonia',
    'St. Pierre and Miquelon' => 'Saint Pierre and Miquelon',
    'Pitcairn Island' => 'Pitcairn',
    'Palestinian Territory' => 'State of Palestine',
    'Reunion' => 'Réunion',
    'St. Helena' => 'Saint Helena, Ascension and Tristan da Cunha',
    'Svalbard & Jan Mayen Islands' => 'Svalbard and Jan Mayen',
    'Soviet Union' => 'USSR',
    'Swaziland' => 'Eswatini',
    'East Timor' => 'Timor-Leste',
    'Tanzania' => 'United Republic of Tanzania',
    'United States of America' => 'USA',
    'Holy See' => 'Vatican City',
    'St. Vincent and the Grenadines' => 'Saint Vincent and the Grenadines',
    'Venezuela' => 'Bolivarian Republic of Venezuela',
    'Wallis and Futuna Islands' => 'Wallis and Futuna',
    'Zaire' => 'Democratic Republic of Congo',
  ],
  'languages' => [
    'Navajo' => 'Navajo, Navaho',
    'Kuanyama' => 'Kwanyama, Kuanyama',
    'Pushto' => 'Pashto, Pushto',
    'Fulah' => 'Fula, Fulah, Pulaar, Pular',
    'Ndebele' => 'Northern Ndebele',
    'Frisian' => 'Western Frisian',
    'Haitian; Haitian Creole' => 'Haitian, Haitian Creole',
    'Marshall' => 'Marshallese',
    'Rundi' => 'Kirundi',
    'Gaelic' => 'Scottish Gaelic, Gaelic',
    'Chichewa; Nyanja' => 'Chichewa, Chewa, Nyanja',
    'Ojibwa' => 'Ojibwe, Ojibwa',
    'Abkhazian' => 'Abkhaz',
    'Bengali' => 'Bengali, Bangla',
    'Zhuang' => 'Zhuang, Chuang',
    'Mandarin' => 'Chinese',
    'Greek' => 'Greek (modern)',
    'Guarani' => 'Guaraní',
    'Kalaallisut' => 'Kalaallisut, Greenlandic',
    'Kirghiz' => 'Kyrgyz',
    'Slovenian' => 'Slovene',
    'Uighur' => 'Uyghur',
    'Divehi' => 'Divehi, Dhivehi, Maldivian',
    'Kikuyu' => 'Kikuyu, Gikuyu',
    'Sinhalese' => 'Sinhalese, Sinhala',
    'Tonga' => 'Tonga (Tonga Islands)',
    'Raeto-Romance' => 'Romansh',
    'No Language' => 'No spoken language',
    'Hebrew' => 'Hebrew (modern)',
    'Punjabi' => 'Eastern Punjabi, Eastern Panjabi',
    'Sanskrit' => 'Sanskrit (Saṁskṛta)',
    'Persian' => 'Persian (Farsi)',
    'Maori' => 'Māori',
    'Sotho' => 'Southern Sotho',
    'Letzeburgesch' => 'Luxembourgish, Letzeburgesch',
    'Limburgish' => 'Limburgish, Limburgan, Limburger',
    'Ossetian; Ossetic' => 'Ossetian, Ossetic',
  ],
];

$letterboxdCountryPage = "https://letterboxd.com/countries/";

$dom = new DOMDocument();
libxml_use_internal_errors(true); // Suppress parsing warnings for malformed HTML
$dom->loadHTML(file_get_contents($letterboxdCountryPage));
libxml_clear_errors();

$xpath = new DOMXPath($dom);

// First section is countries, second is languages
$sections = $xpath->query("//div[contains(@class, 'browse-countries')]//section");

$countries = [];
$countryItems = $xpath->query(".//div[@class='listitem']", $sections[1]);
foreach ($countryItems as $countryItem) {
  // Add to the countries array
  $countries[str_replace("\xC2\xA0", ' ', (trim($xpath->query(".//span[@class='name']", $countryItem)->item(0)->nodeValue)))] = [
    'url' => 'https://letterboxd.com' . trim($xpath->query(".//a[@class='link']", $countryItem)->item(0)->getAttribute('href')),
    'count' => (int)str_replace(',', '', trim($xpath->query(".//span[@class='count']", $countryItem)->item(0)->nodeValue)),
  ];
}

$languages = [];
$languageItems = $xpath->query(".//div[@class='listitem']", $sections[2]);
foreach ($languageItems as $languageItem) {
  // Add to the countries array
  $languages[str_replace("\xC2\xA0", ' ', (trim($xpath->query(".//span[@class='name']", $languageItem)->item(0)->nodeValue)))] = [
    'url' => 'https://letterboxd.com' . trim($xpath->query(".//a[@class='link']", $languageItem)->item(0)->getAttribute('href')),
    'count' => (int)str_replace(',', '', trim($xpath->query(".//span[@class='count']", $languageItem)->item(0)->nodeValue)),
  ];
}

$tmdbData = getLanguagesAndCountries();

// TODO: Upload this to the server so that this is only run sparingly
$combined = [
  'countries' => [],
  'languages' => [],
];

foreach ($tmdbData['countries'] as $short => $full) {
  if (array_key_exists($full, $mapping['countries'])) {
    $full = $mapping['countries'][$full];
  }
  if (array_key_exists($full, $countries)) {
    $combined['countries'][$short] = [
      'url' => $countries[$full]['url'],
      'count' => $countries[$full]['count'],
      'full' => $full,
    ];
  } else {
    // just Northern Ireland last check
    // echo 'missing country - ' . $full . '<br>';
  }
}

foreach ($tmdbData['languages'] as $short => $full) {
  if (array_key_exists($full, $mapping['languages'])) {
    $full = $mapping['languages'][$full];
  }
  if (array_key_exists($full, $languages)) {
    $combined['languages'][$short] = [
      'url' => $languages[$full]['url'],
      'count' => $languages[$full]['count'],
      'full' => $full,
    ];
  } else {
    // There are a number, but they seem pretty rare?
    // echo 'missing language - ' . $full . '<br>';
  }
}

uasort($combined['countries'], function($a, $b) {
  return $b['count'] <=> $a['count'];
});
uasort($combined['languages'], function($a, $b) {
  return $b['count'] <=> $a['count'];
});

$PDO = getDatabase();
$placeholders = [];
$bindValues = [];
foreach ($combined['countries'] as $country_code => $info) {
  $placeholders[] = '(' . implode(',', array_fill(0, 3, '?')) . ')';
  $bindValues = array_merge($bindValues, [$country_code, $info['count'], $info['url']]);
}
$sql = "REPLACE INTO countries
(country_code, num_movies, url)
VALUES " . implode(', ', $placeholders);
$stmt = $PDO->prepare($sql);
$stmt->execute($bindValues);
?>