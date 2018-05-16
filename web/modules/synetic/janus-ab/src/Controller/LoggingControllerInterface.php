<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller responsible for saving traffic and success hits to a queue.
 */
interface LoggingControllerInterface {

  /**
   * Controller route for handling traffic requests.
   *
   * Designed for "Fire-and-forget" requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object representing the user's request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response. Always returns status 200, empty body.
   */
  public function logTrafficAction(Request $request): Response;

  /**
   * Controller route for handling success requests.
   *
   * Designed for "Fire-and-forget" requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object representing the user's request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response. Always returns status 200, empty body.
   */
  public function logSuccessAction(Request $request): Response;
}
