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
    // Return the updated array.
    return $this->attachLibraryForExperimentId(
      $variables,
      $experimentId,
      self::SUCCESS_LIBRARY
    );
  }

  /**
   * {@inheritdoc}
   */
  public function attachTrafficLibraryForExperimentId(
    array $variables,
    string $experimentId
  ): array {
    // Return the updated array.
    return $this->attachLibraryForExperimentId(
      $variables,
      $experimentId,
      self::TRAFFIC_LIBRARY
    );
  }

  /**
   * Attach either success or traffic libraries to render arrays.
   *
   * Generically handles it for both traffic and success libraries
   * to avoid code duplication.
   *
   * @param array $variables
   *   The variable array to be changed.
   * @param string $experimentId
   *   The experiment array to render.
   * @param string $libraryName
   *   The library to render, either traffic or success.
   *
   * @return array
   *   The updated variables.
   */
  private function attachLibraryForExperimentId(
    array  $variables,
    string $experimentId,
    string $libraryName
  ): array {
    // If no active experiment is found, don't attach the library.
    if (!$this->config->hasActiveExperimentWithId($experimentId)) {
      return $variables;
    }

    $experiment = $this->config->getActiveExperimentById($experimentId);
    $variation  = $this->variationPicker->pickVariationForExperiment(
      $experiment
    );
    $isTraffic = $libraryName === self::TRAFFIC_LIBRARY;
    $urlKey    = $isTraffic ? 'trafficUrl' : 'successUrl';
    $urlValue  = $isTraffic ?
      $this->config->getTrafficUrl() :
      $this->config->getSuccessUrl();
    $settingsKey = $isTraffic ? 'traffic' : 'success';

    $variables['#attached']['library'][] = $libraryName;
    $variables['#attached']['drupalSettings']['janus_ab'][$settingsKey] = [
      'experimentId' => $experiment->getId(),
      'variationId'  => $variation->getId(),
      $urlKey        => $urlValue,
      'userIdCookie' => $this->getUserIdCookieName($experiment),
    ];
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
    // Return the updated array.
    return $this->attachEventLibraryForExperimentId(
      $variables,
      $selector,
      $event,
      $experimentId,
      FALSE
    );
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
    // Return the updated array.
    return $this->attachEventLibraryForExperimentId(
      $variables,
      $selector,
      $event,
      $experimentId,
      TRUE
    );
  }

  /**
   * Attach event based libraries to a render array.
   *
   * Handled generically to avoid code duplication.
   *
   * @param array $variables
   *   The given variable render array.
   * @param string $selector
   *   A valid JQuery selector.
   * @param string $event
   *   The JavaScript event to bind the library to.
   * @param string $experimentId
   *   The experiment Id to attach for.
   * @param bool $isTraffic
   *   Should a traffic library be rendered or not.
   *
   * @return array
   *   The updated array of variables.
   */
  private function attachEventLibraryForExperimentId(
    array  $variables,
    string $selector,
    string $event,
    string $experimentId,
    bool   $isTraffic
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
      'postUrl'      => $isTraffic ?
        $this->config->getTrafficUrl() :
        $this->config->getSuccessUrl(),
      'selector'     => $selector,
      'event'        => $event,
      'userIdCookie' => $this->getUserIdCookieName($experiment),
    ];

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
