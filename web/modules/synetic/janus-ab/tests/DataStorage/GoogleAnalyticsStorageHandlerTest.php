<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\DataStorage;

use PHPUnit\Framework\TestCase;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\DataStorage\StorageData;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * @covers \Drupal\janus_ab\DataStorage\GoogleAnalyticsStorageHandler
 */
class GoogleAnalyticsStorageHandlerTest extends TestCase {

  /**
   * The analytics handler under test.
   *
   * @var \Drupal\janus_ab\DataStorage\GoogleAnalyticsStorageHandler
   */
  private $analyticsHandler;

  /**
   * A mocked analytics object to prevent sending data to GA in tests.
   *
   * @var \TheIconic\Tracking\GoogleAnalytics\Analytics
   */
  private $analytics;

  /**
   * Mocked ABConfig object for its analytics id.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $abConfig;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->analytics        = $this->prophesize(Analytics::class);
    $this->abConfig         = $this->prophesize(ABConfigInterface::class);
    $this->analyticsHandler = new GoogleAnalyticsStorageHandler(
      $this->analytics->reveal(),
      $this->abConfig->reveal()
    );
  }

  /**
   * Test if our handler's name is what we expect it to be.
   */
  public function testGetName(): void {
    self::assertSame(
      GoogleAnalyticsStorageHandler::class,
      $this->analyticsHandler->getName()
    );
  }

  /**
   * Test storing basic data.
   */
  public function testStoreData(): void {
    $this->abConfig->getTrackingId()->willReturn('UA-1234567890');

    $data = new StorageData(
      'exp',
      'var',
      'usr',
      'traffic'
    );

    $this->analytics->setProtocolVersion('1')
      ->shouldBeCalled()
      ->willReturn($this->analytics->reveal());
    $this->analytics->setTrackingId('UA-1234567890')
      ->shouldBeCalled()
      ->willReturn($this->analytics->reveal());
    $this->analytics->setClientId('usr')
      ->shouldBeCalled()
      ->willReturn($this->analytics->reveal());
    $this->analytics->setExperimentId('exp')
      ->shouldBeCalled()
      ->willReturn($this->analytics->reveal());
    $this->analytics->setExperimentVariant('var')
      ->shouldBeCalled()
      ->willReturn($this->analytics->reveal());
    $this->analytics->setEventCategory('JanusAB')
      ->shouldBeCalled()
      ->willReturn($this->analytics->reveal());
    $this->analytics->setEventAction('traffic')
      ->shouldBeCalled()
      ->willReturn($this->analytics->reveal());
    $this->analytics->setAnonymizeIp(TRUE)
      ->shouldBeCalled()
      ->willReturn($this->analytics->reveal());
    $this->analytics->setUserAgentOverride('JanusAB - AB testing')
      ->shouldBeCalled()
      ->willReturn($this->analytics->reveal());
    $this->analytics->sendEvent()->shouldBeCalled();

    $this->analyticsHandler->storeData($data);
  }

}
