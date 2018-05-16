<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Cookie;

use Synetic\JanusAB\Cookie\CookieJarInterface;

/**
 * Factory wrapper that creates BasicCookieJar concrete objects.
 */
interface CookieJarFactoryInterface {

  /**
   * Create a CookieJarInterface and fill it with the proper cookies.
   *
   * @return \Synetic\JanusAB\Cookie\CookieJarInterface
   *   The created CookieJarInterface object.
   */
  public function create(): CookieJarInterface;

}
