<?php
require_once("secret.php");

function getInfo($letterboxdURL, $movieName, $movieYear) {
  $letterboxdPage = file_get_contents($letterboxdURL);

  $dom = new DOMDocument();
  libxml_use_internal_errors(true); // Suppress parsing warnings for malformed HTML
  $dom->loadHTML($letterboxdPage);
  libxml_clear_errors();

  $xpath = new DOMXPath($dom);

  $body = $xpath->query('//body')->item(0);
  preg_match("/.org\/(.*?)\/(\d+)\//", $xpath->query('//a[text()="TMDb"]')->item(0)->getAttribute('href'), $matches); // Finds <a> tags with text content "TMDb"
  $tmdbType = $matches[1];
  $tmdbId = (int)$matches[2];

  // echo '<pre>' . htmlspecialchars($letterboxdPage) . '</pre>';

  $posterUrl = null;
  if (preg_match("/{\"image\":\"(https:\/\/a.ltrbxd.com\/.*?)\?/", $letterboxdPage, $matches)) {
    $posterUrl = $matches[1];
  }
  $imdb_tag = $xpath->query('//a[text()="IMDb"]');
  $imdb_fallback = null;
  if ($imdb_tag->length) {
    preg_match("/\/(tt\d+)\//", $xpath->query('//a[text()="IMDb"]')->item(0)->getAttribute('href'), $imdb_matches); // Finds <a> tags with text content "IMDb"
    $imdb_fallback = $imdb_matches[1];
  }

  if ($tmdbType == 'movie') {
    $additionalURL = "https://api.themoviedb.org/3/movie/$tmdbId?append_to_response=credits&api_key=".TMDB_API_KEY;
    if (($data = @file_get_contents($additionalURL)) === false) {
      $info = [
        'tmdb_id' => $tmdbId,
        'poster' => $posterUrl,
        'language' => null,
        'imdb_id' => $imdb_fallback,
        'production_countries' => [],
        'has_female_director' => 0,
      ];
    } else {
      $additionalData = json_decode($data, true);

      $info = [
        'tmdb_id' => $tmdbId,
        'poster' => $posterUrl ?? ($additionalData['poster_path'] ? ('https://image.tmdb.org/t/p/w92' . $additionalData['poster_path']) : null),
        'language' => $additionalData['original_language'],
        'imdb_id' => $additionalData['imdb_id'] ?? $imdb_fallback,
        'production_countries' => array_filter(array_unique(array_map(function($company) {
          return $company['origin_country'];
        }, $additionalData['production_companies'])), function ($country) {
          return $country !== '';
        }),
        'has_female_director' => count(array_filter($additionalData['credits']['crew'], function ($crewMember) {
          return $crewMember['job'] === 'Director' && $crewMember['gender'] === 1;
        })) > 0,
      ];
    }
  } else {
    $additionalURL = "https://api.themoviedb.org/3/tv/$tmdbId?append_to_response=credits&api_key=".TMDB_API_KEY;
    if (($data = @file_get_contents($additionalURL)) === false) {
      $info = [
        'tmdb_id' => $tmdbId,
        'poster' => $posterUrl,
        'language' => null,
        'imdb_id' => $imdb_fallback,
        'production_countries' => [],
        'has_female_director' => 0,
      ];
    } else {
      $additionalData = json_decode($data, true);

      $info = [
        'tmdb_id' => $tmdbId,
        'poster' => $posterUrl ?? ($additionalData['poster_path'] ? ('https://image.tmdb.org/t/p/w92' . $additionalData['poster_path']) : null),
        'language' => $additionalData['original_language'],
        'imdb_id' => $additionalData['imdb_id'] ?? $imdb_fallback,
        'production_countries' => array_filter(array_unique(array_map(function($company) {
          return $company['origin_country'];
        }, $additionalData['production_companies'])), function ($country) {
          return $country !== '';
        }),
        'has_female_director' => count(array_filter($additionalData['created_by'], function ($crewMember) {
          return $crewMember['gender'] === 1;
        })) > 0,
      ];
    }
  }

  return $info;
}

function getMovieInfo($movieName, $movieYear) {
  $searchUrl = "https://api.themoviedb.org/3/search/movie?api_key=".TMDB_API_KEY . "&query=" . urlencode($movieName) . "&year=$movieYear";

  $searchResponse = file_get_contents($searchUrl);
  $searchData = json_decode($searchResponse, true);

  if (!isset($searchData['results'][0]['id'])) {
    return null;
  }
  
  $movieId = $searchData['results'][0]['id'];

  $info = [
    'poster' => $searchData['results'][0]['poster_path'],
    'tmdb_id' => $movieId,
    'language' => $searchData['results'][0]['original_language'],
  ];
  
  // Step 2: Get the movie credits
  $additionalURL = "https://api.themoviedb.org/3/movie/$movieId?append_to_response=credits&api_key=".TMDB_API_KEY;
  $additionalData = json_decode(file_get_contents($additionalURL), true);

  $info['imdb_id'] = $additionalData['imdb_id'];
  $info['production_countries'] = array_unique(array_map(function($company) {
    return $company['origin_country'];
  }, $additionalData['production_companies']));
  
  $female_directors = array_filter($additionalData['credits']['crew'], function ($crewMember) {
    return $crewMember['job'] === 'Director' && $crewMember['gender'] === 1;
  });

  $info['has_female_director'] = count($female_directors) > 0;
  
  return $info;
}

function getLanguagesAndCountries() {
  $searchUrl = "https://api.themoviedb.org/3/configuration?append_to_response=languages,countries&api_key=".TMDB_API_KEY;

  $searchResponse = file_get_contents($searchUrl);
  $searchData = json_decode($searchResponse, true);

  $countries = [];
  foreach ($searchData['countries'] as $country) {
    $countries[$country['iso_3166_1']] = $country['english_name'];
  }

  $languages = [];
  foreach ($searchData['languages'] as $language) {
    $languages[$language['iso_639_1']] = $language['english_name'];
  }

  return [
    'countries' => $countries,
    'languages' => $languages,
  ];
}
?>