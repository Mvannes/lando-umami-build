<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Config;

use Drupal\Core\Config\ConfigFactoryInterface;
use Synetic\JanusAB\Config\ABConfig;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\Experiment;
use Synetic\JanusAB\Variation\Variation;

/**
 * Implementation of the ABConfigFactoryInterface that makes ABConfigInterfaces.
 *
 * Creates objects based on the information defined within the Drupal config
 * under the name 'janus.settings'.
 */
class ABConfigFactory implements ABConfigFactoryInterface {

  /**
   * The config name to use.
   *
   * @var string
   */
  private const CONFIG_NAME = 'janus_ab.settings';

  /**
   * Holds configuration for the JanusAB module.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * ABConfigFactory constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Creates the correct config object.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->config = $configFactory->get(self::CONFIG_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public function create(): ABConfigInterface {
    $experimentObjects = [];
    foreach ($this->config->get('experiments') as $experiment) {
      $variationObjects = [];
      foreach ($experiment['variations'] as $variation) {
        $variationObjects[] = new Variation(
          (string) $variation['id'],
          $variation['name']
        );
      }
      $experimentObjects[] = new Experiment(
        $experiment['id'],
        new \DateTime($experiment['startDate']),
        new \DateTime($experiment['endDate']),
        $variationObjects
      );
    }
    // Cast to string to ensure argument types always match.
    $abConfig = new ABConfig(
      (string) $this->config->get('vendorName'),
      (string) $this->config->get('siteName'),
      (string) $this->config->get('trafficUrl'),
      (string) $this->config->get('successUrl'),
      (string) $this->config->get('analyticsId'),
      $experimentObjects
    );

    return $abConfig;
  }

}
