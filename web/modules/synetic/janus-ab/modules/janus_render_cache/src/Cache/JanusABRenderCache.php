<?php

declare(strict_types = 1);

namespace Drupal\janus_render_cache\Cache;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Core\Render\PlaceholderGeneratorInterface;
use Drupal\Core\Render\PlaceholderingRenderCache;
use Drupal\Core\Render\RenderCache;
use Drupal\page_cache\StackMiddleware\PageCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\VariationPickerInterface;

/**
 * Override the PageCache to allow for AB-testing.
 */
class JanusABRenderCache extends PlaceholderingRenderCache {

  /**
   * The Janus configuration object.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * The variation picker to use.
   *
   * @var \Synetic\JanusAB\Variation\VariationPickerInterface
   */
  private $variationPicker;

  public function __construct(
    RequestStack $requestStack,
    CacheFactoryInterface $cacheFactory,
    CacheContextsManager $cacheContextsManager,
    PlaceholderGeneratorInterface $placeholderGenerator,
    ABConfigInterface $config,
    VariationPickerInterface $variationPicker
  ) {
    parent::__construct(
      $requestStack,
      $cacheFactory,
      $cacheContextsManager,
      $placeholderGenerator
    );
    $this->config = $config;
    $this->variationPicker = $variationPicker;
  }

  protected function createCacheID(array &$elements) {
    // If we have an experiment, we should add cache contexts with the experiment
    // and variation ids to ensure user's caching works correctly.
    if ($this->config->hasActiveExperiment()) {
      $experiment = $this->config->getActiveExperiment();
      $variation = $this->variationPicker->pickVariationForExperiment($experiment);
      $elements['#cache']['context'][] = $experiment->getId();
      $elements['#cache']['context'][] = $variation->getId();
    }
    parent::createCacheID($elements);
  }
}
