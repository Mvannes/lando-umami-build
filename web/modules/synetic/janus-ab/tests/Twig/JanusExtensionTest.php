<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Twig;

use Drupal\Core\Render\RendererInterface;
use Drupal\janus_ab\JavaScript\LibraryAttacher;
use PHPUnit\Framework\TestCase;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\ExperimentInterface;
use Synetic\JanusAB\Variation\VariationInterface;
use Synetic\JanusAB\Variation\VariationPickerInterface;

/**
 * @covers \Drupal\janus_ab\Twig\JanusExtension
 */
class JanusExtensionTest extends TestCase {

  /**
   * The extension under test.
   *
   * @var \Drupal\janus_ab\Twig\JanusExtension
   */
  private $extension;

  /**
   * Mocked config object to avoid work.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * Mocked VariationPicker to avoid randomness.
   *
   * @var \Synetic\JanusAB\Variation\VariationPickerInterface
   */
  private $variationPicker;

  /**
   * Mocked renderer to do assertations with.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * Mocked library attacher because its not under test.
   *
   * @var \Drupal\janus_ab\JavaScript\LibraryAttacher
   */
  private $libraryAttacher;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->config          = $this->prophesize(ABConfigInterface::class);
    $this->variationPicker = $this->prophesize(VariationPickerInterface::class);
    $this->renderer        = $this->prophesize(RendererInterface::class);
    $this->libraryAttacher = $this->prophesize(LibraryAttacher::class);
    $this->extension       = new JanusExtension(
      $this->variationPicker->reveal(),
      $this->config->reveal(),
      $this->renderer->reveal(),
      $this->libraryAttacher->reveal()
    );
  }

  /**
   * Test if the proper functions are registered.
   */
  public function testGetFunctions(): void {
    $functions = [
      new \Twig_SimpleFunction(
        'attachTrafficLibraryForExperimentId',
        [$this->extension, 'attachTrafficLibraryForExperimentId']
      ),
      new \Twig_SimpleFunction(
        'attachSuccessLibraryForExperimentId',
        [$this->extension, 'attachSuccessLibraryForExperimentId']
      ),
      new \Twig_SimpleFunction(
        'attachTrafficEventLibraryForExperimentId',
        [$this->extension, 'attachTrafficEventLibraryForExperimentId']
      ),
      new \Twig_SimpleFunction(
        'attachSuccessEventLibraryForExperimentId',
        [$this->extension, 'attachSuccessEventLibraryForExperimentId']
      ),
      new \Twig_SimpleFunction(
        'hasActiveExperimentWithId',
        [$this->extension, 'hasActiveExperimentWithId']
      ),
      new \Twig_SimpleFunction(
        'getActiveExperimentById',
        [$this->extension, 'getActiveExperimentById']
      ),
      new \Twig_SimpleFunction(
        'pickVariationForExperiment',
        [$this->extension, 'pickVariationForExperiment']
      ),
    ];

    self::assertEquals($functions, $this->extension->getFunctions());
  }

  /**
   * Test the attachTrafficLibraryForExperimentId wrapper.
   */
  public function testAttachTrafficLibraryForExperimentId(): void {
    $this->libraryAttacher->attachTrafficLibraryForExperimentId(
      [],
      'bla'
    )->willReturn([])->shouldBeCalled();
    $this->renderer->render([])->shouldBeCalled();
    $this->extension->attachTrafficLibraryForExperimentId('bla');
  }

  /**
   * Test the attachSuccessLibraryForExperimentId wrapper.
   */
  public function testAttachSuccessLibraryForExperimentId(): void {
    $this->libraryAttacher->attachSuccessLibraryForExperimentId(
      [],
      'bla'
    )->willReturn([])->shouldBeCalled();
    $this->renderer->render([])->shouldBeCalled();
    $this->extension->attachSuccessLibraryForExperimentId('bla');
  }

  /**
   * Test the attachTrafficEventLibraryForExperimentId wrapper.
   */
  public function testAttachTrafficEventLibraryForExperimentId(): void {
    $this->libraryAttacher->attachTrafficEventLibraryForExperimentId(
      [],
      'sel',
      'ev',
      'bla'
    )->willReturn([]);
    $this->renderer->render([])->shouldBeCalled();
    $this->extension->attachTrafficEventLibraryForExperimentId(
      'sel',
      'ev',
      'bla'
    );
  }

  /**
   * Test the attachSuccessEventLibraryForExperimentId wrapper.
   */
  public function testAttachSuccessEventLibraryForExperimentId(): void {
    $this->libraryAttacher->attachSuccessEventLibraryForExperimentId(
      [],
      'sel',
      'ev',
      'bla'
    )->willReturn([]);
    $this->renderer->render([])->shouldBeCalled();
    $this->extension->attachSuccessEventLibraryForExperimentId(
      'sel',
      'ev',
      'bla'
    );
  }

  /**
   * Test the hasActiveExperimentWithId wrapper.
   */
  public function testHasActiveExperimentWithId(): void {
    $this->config->hasActiveExperimentWithId('id')->willReturn(TRUE);
    self::assertTrue($this->extension->hasActiveExperimentWithId('id'));
  }

  /**
   * Test the getActiveExperimentById wrapper.
   */
  public function testGetActiveExperimentWithId(): void {
    $experiment = $this->prophesize(ExperimentInterface::class)->reveal();
    $this->config->getActiveExperimentById('id')->willReturn($experiment);
    self::assertSame(
      $experiment,
      $this->extension->getActiveExperimentById('id')
    );
  }

  /**
   * Test the pickVariationForExperiment wrapper.
   */
  public function testPickVariationForExperiment(): void {
    $experiment = $this->prophesize(ExperimentInterface::class)->reveal();
    $this->config->getActiveExperimentById('id')
      ->willReturn($experiment);

    $variation = $this->prophesize(VariationInterface::class)->reveal();
    $this->variationPicker->pickVariationForExperiment($experiment)
      ->willReturn($variation);

    self::assertSame(
      $variation,
      $this->extension->pickVariationForExperiment($experiment)
    );
  }

}
