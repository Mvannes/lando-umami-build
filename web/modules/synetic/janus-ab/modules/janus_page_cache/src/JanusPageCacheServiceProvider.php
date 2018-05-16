<?php

declare(strict_types = 1);

namespace Drupal\janus_page_cache;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\janus_page_cache\Cache\JanusABPageCache;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Service Provider / Modifier responsible for replacing services.
 *
 * Replaces the PageCache service with the Janus override.
 * This ensures that the internal page cache is no longer called in the caching
 * process, allowing for the caching of variations.
 */
class JanusPageCacheServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Create an entirely new definition instead of getting the existing one
    // from the container, because we always want to add this our own cache
    // if this module is turned on.
    $definition = new Definition();
    $definition->setClass(JanusABPageCache::class)
      ->addArgument(new Reference('cache.page'))
      ->addArgument(new Reference('page_cache_request_policy'))
      ->addArgument(new Reference('page_cache_response_policy'))
      ->addArgument(new Reference('janus_ab.ab_config'))
      ->addArgument(new Reference('janus_ab.variation_picker'))
      ->addTag('http_middleware', ['priority' => 200, 'responder' => TRUE]);
    $container->setDefinition('http_middleware.page_cache', $definition);
  }

}
