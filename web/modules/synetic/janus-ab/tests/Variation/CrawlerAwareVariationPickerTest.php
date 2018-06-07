<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Variation;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Cookie\CookieJarInterface;
use Synetic\JanusAB\Variation\ExperimentInterface;
use Synetic\JanusAB\Variation\Variation;

/**
 * @covers \Drupal\janus_ab\Variation\CrawlerAwareVariationPicker
 */
class CrawlerAwareVariationPickerTest extends TestCase {

  /**
   * DataProvider that provides bot user agents.
   *
   * @return array
   *   The array of provided test cases
   */
  public function crawlerUserAgentProvider(): array {
    return [
      [
        'Mozilla/5.0 (compatible; Googlebot/2.1; 
        +http://www.google.com/bot.html)',
      ],
      [
        'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) 
        AppleWebKit/537.36 (KHTML, like Gecko) 
        Chrome/41.0.2272.96 Mobile Safari/537.36
         (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
      ],
      [
        '(compatible; Mediapartners-Google/2.1; 
        +http://www.google.com/bot.html)',
      ],
      [
        'Mozilla/5.0 (compatible; 
        MJ12bot/v1.4.5; http://www.majestic12.co.uk/bot.php?+)',
      ],
      [
        'Mozilla/5.0 (compatible; Yahoo! Slurp; 
        http://help.yahoo.com/help/us/ysearch/slurp)',
      ],
      [
        'Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X) 
        AppleWebKit/537.51.1 (KHTML, like Gecko) 
        Version/7.0 Mobile/11A465 Safari/9537.53 
        (compatible; bingbot/2.0; http://www.bing.com/bingbot.htm)',
      ],
      [
        'Mozilla/5.0 (compatible; Bingbot/2.0; 
        +http://www.bing.com/bingbot.htm)',
      ],
      [
        'DuckDuckBot/1.0; (+http://duckduckgo.com/duckduckbot.html)',
      ],
      [
        'Mozilla/5.0 (compatible; Baiduspider/2.0;
         +http://www.baidu.com/search/spider.html)',
      ],
      [
        'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)',
      ],
      [
        'Sogou Pic Spider/3.0
        ( http://www.sogou.com/docs/help/webmasters.htm#07)',
      ],
      [
        'Sogou head spider/3.0
        ( http://www.sogou.com/docs/help/webmasters.htm#07)',
      ],
      [
        'Sogou web spider/4.0
        (+http://www.sogou.com/docs/help/webmasters.htm#07)',
      ],
      [
        'Sogou Orion spider/3.0
        ( http://www.sogou.com/docs/help/webmasters.htm#07)',
      ],
      [
        'Sogou-Test-Spider/4.0 (compatible; MSIE 5.5; Windows 98)',
      ],
      [
        ' Mozilla/5.0 (compatible; Konqueror/3.5; Linux) 
        KHTML/3.5.5 (like Gecko) (Exabot-Thumbnails)',
      ],
      [
        ' Mozilla/5.0 (compatible; Exabot/3.0; 
        +http://www.exabot.com/go/robot)',
      ],
      [
        'facebot',
      ],
      [
        'facebookexternalhit/1.0 
        (+http://www.facebook.com/externalhit_uatext.php)',
      ],
      [
        'facebookexternalhit/1.1 
        (+http://www.facebook.com/externalhit_uatext.php)',
      ],
      [
        'ia_archiver 
        (+http://www.alexa.com/site/help/webmasters; crawler@alexa.com)',
      ],
    ];
  }

  /**
   * Test the picking of variations when the user is in fact a bot.
   *
   * This should always return the first variation configured in an experiment.
   *
   * @param string $userAgent
   *   The user agent to check. Is always a common bot.
   *
   * @dataProvider crawlerUserAgentProvider
   */
  public function testPickVariationForExperimentWhenCrawler(
    string $userAgent
  ): void {
    $cookieJar     = $this->prophesize(CookieJarInterface::class);
    $config        = $this->prophesize(ABConfigInterface::class);
    $requestStack  = $this->prophesize(RequestStack::class);
    $crawlerDetect = new CrawlerDetect();
    $request       = new Request();
    $request->headers->set('User-Agent', $userAgent);
    $requestStack->getCurrentRequest()->willReturn($request);
    $cookieJar->getCookie(Argument::any())->shouldNotBeCalled();
    $picker = new CrawlerAwareVariationPicker(
      $cookieJar->reveal(),
      $config->reveal(),
      $requestStack->reveal(),
      $crawlerDetect
    );

    $experiment = $this->prophesize(ExperimentInterface::class);
    $variation = new Variation('0', 'a');
    $notThisVariation = new Variation('1', 'b');
    $experiment->getVariations()->willReturn([$variation, $notThisVariation]);

    $result = $picker->pickVariationForExperiment($experiment->reveal());

    self::assertSame($variation, $result);
  }

  /**
   * Test the picking of variations when the user is in fact a bot.
   *
   * This should always return the first variation configured in an experiment.
   *
   * @param string $userAgent
   *   The user agent to check. Is always a common bot.
   *
   * @dataProvider crawlerUserAgentProvider
   */
  public function testPickVariationForExperimentAndRequestWhenCrawler(
    string $userAgent
  ): void {
    $cookieJar     = $this->prophesize(CookieJarInterface::class);
    $config        = $this->prophesize(ABConfigInterface::class);
    $requestStack  = $this->prophesize(RequestStack::class);
    $crawlerDetect = new CrawlerDetect();
    $request       = new Request();
    $request->headers->set('User-Agent', $userAgent);
    $cookieJar->getCookie(Argument::any())->shouldNotBeCalled();
    $picker = new CrawlerAwareVariationPicker(
      $cookieJar->reveal(),
      $config->reveal(),
      $requestStack->reveal(),
      $crawlerDetect
    );

    $experiment = $this->prophesize(ExperimentInterface::class);
    $variation = new Variation('0', 'a');
    $notThisVariation = new Variation('1', 'b');
    $experiment->getVariations()->willReturn([$variation, $notThisVariation]);

    $result = $picker->pickVariationForExperimentAndRequest(
      $experiment->reveal(),
      $request
    );

    self::assertSame($variation, $result);
  }

}
