<?php

declare(strict_types = 1);

namespace Drupal\janus_page_cache\Cache;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\janus_ab\Variation\CrawlerAwareVariationPicker;
use Drupal\page_cache\StackMiddleware\PageCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\VariationPickerInterface;

/**
 * Override the PageCache to allow for AB-testing.
 */
class JanusABPageCache extends PageCache {

  /**
   * The Janus configuration object.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * The variation picker to use.
   *
   * This is a CrawlerAwareVariationPicker rather than a regular
   * VariationPickerInterface because we require access to functions that are
   * specific to that subclass of the VariationPicker.
   *
   * @var \Drupal\janus_ab\Variation\CrawlerAwareVariationPicker
   */
  private $variationPicker;

  /**
   * Constructs a JanusABPageCache object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache bin.
   * @param \Drupal\Core\PageCache\RequestPolicyInterface $request_policy
   *   A policy rule determining the cacheability of a request.
   * @param \Drupal\Core\PageCache\ResponsePolicyInterface $response_policy
   *   A policy rule determining the cacheability of the response.
   * @param \Synetic\JanusAB\Config\ABConfigInterface $config
   *   The Janus configuration object.
   * @param \Drupal\janus_ab\Variation\CrawlerAwareVariationPicker $variationPicker
   *   The variation picker to use.
   */
  public function __construct(
    HttpKernelInterface $http_kernel,
    CacheBackendInterface $cache,
    RequestPolicyInterface $request_policy,
    ResponsePolicyInterface $response_policy,
    ABConfigInterface $config,
    CrawlerAwareVariationPicker $variationPicker
  ) {
    parent::__construct(
      $http_kernel,
      $cache,
      $request_policy,
      $response_policy
    );
    $this->config          = $config;
    $this->variationPicker = $variationPicker;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCacheId(Request $request) {
    // If there is no active experiment, fall back to the regular cache id.
    if (!$this->config->hasActiveExperiment()) {
      return parent::getCacheId($request);
    }
    $experiment = $this->config->getActiveExperiment();
    $variation  = $this->variationPicker
      ->pickVariationForExperimentAndRequest($experiment, $request);

    $cid_parts = [
      $request->getSchemeAndHttpHost() . $request->getRequestUri(),
      $request->getRequestFormat(),
      $experiment->getId(),
      $variation->getId(),
    ];
    return implode(':', $cid_parts);
  }

}
