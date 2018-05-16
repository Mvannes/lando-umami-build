<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Variation;

use Synetic\JanusAB\Variation\VariationPickerInterface;

/**
 * Creates VariationPickers for the service containers.
 */
interface VariationPickerFactoryInterface {

  /**
   * Create the variation picker with the configured settings.
   *
   * @return \Synetic\JanusAB\Variation\VariationPickerInterface
   *   The created variation picker.
   */
  public function create(): VariationPickerInterface;

}
