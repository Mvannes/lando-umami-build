<?php

declare(strict_types = 1);

namespace Drupal\janus_render_cache;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\janus_render_cache\Cache\JanusABRenderCache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Drupal\janus_render_cache\JanusRenderCacheServiceProvider
 */
class JanusRenderCacheServiceProviderTest extends TestCase {

  /**
   * Test that we can override services using the alter function.
   */
  public function testAlter(): void {
    $definition = new Definition();
    $definition->setClass(JanusABRenderCache::class)
      ->addArgument(new Reference('request_stack'))
      ->addArgument(new Reference('cache_factory'))
      ->addArgument(new Reference('cache_contexts_manager'))
      ->addArgument(new Reference('render_placeholder_generator'))
      ->addArgument(new Reference('janus_ab.ab_config'))
      ->addMethodCall(
        'setVariationPicker',
        [new Reference('janus_ab.variation_picker')]
      );

    $containerBuilder = $this->prophesize(ContainerBuilder::class);
    $containerBuilder->setDefinition(
      'render_cache',
      $definition
    )->shouldBeCalled();

    $provider = new JanusRenderCacheServiceProvider();
    $provider->alter($containerBuilder->reveal());
  }

}
