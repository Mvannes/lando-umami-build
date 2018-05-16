<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Synetic\JanusAB\Logging\ABLoggerInterface;

/**
 * @covers \Drupal\janus_ab\Controller\LoggingController
 */
class LoggingControllerTest extends TestCase {

  /**
   * Mocked logger.
   *
   * @var \Synetic\JanusAB\Logging\ABLoggerInterface
   */
  private $abLogger;

  /**
   * The controller to test.
   *
   * @var \Drupal\janus_ab\Controller\LoggingController
   */
  private $loggingController;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->abLogger          = $this->prophesize(ABLoggerInterface::class);
    $this->loggingController = new LoggingController($this->abLogger->reveal());

  }

  /**
   * Tests if we can create the loggingController.
   */
  public function testCreate(): void {

    $container = $this->prophesize(ContainerInterface::class);
    $container->get('janus_ab.ab_logger')
      ->willReturn($this->abLogger->reveal());

    self::assertEquals(
      $this->loggingController,
      LoggingController::create($container->reveal())
    );

  }

  /**
   * Tests if our logTraffic function does what we expect, using a mock logger.
   */
  public function testLogTrafficAction(): void {
    $request = new Request();
    $request->request->set('experiment', 'idhere');
    $request->request->set('variation', '1');
    $request->request->set('userId', 'user');

    $this->abLogger->logTraffic('idhere', '1', 'user')->shouldBeCalled();
    self::assertInstanceOf(
      Response::class,
      $this->loggingController->logTrafficAction($request)
    );
  }

  /**
   * Tests if our logSuccess function does what we expect, using a mock logger.
   */
  public function testLogSuccessAction(): void {
    $request = new Request();
    $request->request->set('experiment', 'idhere');
    $request->request->set('variation', '1');
    $request->request->set('userId', 'user');

    $this->abLogger->logSuccess('idhere', '1', 'user')->shouldBeCalled();
    self::assertInstanceOf(
      Response::class,
      $this->loggingController->logSuccessAction($request)
    );
  }

}
