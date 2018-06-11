<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Routing;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseSubscriber implements EventSubscriberInterface {

  /**
   * The config to use in determining whether or not an experiment is active.
   *
   * @var \Synetic\JanusAB\Config\ABConfigInterface
   */
  private $config;

  /**
   * VariationPicker to pick variations.
   *
   * @var \Synetic\JanusAB\Variation\VariationPickerInterface
   */
  private $variationPicker;

  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => 'onResponse'
    ];
  }

  public function onResponse(FilterResponseEvent $event) {
    $headers = ['cookie_name' => 'cool-cookie-name', 'id_cookie' => 'cool-cookie-name-id', 'active-experiment' => 'cool-exp-id', 'chosen-variation' => '0', 'amount_of_variations' => '2'];
    $event->getResponse()->headers->set('janus-ab', $headers);
//    $event->getResponse()->headers->set('janus-ab-cookie', 'cool-cookie-name');
//    $event->getResponse()->headers->set('janus-ab-id-cookie', 'cool-cookie-name_ID');
//    $event->getResponse()->headers->set('janus-ab-active-experiment', 'cool-exp-id');
//    $event->getResponse()->headers->set('janus-ab-chosen-variation', '0'); // cool-variation-id
    dump($event); die;
  }
}