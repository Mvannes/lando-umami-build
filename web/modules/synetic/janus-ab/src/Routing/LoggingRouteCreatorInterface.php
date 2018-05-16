<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Routing;

/**
 * Creates dynamic logging related routes.
 */
interface LoggingRouteCreatorInterface {

  /**
   * Create and return the dynamically created routes.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   The created routes.
   */
  public function routes(): array;

}
