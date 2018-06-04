<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Routing;

use Drupal\janus_ab\Controller\LoggingController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Synetic\JanusAB\Config\ABConfigInterface;


/**
 * @covers \Drupal\janus_ab\Routing\LoggingRouteCreator
 */
class LoggingRouteCreatorTest extends TestCase {

  /**
   * Mocked config for urls.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * Creator under test.
   *
   * @var \Drupal\janus_ab\Routing\LoggingRouteCreator
   */
  private $routeCreator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->config       = $this->prophesize(ABConfigInterface::class);
    $this->routeCreator = new LoggingRouteCreator($this->config->reveal());
  }

  /**
   * Test if routes are created for external routes.
   */
  public function testRoutesWithoutInternalRoutes(): void {
    $this->config->getTrafficUrl()->willReturn('https://google.com');
    $this->config->getSuccessUrl()->willReturn('https://google.com');

    self::assertEmpty($this->routeCreator->routes());
  }

  /**
   * Test if routes are created for internal routes.
   */
  public function testRoutes(): void {
    $this->config->getTrafficUrl()->willReturn('/traffic');
    $this->config->getSuccessUrl()->willReturn('/success');
    $trafficRoute = new Route(
      '/traffic',
      [
        '_controller' => LoggingController::class . '::logTrafficAction',
      ],
      [
        '_permission' => 'access content',
      ]
    );
    $trafficRoute->setMethods(['POST']);
    $successRoute = new Route(
      '/success',
      [
        '_controller' => LoggingController::class . '::logSuccessAction',
      ],
      [
        '_permission' => 'access content',
      ]
    );
    $successRoute->setMethods(['POST']);
    $expected = [
      'janus.logTraffic' => $trafficRoute,
      'janus.logSuccess' => $successRoute,
    ];

    self::assertEquals($expected, $this->routeCreator->routes());
  }

}
