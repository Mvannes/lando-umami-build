<?php

declare(strict_types = 1);

namespace Drupal\janus_layout_builder\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Extension that replaces the default Layout to add a form view.
 *
 * Contains plugin based form extension to add JanusAB related form fields.
 */
class JanusABConfigLayout extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'experiment' => NULL,
      'variations' => [],
    ];
  }

  /**
   * Callback used with variation form ajax.
   *
   * @param array $form
   *   The form that was changed for this callback.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   The variation container.
   */
  public function variationFormCallback(
    array &$form,
    FormStateInterface $form_state,
    Request $request
  ) {
    return $form['layout_settings']['variation_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Need to do this because of:
    // https://www.drupal.org/project/bootstrap_layouts/issues/2868254 .
    if ($form_state instanceof SubformStateInterface) {
      $form_state = $form_state->getCompleteFormState();
    }

    $config = \Drupal::service('janus_ab.ab_config');
    $experiments = $config->getExperiments();

    $variationsByExperiment = [];
    foreach ($experiments as $experiment) {
      $variationsByExperiment[$experiment->getId()] = [];
      foreach ($experiment->getVariations() as $variation) {
        $variationsByExperiment[$experiment->getId()] += [
          $variation->getId() => $variation->getName(),
        ];
      }
    }
    $experimentIds = array_keys($variationsByExperiment);

    $form['experiment'] = [
      '#type' => 'select',
      '#title' => 'Experiment',
      '#options' => array_combine($experimentIds, $experimentIds),
      '#empty_option' => 'Default',
      '#empty_value' => NULL,
      '#default_value' => $this->configuration['experiment'] ?? NULL,
      '#ajax' => [
        'event' => 'change',
        'callback' => [$this, 'variationFormCallback'],
        'wrapper' => 'variation-target',
      ],
    ];

    $form['variation_container'] = [
      '#type' => 'container',
      '#id'   => 'variation-target',
    ];

    $experimentId = $form_state->getValue(
      'layout_settings',
      []
    )['experiment'] ?? $this->configuration['experiment'];
    $form['variation_container']['variations'] = [
      '#type' => 'checkboxes',
      '#title' => 'Variations',
      '#multiple' => TRUE,
      '#options' => $variationsByExperiment[$experimentId] ?? [],
      '#default_value' => $this->configuration['variations'] ?? [],
    ];

    return $form;
  }

  /**
   * Does nothing, we don't really care about custom validation here.
   *
   * Sadly, it must be implemented by the interface.
   *
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Doing an explicit empty check to avoid empty string values.
    $this->configuration['experiment'] = empty($form_state->getValue('experiment')) ?
      NULL :
      $form_state->getValue('experiment');
    $configuredVariations = [];
    $formVariations = $form_state->getValue('variation_container')['variations'] ?? [];
    foreach ($formVariations as $varKey => $formValue) {
      if (is_string($formValue)) {
        $configuredVariations[] = $formValue;
      }
    }
    $this->configuration['variations'] = $configuredVariations;
  }

}
