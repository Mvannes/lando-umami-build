<?php

declare(strict_types = 1);

namespace Drupal\janus_render_cache\Cache;

use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Render\PlaceholderGeneratorInterface;
use Drupal\Core\Render\PlaceholderingRenderCache;
use Symfony\Component\HttpFoundation\RequestStack;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\VariationPickerInterface;

/**
 * Override the RenderCache to allow for AB-testing.
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

  /**
   * JanusABRenderCache constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   RequestStack to get the correct request object.
   * @param \Drupal\Core\Cache\CacheFactoryInterface $cacheFactory
   *   Cachefactory to create caches.
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $cacheContextsManager
   *   CacheContextsManager to manage caches.
   * @param \Drupal\Core\Render\PlaceholderGeneratorInterface $placeholderGenerator
   *   PlaceholderGenerator to generate placeholders.
   * @param \Synetic\JanusAB\Config\ABConfigInterface $config
   *   ABConfig to check if experiments exist.
   * @param \Synetic\JanusAB\Variation\VariationPickerInterface $variationPicker
   *   Used to choose the correct variation for a user.
   */
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

  /**
   * {@inheritdoc}
   */
  protected function createCacheID(array &$elements) {
    // If we have an experiment, we should add cache contexts for the experiment
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
