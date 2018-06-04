<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Variation;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Symfony\Component\HttpFoundation\RequestStack;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Cookie\CookieJarInterface;
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
   * The request stack to get the current request from.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Crawler detect object to check user agent of a request for crawlers.
   *
   * @var \Jaybizzle\CrawlerDetect\CrawlerDetect
   */
  private $crawlerDetect;

  /**
   * VariationPickerFactory constructor.
   *
   * @param \Synetic\JanusAB\Config\ABConfigInterface $config
   *   The config to use.
   * @param \Synetic\JanusAB\Cookie\CookieJarInterface $cookieJar
   *   The cookie jar to use.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack to use for its current request.
   * @param \Jaybizzle\CrawlerDetect\CrawlerDetect $crawlerDetect
   *   The CrawlerDetect object to detect user agents to exclude.
   */
  public function __construct(
    ABConfigInterface $config,
    CookieJarInterface $cookieJar,
    RequestStack $requestStack,
    CrawlerDetect $crawlerDetect
  ) {
    $this->config        = $config;
    $this->cookieJar     = $cookieJar;
    $this->requestStack  = $requestStack;
    $this->crawlerDetect = $crawlerDetect;
  }

  /**
   * Create the variation picker with the configured settings.
   *
   * @return \Synetic\JanusAB\Variation\VariationPickerInterface
   *   The created variation picker.
   */
  public function create(): VariationPickerInterface {
    return new CrawlerAwareVariationPicker(
      $this->cookieJar,
      $this->config,
      $this->requestStack,
      $this->crawlerDetect
    );
  }

}
