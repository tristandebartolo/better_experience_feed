<?php

namespace Drupal\better_experience_feed;

/**
 * Interface BetterExperienceServiceInterface.
 *
 * @package Drupal\better_experience_feed
 *
 * Provides an interface defining a Better Experience Feed Rss Service.
 */
interface BetterExperienceServiceInterface {
	/**
     * Convert date to drupal date.
	 * @param $date
	 * date field
	 * @return string
     */
	public function convertDateDrupal($date);
	/**
	 * Constructs a Mix Rss flux.
	 * @param $url_of_feed, $content_type
	 * url of feed and content type
	 * @param $options_request
	 * option parameters
	 * @return array
     */
  	public function getMixRss($url_of_feed, $content_type, $options_request);
	/**
	 * Constructs a content type Rss flux.
	 * @param $content_type
	 * content type
	 * @param $options_request
	 * option parameters
	 * @return array
	 */
	public function getContentTypeRss($content_type, $options_request);
	/**
	 * Constructs a content type Rss flux.
	 * @param $urlRss
	 * url feed
	 * @param $options_request
	 * option parameters
	 * @return array
	 */
	public function getUrlRss($urlRss, $options_request);

}
