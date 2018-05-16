<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Variation;

use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Cookie\CookieJarInterface;
use Synetic\JanusAB\Variation\VariationPicker;
use Synetic\JanusAB\Variation\VariationPickerInterface;

/**
 * Creates VariationPickers for the service containers.
 */
class VariationPickerFactory implements VariationPickerFactoryInterface {

  /**
   * The configuration.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * A cookie jar.
   *
   * @var \GuzzleHttp\Cookie\CookieJarInterface
   */
  private $cookieJar;

  /**
   * VariationPickerFactory constructor.
   *
   * @param \Synetic\JanusAB\Config\ABConfigInterface $config
   *   The config to use.
   * @param \Synetic\JanusAB\Cookie\CookieJarInterface $cookieJar
   *   The cookie jar to use.
   */
  public function __construct(ABConfigInterface $config, CookieJarInterface $cookieJar) {
    $this->config    = $config;
    $this->cookieJar = $cookieJar;
  }

  /**
   * Create the variation picker with the configured settings.
   *
   * @return \Synetic\JanusAB\Variation\VariationPickerInterface
   *   The created variation picker.
   */
  public function create(): VariationPickerInterface {
    return new VariationPicker($this->cookieJar, $this->config);
  }

}
