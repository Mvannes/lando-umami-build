<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\DataStorage;

use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\DataStorage\DataStorageHandlerInterface;
use Synetic\JanusAB\DataStorage\StorageDataInterface;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * DataStorageHandler that sends experiment data to GoogleAnalytics.
 *
 * This specific implementation sends data to the measurement protocol found
 * in Universal Analytics.
 *
 * @see https://developers.google.com/analytics/devguides/collection/protocol/v1/
 */
class GoogleAnalyticsStorageHandler implements DataStorageHandlerInterface {

  /**
   * Constant holding the User Agent string that is send to Google Analytics.
   *
   * This can be used to filter out the AB-testing hits in various views.
   *
   * @var string
   */
  private const ANALYTICS_USER_AGENT = 'JanusAB - AB testing';

  /**
   * Constant holding the identifiable event category for Google Analytics.
   *
   * @var string
   */
  private const EVENT_CATEGORY = 'JanusAB';

  /**
   * Constant holding the Measurement API protocol version.
   *
   * @var string
   */
  private const PROTOCOL_VERSION = '1';

  /**
   * Analytics object that wraps around doing Google Analytics calls.
   *
   * @var \TheIconic\Tracking\GoogleAnalytics\Analytics
   */
  private $analytics;

  /**
   * The ABConfig interface that contains configuration information.
   *
   * Used for its analytics id.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $abConfig;

  /**
   * GoogleAnalyticsStorageHandler constructor.
   *
   * @param \TheIconic\Tracking\GoogleAnalytics\Analytics $analytics
   *   TheIconic analytics api client, used to talk to the measurement api.
   * @param \Synetic\JanusAB\Config\ABConfigInterface $abConfig
   *   The ABConfig for config information.
   */
  public function __construct(Analytics $analytics, ABConfigInterface $abConfig) {
    $this->analytics = $analytics;
    $this->abConfig = $abConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return GoogleAnalyticsStorageHandler::class;
  }

  /**
   * {@inheritdoc}
   */
  public function storeData(StorageDataInterface $data): void {
    $this->analytics->setProtocolVersion(self::PROTOCOL_VERSION)
      ->setTrackingId($this->abConfig->getAnalyticsId())
      ->setClientId($data->getUserId())
      ->setExperimentId($data->getExperimentId())
      ->setExperimentVariant($data->getVariationId())
      ->setEventCategory(self::EVENT_CATEGORY)
      ->setEventAction($data->getTrafficType())
      ->setAnonymizeIp(TRUE)
      ->setUserAgentOverride(self::ANALYTICS_USER_AGENT)
      ->sendEvent();
  }

}
