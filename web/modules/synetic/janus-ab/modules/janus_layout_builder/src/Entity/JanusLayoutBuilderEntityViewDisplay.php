<?php

declare(strict_types = 1);

namespace Drupal\janus_layout_builder\Entity;

use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;

class JanusLayoutBuilderEntityViewDisplay extends LayoutBuilderEntityViewDisplay{

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

  public function __construct(array $values, $entity_type) {
    $this->abConfig        = \Drupal::service('janus_ab.ab_config');
    $this->variationPicker = \Drupal::service('janus_ab.variation_picker');
    $this->routeMatcher    = \Drupal::routeMatch();

    parent::__construct($values, $entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function getSections() {
    $sections =  parent::getSections();
    $route    = $this->routeMatcher->getRouteObject();

    if ((!$this->abConfig->hasActiveExperiment()) ||
      NULL === $route ||
      TRUE === $route->getOption('_admin_route') ||
      $route->hasRequirement('_field_ui_view_mode_access')) {
      return $sections;
    }

    $displayableSections = [];
    /**
     * @var \Drupal\layout_builder\Section $section
     */
    foreach ($sections as $section) {
      $layoutSettings = $section->getLayoutSettings();
      if (!isset($layoutSettings['experiment'], $layoutSettings['variations'])) {
        $displayableSections[] = $section;
        continue;
      }

      $experimentId = $layoutSettings['experiment'];
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
      $variation = $this->variationPicker->pickVariationForExperiment($experiment);

      if (in_array($variation->getId(), $availableVariations)) {
        $displayableSections[] = $section;
      }

    }
    return $displayableSections;
  }

}
