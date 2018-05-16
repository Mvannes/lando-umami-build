<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Config;

use Synetic\JanusAB\Config\ABConfigInterface;

/**
 * Creates ABConfigInterface objects.
 */
interface ABConfigFactoryInterface {

  /**
   * Create an ABConfig object from the configured drupal settings.
   *
   * @return \Synetic\JanusAB\Config\ABConfigInterface
   *   The created ABConfigInterface object.
   */
  public function create(): ABConfigInterface;

}
