<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Routing;

use Drupal\janus_ab\Controller\LoggingController;
use Symfony\Component\Routing\Route;
use Synetic\JanusAB\Config\ABConfigInterface;

/**
 * Creates dynamic logging related routes.
 *
 * Routes are created based on the configuration found
 * in ABConfigInterface implementations.
 */
class LoggingRouteCreator implements LoggingRouteCreatorInterface {

  /**
   * The config used for the configured traffic and success urls.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * LoggingRouteCreator constructor.
   *
   * @param \Synetic\JanusAB\Config\ABConfigInterface $config
   *   Config object used for traffic and success url access.
   */
  public function __construct(ABConfigInterface $config) {
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function routes(): array {
    $routes = [];

    $trafficUrl = $this->config->getTrafficUrl();
    // Only create a route if the given url is internal and starts with '/'.
    if (0 === strpos($trafficUrl, '/')) {
      $trafficRoute = new Route(
        $trafficUrl,
        [
          '_controller' => LoggingController::class . '::logTrafficAction',
        ],
        [
          '_permission' => 'access content',
        ]
      );
      $trafficRoute->setMethods(['POST']);
      $routes['janus.logTraffic'] = $trafficRoute;
    }

    $successUrl = $this->config->getSuccessUrl();
    // Only create a route if the given url is internal and starts with '/'.
    if (0 === strpos($successUrl, '/')) {
      $successRoute = new Route(
        $successUrl,
        [
          '_controller' => LoggingController::class . '::logSuccessAction',
        ],
        [
          '_permission' => 'access content',
        ]
      );
      $successRoute->setMethods(['POST']);
      $routes['janus.logSuccess'] = $successRoute;
    }

    return $routes;
  }

}
