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
    'poster' => 'https://image.tmdb.org/t/p/w500' . $searchData['results'][0]['poster_path'],
    'id' => $movieId,
  ];
  
  // Step 2: Get the movie credits
  $creditsUrl = "https://api.themoviedb.org/3/movie/$movieId/credits?api_key=".TMDB_API_KEY;
  
  $creditsResponse = file_get_contents($creditsUrl);
  $creditsData = json_decode($creditsResponse, true);
  
  $female_directors = array_filter($creditsData['crew'], function ($crewMember) {
    return $crewMember['job'] === 'Director' && $crewMember['gender'] === 1;
  });

  $info['hasFemaleDirector'] = count($female_directors) > 0;
  
  return $info;
}
?>