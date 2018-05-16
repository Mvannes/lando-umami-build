<?php

declare(strict_types = 1);

namespace Drupal\janus_render_cache;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\janus_render_cache\Cache\JanusABRenderCache;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Service Provider / Modifier responsible for replacing services.
 *
 * Replaces the PageCache service with the Janus override.
 * This ensures that the internal page cache is no longer called in the caching
 * process, allowing for the caching of variations.
 */
class JanusRenderCacheServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Create an entirely new definition instead of getting the existing one
    // from the container, because we always want to add this our own cache
    // if this module is turned on.
    $definition = new Definition();
    $definition->setClass(JanusABRenderCache::class)
      ->addArgument(new Reference('request_stack'))
      ->addArgument(new Reference('cache_factory'))
      ->addArgument(new Reference('cache_contexts_manager'))
      ->addArgument(new Reference('render_placeholder_generator'))
      ->addArgument(new Reference('janus_ab.ab_config'))
      ->addArgument(new Reference('janus_ab.variation_picker'));

    $container->setDefinition('render_cache', $definition);
  }

}
