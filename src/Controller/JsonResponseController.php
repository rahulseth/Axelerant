<?php

namespace Drupal\axelerant\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata; 

/**
 * Provides route responses for the axelerant module.
 */
class JsonResponseController extends ControllerBase {

  /**
   * Returns a JSON response of node of type Page.
   *
   * @param $site_key
   *   The custom site api key.
   * @param $node_id
   *   The page node nid.
   *
   * @return JsonResponse
   *   Return json response.
   */
  public function responseJson($site_key, $node_id) {
    $cache_metadata = new CacheableMetadata();
    $cache_metadata->setCacheTags(['node:' . $node_id]);
    $node_array = $this->getPageNode($site_key, $node_id);

    // Create the JSON response object and add the cache metadata.
    $response = new CacheableJsonResponse($node_array);
    $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    $response->addCacheableDependency($cache_metadata);
    return $response;
  }

  /**
   * Return node array of type page.
   *
   * @param $site_key
   *   The custom site api key.
   * @param $node_id
   *   The page node nid.
   *
   * @return JsonResponse
   *   Return node array.
   */
  public function getPageNode($site_key, $node_id) {
    try {
      $node_json = [];
      $sitekey = \Drupal::config('system.site')->get('siteapikey');
      if (is_numeric($node_id) && !empty($node = Node::load($node_id)) && 
       $site_key == $sitekey && $node->bundle() == 'page') {
        $node_json[] = [
          'nid' => $node->id(),
          'title' =>  $node->get('title')->value,
          'body' => $node->get('body')->value,
        ];
        return $node_json;
      } else {
        // Return text 'access denied' if no result found.
        $node_json = ['message' => 'access denied.'];
        // throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
      }
    } catch(Exception $e) {
      \Drupal::logger('axelerant')->error($e->getMessage());
    }
    return $node_json;
  }

}
