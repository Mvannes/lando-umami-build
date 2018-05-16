<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Cookie;

use Synetic\JanusAB\Cookie\BasicCookieJar;
use Synetic\JanusAB\Cookie\CookieJarInterface;

/**
 * Factory wrapper that creates BasicCookieJar concrete objects.
 *
 * This factory makes use of the $_COOKIE superglobal to fill the created
 * cookie jar.
 */
class CookieJarFactory implements CookieJarFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function create(): CookieJarInterface {
    return new BasicCookieJar($_COOKIE);
  }

}
