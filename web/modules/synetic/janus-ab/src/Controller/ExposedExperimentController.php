<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Synetic\JanusAB\Config\ABConfigInterface;
use Synetic\JanusAB\Variation\VariationInterface;

class ExposedExperimentController extends ControllerBase {

  /**
   * ABConfig to expose the active experiment to the public.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * LoggingRouteCreator constructor.
   *
   * @param \Synetic\JanusAB\Config\ABConfigInterface $config
   *   Config object used for traffic and success url access.
   */
  public function __construct(ABConfigInterface $config) {
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('janus_ab.ab_config')
    );
  }

  public function activeExperimentInformationAction(): Response {
    $response = new JsonResponse();
    if (!$this->config->hasActiveExperiment()) {
      return $response;
    }
    $experiment = $this->config->getActiveExperiment();
    $variations = array_map(function (VariationInterface $variation) {
      return $variation->getId();
    }, $experiment->getVariations());

    $data = [
      'vendor_name' => $this->config->getVendorName(),
      'site_name' => $this->config->getSiteName(),
      'experiment_id' => $experiment->getId(),
      'variations' => $variations
    ];

    $response->setData($data);
    return $response;
  }
}