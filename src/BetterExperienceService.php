<?php

namespace Drupal\better_experience_feed;

/**
 * This class provide the Better Experience Feed module
 */
class BetterExperienceService implements BetterExperienceServiceInterface {

	/**
	 * Constructs a new BetterExperienceService object.
	 */
	public function __construct() {}

	/**
     * Convert date to drupal date.
	 * @param $date
	 * date field
	 * @return string
     */
	public function convertDateDrupal($date)
	{
		// Format of final date
		$format = 'Y-m-d h:i:s';
		// Process the date
		$date = strtotime($date);
		$data = date($format, $date);
		return $date;
	}

	/**
     * Constructs a Mix request Rss flux.
	 * @param $url_of_feed, $content_type
	 * url of feed and content type
	 * @return array
     */
	public function getMixRss($url_of_feed, $content_type, $options_request)
	{
		// Need an array
    $build_flux = [];
    $build_flux['info'] = [];
    $num = $options_request->number_of_items;
    // Split number for the two options
    $options_request->number_of_items = round($num / 2);
		// Run all process
    // $contentRss = $this->getContentTypeRss($content_type, $options_request)->data;
		// $urlRss = $this->getUrlRss($url_of_feed, $options_request)->data;
		$contentRss = ($this->getContentTypeRss($content_type, $options_request) != null) ? $this->getContentTypeRss($content_type, $options_request)->data : [];
		$urlRss = ($this->getUrlRss($url_of_feed, $options_request) != null) ?  $this->getContentTypeRss($content_type, $options_request)->data : [];
		// reset number for thiw variable list
			$options_request->number_of_items = $num;
			// Merge all data in a big array
			$build_flux['data'] = array_merge($contentRss, $urlRss);
			if ($build_flux['data'] != null) {
				// Filter function
				uasort($build_flux['data'], [
					'Drupal\better_experience_feed\Utility\SortBetterExperienceFeed',
					$options_request->order_by_type
				]);
			}
			// Order Fonction
			$build_flux['data'] = $this->reverseList($options_request->order_asc_des, $build_flux['data']);
			// return it
			return (object) $build_flux;
   
  }
  
	/**
     * Constructs a content type request Rss flux.
	 * @param $content_type
	 * content type
	 * @return array
     */
	public function getContentTypeRss($content_type, $options_request)
	{
		// Need an array
		$build_flux = [];
		// Need to now and list in an array all content type on drupal instance
		$contentTypes = \Drupal::service('entity.manager')->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
		foreach ($contentTypes as $contentType)
		{
      $contentTypesList[] = $contentType->id();
		}
		// If the variable is a content type valid, start the process
		if (in_array($content_type, $contentTypesList)) 
		{
			// Collect the last 5 articles
			$nids = \Drupal::entityQuery('node')->condition('type',$content_type)->range(0,$options_request->number_of_items)->execute();
			$nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
			// Check if there is data
			if ($nodes) {
				// Build the info and data array
				$build_flux['info'] = [];
				foreach ($nodes as $node => $n)
				{
					$description = $n->get("body")->getValue();
					$description = ($description != null) ? substr($n->get("body")->getValue()[0]['value'], 0, 105) : '';
					$alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$n->Id());
					// Store information feedpubDate
					$build_flux['data'][] = [
						'title' => $n->getTitle(),
						'description' => strip_tags($description),
						'link' => $alias,
						'date' => (int)$n->getCreatedTime()
					];
				}
				// Filter function
				uasort($build_flux['data'], [
					'Drupal\better_experience_feed\Utility\SortBetterExperienceFeed',
					$options_request->order_by_type
				]);
				// Order Fonction
				$build_flux['data'] = $this->reverseList($options_request->order_asc_des, $build_flux['data']);
				// Return an object
				return (object) $build_flux;
			}
		}
	}

	/**
   * Constructs a Url request Rss flux.
	 * @param $urlRss
	 * url feed
	 * @return array
   */
	public function getUrlRss($urlRss, $options_request)
	{
		// Need an array
		$build_flux = [];
		// If the rss url is not valid return a massage
		$fluxRss = simplexml_load_file($urlRss);

		if ($fluxRss) {
			// Else prepare and build the Info Rss Owner Information array
			$rss_title = (isset($fluxRss->channel->title)) ? $fluxRss->channel->title->__toString() : '';
			$rss_description = (isset($fluxRss->channel->description)) ? $fluxRss->channel->description->__toString() : '';
			$rss_link = (isset($fluxRss->channel->link)) ? $fluxRss->channel->link->__toString() : '';
			$rss_pubDate = (isset($fluxRss->channel->pubDate)) ? $this->convertDateDrupal($fluxRss->channel->pubDate->__toString()) : '';
			// Store the info Owner Rss
			$build_flux['info'] = [
				'title' => $rss_title,
				'description' => $rss_description,
				'link' => $rss_link,
				'date' => $rss_pubDate
			];
			// Limit status
			$a = 1;
			// And process each items
			foreach ($fluxRss->channel->item as $item => $i)
			{
				// prepare article information
				$i_title = (isset($i->title)) ? $i->title->__toString() : '';
				$i_description = (isset($i->description)) ? $i->description->__toString() : '';
				$i_link = (isset($i->link)) ? $i->link->__toString() : '';
				$i_pubDate = (isset($i->pubDate)) ? $this->convertDateDrupal($i->pubDate->__toString()) : '';
				// Store listed article
				$build_flux['data'][] = [
					'title' => $i_title,
					'description' => $i_description,
					'link' => $i_link,
					'date' => $i_pubDate
				];
				// Limit status end
				if ($a++ == $options_request->number_of_items) break;
			}
			if ($build_flux['data'] != null) {
				// Filter function
				uasort($build_flux['data'], [
					'Drupal\better_experience_feed\Utility\SortBetterExperienceFeed',
					$options_request->order_by_type
				]);
				// Order Fonction
				$build_flux['data'] = $this->reverseList($options_request->order_asc_des, $build_flux['data']);
			}
			// Return an object
			return (object) $build_flux;
		}
  }
  
  	/**
   * Constructs a Url request Rss flux.
	 * @param $urlRss
	 * url feed
	 * @return array
   */
	public function reverseList($order, $build_flux)
	{
    if ($order == 1) {
      $build_flux = array_reverse($build_flux);
    }
		return $build_flux;
	}

}
