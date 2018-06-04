<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Variation;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Symfony\Component\HttpFoundation\RequestStack;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Cookie\CookieJarInterface;
use Synetic\JanusAB\Variation\ExperimentInterface;
use Synetic\JanusAB\Variation\VariationInterface;
use Synetic\JanusAB\Variation\VariationPicker;

/**
 * Extension of the VariationPicker that excludes Crawlers and Bots.
 *
 * Ensures that web crawlers such as the GoogleBot are not included in
 * Experiments. This ensures that bots are not included in the analysis of
 * variation performance.
 */
class CrawlerAwareVariationPicker extends VariationPicker {

  /**
   * The current request, used for its user agent.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  private $request;

  /**
   * Detector for recognizing crawlers.
   *
   * @var \Jaybizzle\CrawlerDetect\CrawlerDetect
   */
  private $crawlerDetect;

  /**
   * The key for the user agent in the request header bag.
   *
   * @var string
   */
  private const USER_AGENT_KEY = 'User-Agent';

  /**
   * CrawlerAwareVariationPicker constructor.
   *
   * @param \Synetic\JanusAB\Cookie\CookieJarInterface $cookieJar
   *   CookieJar used for reading and writing cookies.
   * @param \Synetic\JanusAB\Config\ABConfigInterface $config
   *   Configuration object used to read experiment info.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   RequestStack to get the current request's user agent.
   * @param \Jaybizzle\CrawlerDetect\CrawlerDetect $crawlerDetect
   *   CrawlerDetect object used in analyzing the user agent.
   */
  public function __construct(
    CookieJarInterface $cookieJar,
    ABConfigInterface $config,
    RequestStack $requestStack,
    CrawlerDetect $crawlerDetect
  ) {
    $this->request = $requestStack->getCurrentRequest();
    $this->crawlerDetect = $crawlerDetect;
    parent::__construct($cookieJar, $config);
  }

  /**
   * {@inheritdoc}
   */
  public function pickVariationForExperiment(
    ExperimentInterface $experiment
  ): VariationInterface {
    $userAgent = $this->request->headers->get(self::USER_AGENT_KEY);
    if ($this->crawlerDetect->isCrawler($userAgent)) {
      return $experiment->getVariations()[0];
    }
    return parent::pickVariationForExperiment($experiment);
  }

}
