<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Cookie;

use PHPUnit\Framework\TestCase;
use Synetic\JanusAB\Cookie\BasicCookieJar;

/**
 * @covers \Drupal\janus_ab\Cookie\CookieJarFactory
 */
class CookieJarFactoryTest extends TestCase {

  /**
   * Tests if the cookie jar is correctly created.
   */
  public function testCreate(): void {
    $_COOKIE          = ['cool' => 'cookie'];
    $cookieJarFactory = new CookieJarFactory();
    $cookieJar        = new BasicCookieJar(['cool' => 'cookie']);

    self::assertEquals($cookieJar, $cookieJarFactory->create());

  }

}
