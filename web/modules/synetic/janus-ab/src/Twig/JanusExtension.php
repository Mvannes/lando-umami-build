<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Twig;

use Drupal\Core\Render\RendererInterface;
use Drupal\janus_ab\JavaScript\LibraryAttacher;
use Drupal\janus_ab\JavaScript\LibraryAttacherInterface;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\ExperimentInterface;
use Synetic\JanusAB\Variation\VariationInterface;
use Synetic\JanusAB\Variation\VariationPickerInterface;

/**
 * Twig extension that allows use of JanusAB functions in Twig.
 */
class JanusExtension extends \Twig_Extension
{
  /**
   * Variation picker for picking variations.
   *
   * @var \Synetic\JanusAB\Variation\VariationPickerInterface
   */
  private $variationPicker;

  /**
   * The Janus configuration.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * Drupal renderer to render some additional JavaScript.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * The library attacher that lets us render correct JavaScript from twig.
   *
   * @var \Drupal\janus_ab\JavaScript\LibraryAttacherInterface
   */
  private $libraryAttacher;

  /**
   * JanusExtension constructor.
   *
   * @param \Synetic\JanusAB\Variation\VariationPickerInterface $variationPicker
   *   The variationPicker to use.
   * @param \Synetic\JanusAB\Config\ABConfigInterface $config
   *   The Janus configuration to use.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer to render small amounts of javascript.
   * @param \Drupal\janus_ab\JavaScript\LibraryAttacherInterface $libraryAttacher
   *   The library attacher that attaches our hit and success libraries.
   */
  public function __construct(
    VariationPickerInterface $variationPicker,
    ABConfigInterface $config,
    RendererInterface $renderer,
    LibraryAttacherInterface $libraryAttacher
  ) {
    $this->variationPicker = $variationPicker;
    $this->config          = $config;
    $this->renderer        = $renderer;
    $this->libraryAttacher = $libraryAttacher;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction(
        'attachTrafficLibraryForExperimentId',
        [$this, 'attachTrafficLibraryForExperimentId']
      ),
      new \Twig_SimpleFunction(
        'attachSuccessLibraryForExperimentId',
        [$this, 'attachSuccessLibraryForExperimentId']
      ),
      new \Twig_SimpleFunction(
        'attachTrafficEventLibraryForExperimentId',
        [$this, 'attachTrafficEventLibraryForExperimentId']
      ),
      new \Twig_SimpleFunction(
        'attachSuccessEventLibraryForExperimentId',
        [$this, 'attachSuccessEventLibraryForExperimentId']
      ),
      new \Twig_SimpleFunction(
        'hasActiveExperimentWithId',
        [$this, 'hasActiveExperimentWithId']
      ),
      new \Twig_SimpleFunction(
        'getActiveExperimentById',
        [$this, 'getActiveExperimentById']
      ),
      new \Twig_SimpleFunction(
        'pickVariationForExperiment',
        [$this, 'pickVariationForExperiment']
      ),
    ];
  }

  /**
   * Attaches the traffic library for the given experiment id.
   *
   * If the experiment is not active, the original variables are returned as the
   * library can not be attached.
   *
   * @param string $experimentId
   *   The experiment to use when attaching the library.
   *
   * @throws \Exception
   */
  public function attachTrafficLibraryForExperimentId(string $experimentId): void {
    $attached = $this->libraryAttacher->attachTrafficLibraryForExperimentId(
      [],
      $experimentId
    );
    $this->renderer->render($attached);
  }

  /**
   * Attaches the success library for the given experiment id.
   *
   * If the experiment is not active, the original variables are returned as the
   * library can not be attached.
   *
   * @param string $experimentId
   *   The experiment to use when attaching the library.
   *
   * @throws \Exception
   */
  public function attachSuccessLibraryForExperimentId(string $experimentId): void {
    $attached = $this->libraryAttacher->attachSuccessLibraryForExperimentId(
      [],
      $experimentId
    );
    $this->renderer->render($attached);
  }

  /**
   * Attaches the traffic library for the given experiment id.
   *
   * If the experiment is not active, the original variables are returned as the
   * library can not be attached.
   *
   * @param string $selector
   *   A valid JQuery selector.
   * @param string $event
   *   A valid JQuery event.
   * @param string $experimentId
   *   The experiment to use when attaching the library.
   *
   * @throws \Exception
   */
  public function attachTrafficEventLibraryForExperimentId(string $selector, string $event, string $experimentId): void {
    $attached = $this->libraryAttacher->attachTrafficEventLibraryForExperimentId(
      [],
      $selector,
      $event,
      $experimentId
    );
    $this->renderer->render($attached);
  }

  /**
   * Attaches the success library for the given experiment id.
   *
   * If the experiment is not active, the original variables are returned as the
   * library can not be attached.
   *
   * @param string $selector
   *   A valid JQuery selector.
   * @param string $event
   *   A valid JQuery event.
   * @param string $experimentId
   *   The experiment to use when attaching the library.
   *
   * @throws \Exception
   */
  public function attachSuccessEventLibraryForExperimentId(string $selector, string $event, string $experimentId): void {
    $attached = $this->libraryAttacher->attachSuccessEventLibraryForExperimentId(
      [],
      $selector,
      $event,
      $experimentId
    );
    $this->renderer->render($attached);
  }

  /**
   * Does an active experiment with the given id exist?
   *
   * @param string $experimentId
   *   The experiment to search for.
   *
   * @return bool
   *   If the experiment with the id exists and is active.
   */
  public function hasActiveExperimentWithId(string $experimentId): bool {
    return $this->config->hasActiveExperimentWithId($experimentId);
  }

  /**
   * Get the currently active Experiment with the given id.
   *
   * If no experiment with the given id exists, or if it is not active,
   * an exception is thrown.
   *
   * @param string $experimentId
   *   The experiment to search for.
   *
   * @return \Synetic\JanusAB\Variation\ExperimentInterface
   *   The active experiment with the given id.
   *
   * @throws \Synetic\JanusAB\Config\Exception\ActiveExperimentNotConfiguredException
   */
  public function getActiveExperimentById(
    string $experimentId
  ): ExperimentInterface {
    return $this->config->getActiveExperimentById($experimentId);
  }

  /**
   * Get a variation from the given experiment id.
   *
   * @param \Synetic\JanusAB\Variation\ExperimentInterface $experiment
   *   The experiment to pick from.
   *
   * @return \Synetic\JanusAB\Variation\VariationInterface
   *   The chosen variation.
   */
  public function pickVariationForExperiment(
    ExperimentInterface $experiment
  ): VariationInterface {
    return $this->variationPicker->pickVariationForExperiment($experiment);
  }

}
