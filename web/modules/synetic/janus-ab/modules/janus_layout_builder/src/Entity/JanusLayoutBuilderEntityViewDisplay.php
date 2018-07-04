<?php

declare(strict_types = 1);

namespace Drupal\janus_layout_builder\Entity;

use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;

/**
 * Extension on the layout builder's entity view display to support JanusAB.
 *
 * Adds knowledge about configured experiments to choose which sections get
 * displayed.
 */
class JanusLayoutBuilderEntityViewDisplay extends LayoutBuilderEntityViewDisplay {

  /**
   * Used in checking for active experiments.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $abConfig;

  /**
   * Chooses variations from active experiments.
   *
   * @var \Synetic\JanusAB\Variation\VariationPickerInterface
   */
  private $variationPicker;

  /**
   * Used to expose sections when in an admin context.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatcher;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    // Can't get these from the container through regular constructor DI
    // due to how this class is constructed by Drupal.
    $this->abConfig        = \Drupal::service('janus_ab.ab_config');
    $this->variationPicker = \Drupal::service('janus_ab.variation_picker');
    $this->routeMatcher    = \Drupal::routeMatch();

    parent::__construct($values, $entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function getSections() {
    $sections = parent::getSections();
    $route    = $this->routeMatcher->getRouteObject();

    // Catch cases where there is no need to handle anything for experiments.
    if (NULL === $route ||
      TRUE === $route->getOption('_admin_route') ||
      $route->hasRequirement('_field_ui_view_mode_access')) {
      return $sections;
    }

    $displayableSections = [];
    foreach ($sections as $section) {
      $layoutSettings = $section->getLayoutSettings();
      if (!isset($layoutSettings['experiment'], $layoutSettings['variations'])) {
        $displayableSections[] = $section;
        continue;
      }

      $experimentId        = $layoutSettings['experiment'];
      $availableVariations = $layoutSettings['variations'];

      // Shouldn't display the variation if it's experiment isn't active.
      if (!$this->abConfig->hasActiveExperimentWithId($experimentId)) {
        continue;
      }

      // No specific variations have been selected, assume it should always be
      // displayed.
      if (empty($availableVariations)) {
        $displayableSections[] = $section;
        continue;
      }

      $experiment = $this->abConfig->getActiveExperimentById($experimentId);
      $variation  = $this->variationPicker->pickVariationForExperiment(
        $experiment
      );

      if (in_array($variation->getId(), $availableVariations)) {
        $displayableSections[] = $section;
      }

    }
    return $displayableSections;
  }

}
