<?php

namespace Drupal\janus_ab\Variation;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Cookie\CookieJarInterface;

/**
 * @covers \Drupal\janus_ab\Variation\VariationPickerFactory
 */
class VariationPickerFactoryTest extends TestCase {

  /**
   * Test creation of the variation picker from configuration.
   */
  public function testCreate() {
    $config        = $this->prophesize(ABConfigInterface::class)->reveal();
    $cookieJar     = $this->prophesize(CookieJarInterface::class)->reveal();
    $requestStack  = $this->prophesize(RequestStack::class)->reveal();
    $crawlerDetect = $this->prophesize(CrawlerDetect::class)->reveal();
    $factory       = new VariationPickerFactory(
      $config,
      $cookieJar,
      $requestStack,
      $crawlerDetect
    );

    $expected = new CrawlerAwareVariationPicker(
      $cookieJar,
      $config,
      $requestStack,
      $crawlerDetect
    );
    self::assertEquals($expected, $factory->create());
  }

}
