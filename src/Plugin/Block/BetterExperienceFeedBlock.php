<?php

namespace Drupal\better_experience_feed\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'BetterExperienceFeedBlock' block.
 *
 * @Block(
 *  id = "better_experience_feed",
 *  admin_label = @Translation("Better Experience Feed"),
 * )
 */
class BetterExperienceFeedBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Ok ok this is a dublicate shiit dude!!!!
    $contentTypes = \Drupal::service('entity.manager')->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
        $contentTypesList[$contentType->id()] = $contentType->label();
    }
    $form['#tree'] = TRUE;
    $form['type_of_request'] = [
      '#type' => 'select',
      '#title' => $this->t('Type of request'),
      '#default_value' => (isset($this->configuration['type_of_request'])) ? $this->configuration['type_of_request'] : 0,
      '#options' => array(
        0 => $this->t('Local'),
        1 => $this->t('Externe'),
        2 => $this->t('Both'),
      ),
      '#description' => $this->t('Choose a mode request.'),
      '#prefix' => '<div class="better-experience-type-of-request">',
      '#suffix' => '</div>',
    ];
    $form['request'] = array(
      '#type' => 'details',
      '#title' => t('Request settings'),
      '#open' => FALSE,
    );
    $form['request']['order_asc_des'] = [
      '#type' => 'select',
      '#title' => t('Order'),
      '#default_value' => (isset($this->configuration['options_request']['order_asc_des'])) ? $this->configuration['options_request']['order_asc_des'] : 0,
      '#options' => array(
        0 => $this->t('Ascending'),
        1 => $this->t('Descending'),
      ),
      '#prefix' => '<div class="better-experience-type-of-order-asc">',
      '#suffix' => '</div>',
    ];
    $form['request']['order_by_type'] = [
      '#type' => 'select',
      '#title' => t('Filter'),
      '#default_value' => (isset($this->configuration['options_request']['order_by_type'])) ? $this->configuration['options_request']['order_by_type'] : 0,
      '#options' => array(
        'sortByDateElement' => $this->t('Date'),
        'sortByTitleElement' => $this->t('Title'),
      ),
      '#prefix' => '<div class="better-experience-type-of-order">',
      '#suffix' => '</div>',
    ];
    $form['request']['number_of_items'] = [
      '#type' => 'select',
      '#title' => t('Items'),
      '#default_value' => (isset($this->configuration['options_request']['number_of_items'])) ? $this->configuration['options_request']['number_of_items'] : 5,
      '#options' => array(
        5 => $this->t('5'),
        10 => $this->t('10'),
        15 => $this->t('15'),
        20 => $this->t('20'),
      ),
      '#prefix' => '<div class="better-experience-number-of-items">',
      '#suffix' => '</div>',
    ];
    $form['url_options'] = array(
      '#type' => 'details',
      '#title' => t('Rss url'),
      '#open' => TRUE,
      '#prefix' => '<div class="better-experience-url-options">',
      '#suffix' => '</div>',
    );
    $form['url_options']['url_of_feed'] = [
      '#type' => 'url',
      '#title' => $this->t('Feed url'),
      '#description' => $this->t('Get a url of feed for external or both option.'),
      '#default_value' => (isset($this->configuration['url_options']['url_of_feed'])) ? $this->configuration['url_options']['url_of_feed'] : '',
      '#prefix' => '<div class="better-experience-url-of-feed">',
      '#suffix' => '</div>',
    ];
    $form['url_options']['url_feed_info'] = [
      '#type' => 'select',
      '#title' => $this->t('Feed info'),
      '#default_value' => (isset($this->configuration['url_options']['url_feed_info'])) ? $this->configuration['url_options']['url_feed_info'] : 0,
      '#options' => array(
        0 => $this->t('Show'),
        1 => $this->t('Hide'),
      ),
      '#description' => $this->t('Show information of feed if avalable.'),
      '#prefix' => '<div class="better-experience-url-of-feed">',
      '#suffix' => '</div>',
    ];
    $form['type_of_content'] = [
      '#type' => 'select',
      '#title' => $this->t('Type of content'),
      '#options' => $contentTypesList,
      '#default_value' => (isset($contentTypesList['article'])) ? 'article' : '',
      '#description' => $this->t('Choose a content type if local or both option.'),
      '#prefix' => '<div class="better-experience-type-of-content">',
      '#suffix' => '</div>',
    ];

    $form['#attached']['library'][] = 'better_experience_feed/betterexperienceadmin';

    return $form;
  }

  /**
   * {@inheritdoc}
   * @param $type_of_request, $url_of_feed, $type_of_content
   * info for request int
   * url of feed rss string
   * the type of content to show string
   * @return $build array 
   */
  public function processSelectBlock($type_of_request = 0, $url_of_feed = null, $type_of_content = 'article', $options_request) {
    
    // Prepare a return array
    $build = array();
    // Call the service
    $better_experience_Service  = \Drupal::service('better_experience_feed.request_system');
    // If the service is avalable run the process to populate block result
    if ($better_experience_Service) {
        // If an url Rss Feed is request
      if ($type_of_request == 1) {
        // Request the service if url is not null
        if ($url_of_feed != null) {
          $build = $better_experience_Service->getUrlRss($url_of_feed, $options_request);
        }
      }elseif($type_of_request == 2){ 
        // if is a Remix
        $build = $better_experience_Service->getMixRss($url_of_feed, $type_of_content, $options_request);
      }else{
        // If is article Feed
        $build = $better_experience_Service->getContentTypeRss($type_of_content, $options_request);
      }
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state)
  {
    $feed_url = $form_state->getValue('url_options')['url_of_feed'];
    if($form_state->getValue('url_options')['url_of_feed'] != '' || $form_state->getValue('type_of_request') == 2 || $form_state->getValue('type_of_request') == 1){
      $fluxRss = simplexml_load_file($feed_url);
      if ($fluxRss == false) {
        $form_state->setErrorByName('url_options', $this->t('Sorry, You need a flux Rss valid or leave empty.'));
      } 
    }
    return $form_state;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state)
  {
    $this->configuration['type_of_request'] = $form_state->getValue('type_of_request');
    $this->configuration['options_request'] = $form_state->getValue('request');
    $this->configuration['url_options'] = $form_state->getValue('url_options');
    $this->configuration['type_of_content'] = $form_state->getValue('type_of_content');
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    // Prepare the build
    $build = [];
    // Store the configuration
    // The request
    $type_of_request = $this->configuration['type_of_request'];
    $options_request = $this->configuration['options_request'];
    $options_request['url_feed_info'] = $this->configuration['url_options']['url_feed_info'];
    $options_request = (object)$options_request;
    // The type
    $url_of_feed = $this->configuration['url_options']['url_of_feed'];
    // $url_feed_info = $this->configuration['url']['url_feed_info'];
    $type_of_content = $this->configuration['type_of_content'];
    // Run the process
    $list_Build = $this->processSelectBlock($type_of_request, $url_of_feed, $type_of_content, $options_request);
    // make magic in retun
    // send Data to twig
    // format allowed tag
    if ($list_Build) {
      $build['better_experience_feed'][] = array(
        '#markup' => '',
        '#theme' => 'better_experience_feed_block',
        '#info' => $list_Build->info,
        '#data' => $list_Build->data,
        '#type_request' => $type_of_request,
        '#options_request' => $options_request,
        '#allowed_tags' => ['img', 'span', 'a', 'div', 'nav', 'ul', 'li', 'h4'],
      );
      // No cache please
      $build['#cache']['max-age'] = 0;
    }
    // return the block
    return $build;
  }

}
