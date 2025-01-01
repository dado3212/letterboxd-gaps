<?php
require_once("secret.php");

function hasFemaleDirector($movieName, $movieYear) {
  $searchUrl = "https://api.themoviedb.org/3/search/movie?api_key=".TMDB_API_KEY . "&query=" . urlencode($movieName) . "&year=$movieYear";

  $searchResponse = file_get_contents($searchUrl);
  $searchData = json_decode($searchResponse, true);

  if (!isset($searchData['results'][0]['id'])) {
    die("Movie not found.\n");
  }
  
  $movieId = $searchData['results'][0]['id'];
  // original_language
  
  // Step 2: Get the movie credits
  $creditsUrl = "https://api.themoviedb.org/3/movie/$movieId/credits?api_key=".TMDB_API_KEY;
  
  $creditsResponse = file_get_contents($creditsUrl);
  $creditsData = json_decode($creditsResponse, true);
  
  $female_directors = array_filter($creditsData['crew'], function ($crewMember) {
    return $crewMember['job'] === 'Director' && $crewMember['gender'] === 1;
  });
  
  return count($female_directors) > 0;
}
?>