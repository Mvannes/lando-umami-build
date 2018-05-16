<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Routing;

use Psr\Http\Message\ResponseInterface;
use Synetic\JanusAB\Variation\ExperimentInterface;
use Synetic\JanusAB\Variation\VariationInterface;

/**
 * Fires requests to configured data collection endpoints.
 *
 * Wraps around manual request creation to ensure requests are correctly formed.
 */
interface JanusRequestHandlerInterface {

  /**
   * Create and send a traffic request to the configured traffic endpoint.
   *
   * This is a Sync request, but is as fire and forget as possible.
   * If the request is successful, a Response is returned.
   * If it fails for any reason it returns null.
   *
   * Note that even when a response is returned, it may not be a status 200
   * response.
   *
   * @param \Synetic\JanusAB\Variation\ExperimentInterface $experiment
   *   The experiment to be used in storage.
   * @param \Synetic\JanusAB\Variation\VariationInterface $variation
   *   The chosen variation.
   * @param string $userId
   *   The user id for storage.
   *
   * @return ?ResponseInterface
   *   Either null or the Response, depending on request success.
   */
  public function doTrafficRequest(
    ExperimentInterface $experiment,
    VariationInterface $variation,
    string $userId
  ): ?ResponseInterface;

  /**
   * Create and send a success request to the configured success endpoint.
   *
   * This is a Sync request, but is as fire and forget as possible.
   * If the request is successful, a Response is returned.
   * If it fails for any reason it returns null.
   *
   * Note that even when a response is returned, it may not be a status 200
   * response.
   *
   * @param \Synetic\JanusAB\Variation\ExperimentInterface $experiment
   *   The experiment to be used in storage.
   * @param \Synetic\JanusAB\Variation\VariationInterface $variation
   *   The chosen variation.
   * @param string $userId
   *   The user id for storage.
   *
   * @return ?ResponseInterface
   *   Either null or the Response, depending on request success.
   */
  public function doSuccessRequest(
    ExperimentInterface $experiment,
    VariationInterface $variation,
    string $userId
  ): ?ResponseInterface;

}
