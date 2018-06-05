<?php

declare(strict_types = 1);

namespace Drupal\janus_render_cache\Cache;

use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Render\PlaceholderGeneratorInterface;
use Drupal\Core\Render\PlaceholderingRenderCache;
use Symfony\Component\HttpFoundation\RequestStack;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\Variation;
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
   */
  public function __construct(
    RequestStack $requestStack,
    CacheFactoryInterface $cacheFactory,
    CacheContextsManager $cacheContextsManager,
    PlaceholderGeneratorInterface $placeholderGenerator,
    ABConfigInterface $config
  ) {
    parent::__construct(
      $requestStack,
      $cacheFactory,
      $cacheContextsManager,
      $placeholderGenerator
    );
    $this->config = $config;
  }

  /**
   * Late init setter to avoid container compilation issues.
   *
   * This setter should be called as part of the creation of this service using
   * a "methodcall". This is required because of how early in the request the
   * render cache is created. If one were to inject the VariationPicker as part
   * of the constructor, the reference to the RequestStack object is still null.
   * This means that a VariationPicker that makes use of the RequestStack such
   * as the one used here, will throw null reference errors.
   *
   * By using the setVariationPicker function, the RenderCache has been
   * initialized, and so has the RequestStack object that is injected into it.
   * If we inject the VariationPicker here, it will be constructed with the
   * correct RequestStack reference.
   *
   * Because the RenderCache is a service, we can ensure that the
   * setVariationPicker function is always called during its initialization.
   * This should be ensured, as without this object being set, the class will
   * not work.
   *
   * @param \Synetic\JanusAB\Variation\VariationPickerInterface $variationPicker
   *   The variation picker to use in choosing variations.
   */
  public function setVariationPicker(
    VariationPickerInterface $variationPicker
  ): void {
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
      $variation  = $this->variationPicker->pickVariationForExperiment(
        $experiment
      );
      $elements['#cache']['context'][] = $experiment->getId();
      $elements['#cache']['context'][] = $variation->getId();
    }
    parent::createCacheID($elements);
  }

}
