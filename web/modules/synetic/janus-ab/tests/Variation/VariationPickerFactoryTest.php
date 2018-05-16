<?php
namespace Drupal\janus_ab\Variation;

use PHPUnit\Framework\TestCase;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Cookie\CookieJarInterface;
use Synetic\JanusAB\Variation\VariationPicker;

/**
 * @covers \Drupal\janus_ab\Variation\VariationPickerFactory
 */
class VariationPickerFactoryTest extends TestCase {

  /**
   * Test creation of the variation picker from configuration.
   */
  public function testCreate() {
    $config    = $this->prophesize(ABConfigInterface::class)->reveal();
    $cookieJar = $this->prophesize(CookieJarInterface::class)->reveal();
    $factory   = new VariationPickerFactory($config, $cookieJar);

    $expected = new VariationPicker($cookieJar, $config);
    self::assertEquals($expected, $factory->create());
  }
}
