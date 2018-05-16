<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\JavaScript;

use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\ExperimentInterface;
use Synetic\JanusAB\Variation\VariationPickerInterface;

/**
 * Helper class to attach javascript libraries within this module in hooks.
 */
class LibraryAttacher implements LibraryAttacherInterface {

  /**
   * Constant holding the success library name.
   *
   * @var string
   */
  private const SUCCESS_LIBRARY = 'janus_ab/success';

  /**
   * Constant holding the traffic library name.
   *
   * @var string
   */
  private const TRAFFIC_LIBRARY = 'janus_ab/traffic';

  /**
   * Constant holding the click library name.
   *
   * @var string
   */
  private const EVENT_LIBRARY = 'janus_ab/event';

  /**
   * The janus config object.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * The variation picker to use.
   *
   * @var \Synetic\JanusAB\Variation\VariationPickerInterface
   */
  private $variationPicker;

  /**
   * LibraryAttacher constructor.
   *
   * @param \Synetic\JanusAB\Config\ABConfigInterface $config
   *   The config object to use.
   * @param \Synetic\JanusAB\Variation\VariationPickerInterface $variationPicker
   *   The variation picker to use.
   */
  public function __construct(
    ABConfigInterface $config,
    VariationPickerInterface $variationPicker
  ) {
    $this->config          = $config;
    $this->variationPicker = $variationPicker;
  }

  /**
   * {@inheritdoc}
   */
  public function attachSuccessLibraryForExperimentId(
    array $variables,
    string $experimentId
  ): array {
    // If no active experiment is found, don't attach the library.
    if (!$this->config->hasActiveExperimentWithId($experimentId)) {
      return $variables;
    }

    $experiment = $this->config->getActiveExperimentById($experimentId);
    $variation = $this->variationPicker->pickVariationForExperiment($experiment);

    $variables['#attached']['library'][] = self::SUCCESS_LIBRARY;
    $variables['#attached']['drupalSettings']['janus_ab']['success'] = [
      'experimentId' => $experiment->getId(),
      'variationId'  => $variation->getId(),
      'successUrl'   => $this->config->getSuccessUrl(),
      'userIdCookie' => $this->getUserIdCookieName($experiment),
    ];

    // Return the updated array.
    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function attachTrafficLibraryForExperimentId(
    array $variables,
    string $experimentId
  ): array {
    // If no active experiment is found, don't attach the library.
    if (!$this->config->hasActiveExperimentWithId($experimentId)) {
      return $variables;
    }

    $experiment = $this->config->getActiveExperimentById($experimentId);
    $variation = $this->variationPicker->pickVariationForExperiment($experiment);

    $variables['#attached']['library'][] = self::TRAFFIC_LIBRARY;
    $variables['#attached']['drupalSettings']['janus_ab']['traffic'] = [
      'experimentId' => $experiment->getId(),
      'variationId'  => $variation->getId(),
      'trafficUrl'   => $this->config->getTrafficUrl(),
      'userIdCookie' => $this->getUserIdCookieName($experiment),
    ];

    // Return the updated array.
    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function attachSuccessEventLibraryForExperimentId(
    array $variables,
    string $selector,
    string $event,
    string $experimentId
  ): array {
    // If no active experiment is found, don't attach the library.
    if (!$this->config->hasActiveExperimentWithId($experimentId)) {
      return $variables;
    }

    $experiment = $this->config->getActiveExperimentById($experimentId);
    $variation = $this->variationPicker->pickVariationForExperiment($experiment);

    $variables['#attached']['library'][] = self::EVENT_LIBRARY;
    $variables['#attached']['drupalSettings']['janus_ab']['event'] = [
      'experimentId' => $experiment->getId(),
      'variationId'  => $variation->getId(),
      'postUrl'      => $this->config->getSuccessUrl(),
      'selector'     => $selector,
      'event'        => $event,
      'userIdCookie' => $this->getUserIdCookieName($experiment),
    ];

    // Return the updated array.
    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function attachTrafficEventLibraryForExperimentId(
    array $variables,
    string $selector,
    string $event,
    string $experimentId
  ): array {
    // If no active experiment is found, don't attach the library.
    if (!$this->config->hasActiveExperimentWithId($experimentId)) {
      return $variables;
    }

    $experiment = $this->config->getActiveExperimentById($experimentId);
    $variation = $this->variationPicker->pickVariationForExperiment($experiment);

    $variables['#attached']['library'][] = self::EVENT_LIBRARY;
    $variables['#attached']['drupalSettings']['janus_ab']['event'] = [
      'experimentId' => $experiment->getId(),
      'variationId'  => $variation->getId(),
      'postUrl'      => $this->config->getTrafficUrl(),
      'selector'     => $selector,
      'event'        => $event,
      'userIdCookie' => $this->getUserIdCookieName($experiment),
    ];

    // Return the updated array.
    return $variables;
  }

  /**
   * Use the given experiment to form the name of the userIdCookie.
   *
   * @param \Synetic\JanusAB\Variation\ExperimentInterface $experiment
   *   The experiment to use when creating a cookie.
   *
   * @return string
   *   The correct cookie name.
   */
  private function getUserIdCookieName(ExperimentInterface $experiment) {
    return sprintf(
      '%s_ID',
      $this->config->getCookieNameForExperimentId($experiment->getId())
    );
  }

}
