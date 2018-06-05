<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Variation;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Symfony\Component\HttpFoundation\Request;
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
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

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
    $this->requestStack = $requestStack;
    $this->crawlerDetect = $crawlerDetect;
    parent::__construct($cookieJar, $config);
  }

  /**
   * {@inheritdoc}
   */
  public function pickVariationForExperiment(
    ExperimentInterface $experiment
  ): VariationInterface {
    $request   = $this->requestStack->getCurrentRequest();
    $userAgent = $request->headers->get(self::USER_AGENT_KEY);
    if ($this->crawlerDetect->isCrawler($userAgent)) {
      return $experiment->getVariations()[0];
    }
    return parent::pickVariationForExperiment($experiment);
  }

  /**
   * Chooses a Variation from the given Experiment using the request's UA.
   *
   * Uses the given Request to determine if the visiting user is a bot by
   * comparing its "User-Agent" header against known bot user agents.
   * If the user is a bot, the control variation is always returned.
   * If the user is not a bot, a random variation is chosen.
   *
   * This is done to ensure that SEO crawlers are always presented with the
   * same site.
   * Should be used over the "pickVariationForExperiment" function if there is
   * no RequestStack available, or if the RequestStack will always be empty.
   *
   * @param \Synetic\JanusAB\Variation\ExperimentInterface $experiment
   *   The Experiment to take variations from.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request whose User-Agent header will be parsed.
   *
   * @return \Synetic\JanusAB\Variation\VariationInterface
   *   The chosen VariationInterface object.
   */
  public function pickVariationForExperimentAndRequest(
    ExperimentInterface $experiment,
    Request $request
  ): VariationInterface {
    $userAgent = $request->headers->get(self::USER_AGENT_KEY);
    if ($this->crawlerDetect->isCrawler($userAgent)) {
      return $experiment->getVariations()[0];
    }
    return parent::pickVariationForExperiment($experiment);
  }

}
