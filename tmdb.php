<?php
require_once("secret.php");

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