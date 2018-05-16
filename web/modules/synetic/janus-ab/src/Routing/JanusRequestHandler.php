<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Routing;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\ExperimentInterface;
use Synetic\JanusAB\Variation\VariationInterface;

/**
 * Fires requests to configured data collection endpoints.
 *
 * Wraps around manual request creation to ensure requests are correctly formed.
 */
class JanusRequestHandler implements JanusRequestHandlerInterface {

  /**
   * The janus configuration to use.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * Guzzle Client for firing requests.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $client;

  /**
   * JanusRequestHandler constructor.
   *
   * @param \Synetic\JanusAB\Config\ABConfigInterface $config
   *   ABConfig for access to the configured traffic and success urls.
   * @param \GuzzleHttp\ClientInterface $client
   *   Client is injected as a service to allow us to mock it.
   */
  public function __construct(
    ABConfigInterface $config,
    ClientInterface $client
  ) {
    $this->config = $config;
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public function doTrafficRequest(
    ExperimentInterface $experiment,
    VariationInterface $variation,
    string $userId
  ): ?ResponseInterface {
    $url = $this->config->getTrafficUrl();
    // If we have configured a local url, we should add our host to it.
    // Because Guzzle does not play nice with relative urls.
    if (0 === strpos($url, '/')) {
      $url = $_SERVER['HTTP_HOST'] . $url;
    }
    try {
      return $this->client->request(
        'POST',
        $url,
        [
          'form_params' => [
            'experiment' => $experiment->getId(),
            'variation'  => $variation->getId(),
            'userId'     => $userId,
          ],
          'http_errors' => FALSE,
          // Timeout if no response in 2 seconds.
          'timeout'     => 2,
        ]
      );
    }
    catch (\Exception $e) {
      // If any exception happens, we don't really care as this is as this is as
      // "Fire and forget" as possible.
      // So just return null.
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function doSuccessRequest(
    ExperimentInterface $experiment,
    VariationInterface $variation,
    string $userId
  ): ?ResponseInterface {
    $url = $this->config->getSuccessUrl();
    // If we have configured a local url, we should add our host to it.
    // Because Guzzle does not play nice with relative urls.
    if (0 === strpos($url, '/')) {
      $url = $_SERVER['HTTP_HOST'] . $url;
    }
    try {
      return $this->client->request(
        'POST',
        $url,
        [
          'form_params' => [
            'experiment' => $experiment->getId(),
            'variation'  => $variation->getId(),
            'userId'     => $userId,
          ],
          'http_errors' => FALSE,
          // Timeout if no response in 2 seconds.
          'timeout'     => 2,
        ]
      );
    }
    catch (\Exception $e) {
      // If any exception happens, we don't really care as this is as this is as
      // "Fire and forget" as possible.
      // So just return null.
      return NULL;
    }
  }

}
