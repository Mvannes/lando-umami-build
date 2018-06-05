<?php
namespace Drupal\content_vary\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\VariationPickerInterface;

class VaryController extends ControllerBase {

  /**
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * @var \Synetic\JanusAB\Variation\VariationPickerInterface
   */
  private $variationPicker;

  public function __construct(
    ABConfigInterface $config,
    VariationPickerInterface $variationPicker
  ) {
    $this->config = $config;
    $this->variationPicker = $variationPicker;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('janus_ab.ab_config'),
      $container->get('janus_ab.variation_picker')
    );
  }

  public function content() {
    $abConfig        = \Drupal::service('janus_ab.ab_config');
    // If there is no experiment with our specific id, we should not add any variation templates.
    if (!$abConfig->hasActiveExperimentWithId('cool-id')) {
      return;
    }
    $variationPicker = \Drupal::service('janus_ab.variation_picker');
    // Get the active experiment, because we know it exists.
    $experiment = $abConfig->getActiveExperimentById('cool-id');
    // Pick a variation, the variationPicker internals will ensure that this is the correct variation for each user.
    $variation = $variationPicker->pickVariationForExperiment($experiment);

    if ($variation->getId() === '1') {
      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/9');
    } else {
      $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/13');
    }
    return new RedirectResponse($alias);
  }
}
