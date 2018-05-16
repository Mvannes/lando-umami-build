<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\JavaScript;

use PHPUnit\Framework\TestCase;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\Experiment;
use Synetic\JanusAB\Variation\Variation;
use Synetic\JanusAB\Variation\VariationPickerInterface;

/**
 * @covers \Drupal\janus_ab\JavaScript\LibraryAttacher
 */
class LibraryAttacherTest extends TestCase {

  /**
   * Mocked config object.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * Mocked variationPicker.
   *
   * @var \Synetic\JanusAB\Variation\VariationPickerInterface
   */
  private $picker;

  /**
   * The library attacher under test.
   *
   * @var \Drupal\janus_ab\JavaScript\LibraryAttacher
   */
  private $attacher;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->config = $this->prophesize(ABConfigInterface::class);
    $this->picker = $this->prophesize(VariationPickerInterface::class);
    $this->attacher = new LibraryAttacher(
      $this->config->reveal(),
      $this->picker->reveal()
    );
  }

  /**
   * Test the attachSuccessLibraryForExperimentId function's result.
   *
   * Checks if no library is attached when no experiment is active.
   */
  public function testAttachSuccessLibraryForExperimentIdNoExperiment(): void {
    $this->config->hasActiveExperimentWithId('id')->willReturn(FALSE);
    self::assertEmpty(
      $this->attacher->attachSuccessLibraryForExperimentId([], 'id')
    );
  }

  /**
   * Test the attachSuccessLibraryForExperimentId function's result.
   */
  public function testAttachSuccessLibraryForExperiment() {
    $this->config->hasActiveExperimentWithId('id')->willReturn(TRUE);
    $variation = new Variation('0', 'var');
    $experiment = new Experiment(
      'id',
      new \DateTime(),
      new \DateTime(),
      [$variation]
    );
    $this->config->getSuccessUrl()->willReturn('/succcess');
    $this->config->getCookieNameForExperimentId('id')->willReturn('bla_id');
    $this->config->getActiveExperimentById('id')->willReturn($experiment);
    $this->picker->pickVariationForExperiment($experiment)
      ->willReturn($variation);

    $expected = [
      '#attached' => [
        'library' => ['janus_ab/success'],
        'drupalSettings' => [
          'janus_ab' => [
            'success' => [
              'experimentId' => 'id',
              'variationId'  => '0',
              'successUrl'   => '/succcess',
              'userIdCookie' => 'bla_id_ID',
            ],
          ],
        ],
      ],
    ];
    self::assertEquals(
      $expected,
      $this->attacher->attachSuccessLibraryForExperimentId([], 'id')
    );
  }

  /**
   * Test the attachTrafficLibraryForExperimentId function's result.
   *
   * Checks if no library is attached when no experiment is active.
   */
  public function testAttachTrafficLibraryForExperimentIdNoExperiment() {
    $this->config->hasActiveExperimentWithId('id')->willReturn(FALSE);
    self::assertEmpty(
      $this->attacher->attachTrafficLibraryForExperimentId([], 'id')
    );
  }

  /**
   * Test the attachTrafficLibraryForExperimentId function's result.
   */
  public function testAttachTrafficLibraryForExperiment() {
    $this->config->hasActiveExperimentWithId('id')->willReturn(TRUE);
    $variation = new Variation('0', 'var');
    $experiment = new Experiment(
      'id',
      new \DateTime(),
      new \DateTime(),
      [$variation]
    );
    $this->config->getTrafficUrl()->willReturn('/traffic');
    $this->config->getCookieNameForExperimentId('id')->willReturn('bla_id');
    $this->config->getActiveExperimentById('id')->willReturn($experiment);
    $this->picker->pickVariationForExperiment($experiment)
      ->willReturn($variation);

    $expected = [
      '#attached' => [
        'library' => ['janus_ab/traffic'],
        'drupalSettings' => [
          'janus_ab' => [
            'traffic' => [
              'experimentId' => 'id',
              'variationId'  => '0',
              'trafficUrl'   => '/traffic',
              'userIdCookie' => 'bla_id_ID',
            ],
          ],
        ],
      ],
    ];
    self::assertEquals(
      $expected,
      $this->attacher->attachTrafficLibraryForExperimentId([], 'id')
    );
  }

  /**
   * Test the attachSuccessEventLibraryForExperimentId function's result.
   *
   * Checks if no library is attached when no experiment is active.
   */
  public function testAttachSuccessEventLibraryForExperimentIdNoExperiment() {
    $this->config->hasActiveExperimentWithId('id')->willReturn(FALSE);
    self::assertEmpty(
      $this->attacher->attachSuccessEventLibraryForExperimentId(
        [],
        'selec',
        'event',
        'id'
      )
    );
  }

  /**
   * Test the attachSuccessEventLibraryForExperimentId function's result.
   */
  public function testAttachSuccessEventLibraryForExperimentId() {
    $this->config->hasActiveExperimentWithId('id')->willReturn(TRUE);
    $variation = new Variation('0', 'var');
    $experiment = new Experiment(
      'id',
      new \DateTime(),
      new \DateTime(),
      [$variation]
    );
    $this->config->getSuccessUrl()->willReturn('/success');
    $this->config->getCookieNameForExperimentId('id')->willReturn('bla_id');
    $this->config->getActiveExperimentById('id')->willReturn($experiment);
    $this->picker->pickVariationForExperiment($experiment)
      ->willReturn($variation);

    $expected = [
      '#attached' => [
        'library' => ['janus_ab/event'],
        'drupalSettings' => [
          'janus_ab' => [
            'event' => [
              'experimentId' => 'id',
              'variationId'  => '0',
              'postUrl'      => '/success',
              'selector'     => 'selec',
              'event'        => 'event',
              'userIdCookie' => 'bla_id_ID',
            ],
          ],
        ],
      ],
    ];
    self::assertEquals(
      $expected,
      $this->attacher->attachSuccessEventLibraryForExperimentId(
        [],
        'selec',
        'event',
        'id'
      )
    );
  }

  /**
   * Test the attachTrafficEventLibraryForExperimentId function's result.
   *
   * Checks if no library is attached when no experiment is active.
   */
  public function testAttachTrafficEventLibraryForExperimentIdNoExperiment() {
    $this->config->hasActiveExperimentWithId('id')->willReturn(FALSE);
    self::assertEmpty(
      $this->attacher->attachTrafficEventLibraryForExperimentId(
        [],
        'selec',
        'event',
        'id'
      )
    );
  }

  /**
   * Test the attachTrafficEventLibraryForExperimentId function's result.
   */
  public function testAttachTrafficEventLibraryForExperimentId() {
    $this->config->hasActiveExperimentWithId('id')->willReturn(TRUE);
    $variation = new Variation('0', 'var');
    $experiment = new Experiment(
      'id',
      new \DateTime(),
      new \DateTime(),
      [$variation]
    );
    $this->config->getTrafficUrl()->willReturn('/traffic');
    $this->config->getCookieNameForExperimentId('id')->willReturn('bla_id');
    $this->config->getActiveExperimentById('id')->willReturn($experiment);
    $this->picker->pickVariationForExperiment($experiment)
      ->willReturn($variation);

    $expected = [
      '#attached' => [
        'library' => ['janus_ab/event'],
        'drupalSettings' => [
          'janus_ab' => [
            'event' => [
              'experimentId' => 'id',
              'variationId'  => '0',
              'postUrl'      => '/traffic',
              'selector'     => 'selec',
              'event'        => 'event',
              'userIdCookie' => 'bla_id_ID',
            ],
          ],
        ],
      ],
    ];
    self::assertEquals(
      $expected,
      $this->attacher->attachTrafficEventLibraryForExperimentId(
        [],
        'selec',
        'event',
        'id'
      )
    );
  }

}
