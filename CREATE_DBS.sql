CREATE DATABASE `letterboxd` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

-- letterboxd.countries definition

CREATE TABLE `countries` (
  `country_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `num_movies` bigint NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- letterboxd.movies definition

CREATE TABLE `movies` (
  `movie_name` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id` bigint NOT NULL AUTO_INCREMENT,
  `tmdb_id` bigint DEFAULT NULL,
  `imdb_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `letterboxd_url` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_female_director` tinyint(1) DEFAULT NULL,
  `language` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poster` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `countries` json DEFAULT NULL,
  `status` enum('pending','processing','done') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `primary_color` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4558 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- letterboxd.upload_tracking definition

CREATE TABLE `upload_tracking` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `uploaded` json DEFAULT NULL,
  `uploaded_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- letterboxd.languages definition

CREATE TABLE `languages` (
  `language_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `num_movies` bigint NOT NULL,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;