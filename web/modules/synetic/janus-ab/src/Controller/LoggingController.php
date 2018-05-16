<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Synetic\JanusAB\Logging\ABLoggerInterface;

/**
 * Controller responsible for saving traffic and success hits to a queue.
 */
class LoggingController extends ControllerBase implements LoggingControllerInterface {

  /**
   * The logger used to send correct data to the backend.
   *
   * @var \Synetic\JanusAB\Logging\ABLoggerInterface
   */
  private $abLogger;

  /**
   * LoggingController constructor.
   *
   * @param \Synetic\JanusAB\Logging\ABLoggerInterface $abLogger
   *   The logger used for sending data to the backend.
   */
  public function __construct(ABLoggerInterface $abLogger) {
    $this->abLogger = $abLogger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('janus_ab.ab_logger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function logTrafficAction(Request $request): Response {
    $experiment = $request->get('experiment');
    $variation  = $request->get('variation');
    $userId     = $request->get('userId');

    $this->abLogger->logTraffic($experiment, $variation, $userId);
    return new Response();
  }

  /**
   * {@inheritdoc}
   */
  public function logSuccessAction(Request $request): Response {
    $experiment = $request->get('experiment');
    $variation  = $request->get('variation');
    $userId     = $request->get('userId');

    $this->abLogger->logSuccess($experiment, $variation, $userId);
    return new Response();
  }

}
