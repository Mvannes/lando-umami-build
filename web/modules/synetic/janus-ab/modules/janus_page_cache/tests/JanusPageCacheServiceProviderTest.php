<?php

declare(strict_types = 1);

namespace Drupal\janus_dynamic_page_cache;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\janus_ab\Variation\CrawlerAwareVariationPicker;
use Drupal\janus_page_cache\Cache\JanusABPageCache;
use Drupal\janus_page_cache\JanusPageCacheServiceProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Drupal\janus_page_cache\JanusPageCacheServiceProvider
 */
class JanusPageCacheServiceProviderTest extends TestCase {

  /**
   * Test that we can override services using the alter function.
   */
  public function testAlter(): void {
    $pickerDefinition = new Definition();
    $pickerDefinition->setClass(CrawlerAwareVariationPicker::class)
      ->setFactory(
        [new Reference('janus_ab.variation_picker_factory'), 'create']
      )->setShared(FALSE);

    $cacheDefinition = new Definition();
    $cacheDefinition->setClass(JanusABPageCache::class)
      ->addArgument(new Reference('cache.page'))
      ->addArgument(new Reference('page_cache_request_policy'))
      ->addArgument(new Reference('page_cache_response_policy'))
      ->addArgument(new Reference('janus_ab.ab_config'))
      ->addArgument(new Reference('janus_ab.crawler_aware_variation_picker'))
      ->addTag('http_middleware', ['priority' => 200, 'responder' => TRUE]);

    $containerBuilder = $this->prophesize(ContainerBuilder::class);
    $containerBuilder->setDefinition(
      'janus_ab.crawler_aware_variation_picker',
      $pickerDefinition
    )->shouldBeCalled();
    $containerBuilder->setDefinition(
      'http_middleware.page_cache',
      $cacheDefinition
    )->shouldBeCalled();

    $provider = new JanusPageCacheServiceProvider();
    $provider->alter($containerBuilder->reveal());
  }

}
