<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form to configure required janus settings.
 *
 * Involves the configuration of basic parameters,
 * as well as experiment information.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * Constant holding the container id for the experiment container.
   *
   * @var string
   */
  private const CONTAINER_ID = 'experiment-holder';

  /**
   * Constant holding the experiment prefix.
   *
   * @var string
   */
  private const EXPERIMENT_PREFIX = 'experiment_';

  /**
   * Constant holding the variation prefix.
   *
   * @var string
   */
  private const VARIATION_PREFIX = 'variation_';

  /**
   * Constant holding the editable config name.
   *
   * @var string
   */
  private const CONFIG_NAME = 'janus_ab.settings';

  /**
   * RouteBuilder to rebuild our dynamic routes when routing config changes.
   *
   * @var \Drupal\Core\Routing\RouteBuilder
   */
  private $routeBuilder;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::CONFIG_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'janus_ab_form';
  }

  /**
   * ConfigForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   ConfigFactory to allow access to the correct configuration.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $routeBuilder
   *   RouteBuilder to rebuild application routes when needed.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    RouteBuilderInterface $routeBuilder
  ) {
    parent::__construct($configFactory);
    $this->routeBuilder = $routeBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_NAME);
    // Don't cache this ever, lots of changing components.
    $form_state->setCached(FALSE);
    $form['#tree'] = TRUE;

    $form['vendorName'] = [
      '#type'          => 'textfield',
      '#title'         => 'Vendor name',
      '#required'      => TRUE,
      '#default_value' => $config->get('vendorName'),
    ];

    $form['siteName'] = [
      '#type'          => 'textfield',
      '#title'         => 'Site name',
      '#required'      => TRUE,
      '#default_value' => $config->get('siteName'),
    ];

    $form['trafficUrl'] = [
      '#type'          => 'textfield',
      '#title'         => 'Url for traffic logging',
      '#required'      => TRUE,
      '#default_value' => $config->get('trafficUrl'),
    ];

    $form['successUrl'] = [
      '#type'          => 'textfield',
      '#title'         => 'Url for success logging',
      '#required'      => TRUE,
      '#default_value' => $config->get('successUrl'),
    ];

    $form['trackingId'] = [
      '#type'          => 'textfield',
      '#title'         => 'Google Analytics id',
      '#required'      => TRUE,
      '#default_value' => $config->get('trackingId'),
    ];

    // Add experiments.
    $container = [
      '#type' => 'container',
      '#id'   => self::CONTAINER_ID,
    ];
    // If this is the first time we load the form,
    // we must populate with possible existing experiments from our config.
    if (!$form_state->has('experiments')) {
      $form_state->set(
        'experiments',
        $config->get('experiments') ?? []
      );
    }

    // Add fields for each experiment.
    foreach ($form_state->get('experiments') as $key => $experiment) {
      $container = $this->createExperimentFields($container, $experiment, $key);
    }

    $container['actions'] = [
      '#type' => 'actions',
    ];

    $container['actions']['addItem'] = [
      '#type'   => 'submit',
      '#name'   => 'add-experiment',
      '#value'  => $this->t('Add another experiment'),
      '#submit' => [$this, '::addExperiment'],
      '#limit_validation_errors' => [],
      '#ajax'   => [
        'callback' => [$this, 'callExperimentAjax'],
        'wrapper'  => self::CONTAINER_ID,
      ],
    ];
    $form['container'] = $container;

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type'        => 'submit',
      '#value'       => $this->t('Save configuration'),
      '#button_type' => 'primary',
      '#validate'    => [$this, '::doValidateForm'],
    ];
    return $form;
  }

  /**
   * Utility function that creates experiment input fields for the form.
   *
   * Creation is done based on the experiment information as well as the array
   * key of the experiment within the form state.
   *
   * @param array $container
   *   The main experiment container. This is edited within the function
   *   and then returned with additional fields.
   * @param array $experiment
   *   The experiment array that holds the data for each field.
   * @param string $key
   *   The array key of this experiment in the form state.
   *
   * @return array
   *   The adjusted container.
   */
  private function createExperimentFields(
    array $container,
    array $experiment,
    string $key
  ): array {
    $experimentId = self::EXPERIMENT_PREFIX . $key;

    // Change state by the start- and end dates for display purposes.
    $state = 'new';
    if (isset($experiment['startDate'], $experiment['endDate'])) {
      $startDate = new \DateTime($experiment['startDate']);
      $endDate   = new \DateTime($experiment['endDate']);
      $now       = new \DateTime();
      $state     = $startDate <= $now && $endDate >= $now ?
        'active' :
        ($endDate <= $now ? 'past' : 'future');
    }

    $experimentFields = [
      '#type'  => 'details',
      '#title' => sprintf('experiment: %s (%s)', $key, $state),
      '#id'    => $experimentId . '_fields',
    ];

    $experimentFields['id'] = [
      '#type'          => 'textfield',
      '#title'         => 'Experiment Id',
      '#attributes'    => ['placeholder' => $this->t('Id')],
      '#required'      => TRUE,
      '#default_value' => $experiment['id'] ?? '',
    ];

    $experimentFields['startDate'] = [
      '#type'          => 'date',
      '#title'         => 'Start date',
      '#required'      => TRUE,
      '#default_value' => $experiment['startDate'] ?? '',
    ];

    $experimentFields['endDate'] = [
      '#type'          => 'date',
      '#title'         => 'End date',
      '#required'      => TRUE,
      '#default_value' => $experiment['endDate'] ?? '',
    ];

    // Add a variation container.
    $experimentFields['variations'] = [
      '#type'  => 'details',
      '#title' => 'Variations',
      '#open'  => TRUE,
      '#id'    => $experimentId . '-variations',
    ];

    $variations = $experiment['variations'] ?? [];
    // Add variation fields.
    foreach ($variations as $key => $variation) {
      $experimentFields['variations'] = $this->createVariationFields(
        $experimentFields['variations'], $variation, (string) $key, $experimentId
      );
    }

    $experimentFields['variations']['actions'] = [
      '#type' => 'actions',
    ];

    $experimentFields['variations']['actions']['addItem'] = [
      '#type'                    => 'submit',
      '#name'                    => 'add-variation-' . $experimentId,
      '#value'                   => $this->t('Add another Variation'),
      '#submit'                  => [$this, '::addVariation'],
      '#limit_validation_errors' => [],
      '#ajax'                    => [
        'callback' => [$this, 'callVariationAjax'],
        'wrapper'  => $experimentId . '-variations',
      ],
    ];

    $experimentFields['actions']['removeItem'] = [
      '#type'                    => 'submit',
      '#name'                    => 'remove-experiment-' . $experimentId,
      '#value'                   => $this->t('Remove this experiment'),
      '#submit'                  => [$this, '::removeExperiment'],
      '#limit_validation_errors' => [],
      '#ajax'                    => [
        'callback' => [$this, 'callExperimentAjax'],
        'wrapper'  => self::CONTAINER_ID,
      ],
    ];

    $container[$experimentId] = $experimentFields;

    return $container;
  }

  /**
   * Utility function that creates variation related fields for the form.
   *
   * @param array $variationContainer
   *   The current variation container on the form.
   *   This is edited and returned to add fields.
   * @param array $variation
   *   An array of variation information.
   * @param string $key
   *   The array key for this variation in the form state.
   * @param string $experimentId
   *   The array key of the experiment.
   *
   * @return array
   *   The updated variation container.
   */
  private function createVariationFields(
    array $variationContainer,
    array $variation,
    string $key,
    string $experimentId
  ): array {
    $variationKey = self::VARIATION_PREFIX . $key;

    $variationContainer[$variationKey]['name'] = [
      '#type'          => 'textfield',
      '#title'         => 'Variation Name',
      '#attributes'    => ['placeholder' => $this->t('Name')],
      '#required'      => TRUE,
      '#default_value' => $variation['name'] ?? '',
    ];

    $variationContainer[$variationKey]['actions']['removeItem'] = [
      '#type'                    => 'submit',
      '#name'                    => sprintf(
        'remove-variation-%s-%s',
        $experimentId,
        $variationKey
      ),
      '#value'                   => $this->t('Remove this Variation'),
      '#submit'                  => [$this, '::removeVariation'],
      '#limit_validation_errors' => [],
      '#ajax'                    => [
        'callback' => [$this, 'callVariationAjax'],
        'wrapper'  => $experimentId . '-variations',
      ],
    ];

    return $variationContainer;
  }

  /**
   * Callback triggered on any ajax related to experiments.
   *
   * Returns the main experiment container.
   *
   * @param array $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current form state.
   *
   * @return array
   *   The container.
   */
  public function callExperimentAjax(
    array $form,
    FormStateInterface $formState
  ): array {
    return $form['container'];
  }

  /**
   * Callback triggered on any ajax related to variations.
   *
   * Returns the variation container for the related experiment.
   *
   * @param array $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current form state.
   *
   * @return array
   *   The variation container that was responsible for this trigger.
   */
  public function callVariationAjax(
    array $form,
    FormStateInterface $formState
  ): array {
    $experimentId = $formState->getTriggeringElement()['#parents'][1];
    return $form['container'][$experimentId]['variations'];
  }

  /**
   * Callback triggered when a "add experiment" button is triggered.
   *
   * Adds a new experiment key to the form state.
   *
   * @param array $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current form state.
   */
  public function addExperiment(
    array $form,
    FormStateInterface $formState
  ): void {
    // Add a new array key with empty array value to render the empty fields.
    $formState->set(
      'experiments',
      array_merge($formState->get('experiments'), [uniqid('new_') => []])
    );
    $formState->setRebuild();
  }

  /**
   * Callback triggered when a "add variation" button is triggered.
   *
   * Adds a new variation key to the form state in the correct position.
   *
   * @param array $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current form state.
   */
  public function addVariation(
    array $form,
    FormStateInterface $formState
  ): void {
    // Get the correct experiment first.
    $experimentId = $this->getCurrentExperimentIdForAjax($formState);
    $experiments  = $formState->get('experiments');
    $experiment   = $experiments[$experimentId];

    // Add a new array key with empty array value to render the empty fields.
    $variations                 = $experiment['variations'] ?? [];
    $experiment['variations']   = array_merge(
      $variations,
      [uniqid('new_') => []]
    );
    $experiments[$experimentId] = $experiment;

    $formState->set('experiments', $experiments);

    $formState->setRebuild();
  }

  /**
   * Get the current experiment id during an AJAX button trigger.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return string
   *   The current experiment id.
   */
  private function getCurrentExperimentIdForAjax(
    FormStateInterface $formState
  ): string {
    $experimentId = $formState->getTriggeringElement()['#parents'][1];
    // Substring out the prefix part of the id.
    $pos = stripos($experimentId, self::EXPERIMENT_PREFIX);
    // Explicit cast to string to avoid TypeError should substr return a bool.
    return (string) substr(
      $experimentId,
      $pos + strlen(self::EXPERIMENT_PREFIX)
    );
  }

  /**
   * Get the current variation id during an AJAX button trigger.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return string
   *   The current variation key.
   */
  private function getCurrentVariationKeyForAjax(
    FormStateInterface $formState
  ): string {
    $variationKey = $formState->getTriggeringElement()['#parents'][3];
    // Substring out the prefix part of the id.
    $pos = stripos($variationKey, self::VARIATION_PREFIX);
    // Explicit cast to string to avoid TypeError should substr return a bool.
    return (string) substr($variationKey, $pos + strlen(self::VARIATION_PREFIX));
  }

  /**
   * Callback triggered when a "remove experiment" button is pressed.
   *
   * @param array $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current form state.
   */
  public function removeExperiment(
    array $form,
    FormStateInterface $formState
  ): void {
    $experimentId = $this->getCurrentExperimentIdForAjax($formState);
    $experiments = $formState->get('experiments');
    // Unset the removed experiment from the form_state.
    unset($experiments[$experimentId]);
    $formState->set('experiments', $experiments);
    $formState->setRebuild();
  }

  /**
   * Callback triggered when a "remove variation" button is pressed.
   *
   * @param array $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current form state.
   */
  public function removeVariation(
    array $form,
    FormStateInterface $formState
  ): void {
    $experimentId = $this->getCurrentExperimentIdForAjax($formState);
    $variationKey = $this->getCurrentVariationKeyForAjax($formState);

    $experiments = $formState->get('experiments');
    $experiment = $experiments[$experimentId];
    $variations = $experiment['variations'];

    unset($variations[$variationKey]);
    $experiment['variations'] = $variations;
    $experiments[$experimentId] = $experiment;

    $formState->set('experiments', $experiments);
    $formState->setRebuild();
  }

  /**
   * Custom validation function that is called on final form submit.
   *
   * Adds the previously loosened validation and some additional date related
   * validations.
   *
   * @param array $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current form state.
   */
  public function doValidateForm(
    array &$form,
    FormStateInterface $formState
  ): void {
    parent::validateForm($form, $formState);
    $experiments = $formState->getValue('container');
    $dateRanges = [];
    foreach ($experiments as $key => $experiment) {
      // Skip the key if its not an experiment.
      if (!stristr($key, self::EXPERIMENT_PREFIX)) {
        continue;
      }
      // Date validation. Start date > End date.
      // Times are set to ensure single day experiments are possible.
      $startDate = new \DateTime($experiment['startDate']);
      $startDate->setTime(0, 0, 0);
      $endDate = new \DateTime($experiment['endDate']);
      $endDate->setTime(23, 59, 59);
      if ($startDate > $endDate) {
        $formState->setErrorByName(
          'container][' . $key . '][startDate',
          sprintf(
            'Experiment "%s" has a start date after its end date.',
            $experiment['id']
          )
        );
        $formState->setErrorByName(
          'container][' . $key . '][endDate',
          sprintf(
            'Experiment "%s" has a start date after its end date.',
            $experiment['id']
          )
        );

      }
      // Date validation. No overlap of experiments date ranges.
      foreach ($dateRanges as $experimentKey => $dates) {
        $existingStart = $dates['startDate'];
        $existingEnd   = $dates['endDate'];
        if (($startDate >= $existingStart && $startDate <= $existingEnd) ||
          ($startDate >= $existingStart && $endDate <= $existingEnd) ||
          ($startDate <= $existingStart && $endDate >= $existingStart)
        ) {
          $formState->setErrorByName(
            'container][' . $key . '][startDate',
            sprintf(
              'Experiment "%s" conflicts with experiment "%s"',
              $experiment['id'],
              $experimentKey)
          );
        }
      }
      // Store the current date range for future iteration.
      $dateRanges[$experiment['id']] = [
        'startDate' => $startDate,
        'endDate'   => $endDate,
      ];

      // Validate that the experiment has variations.
      if (empty($experiment['variations'])) {
        $formState->setErrorByName(
          'container][' . $key . '][variations',
          sprintf(
            'Experiment "%s" has no configured variations.',
            $experiment['id']
          )
        );
      }
      // Validate that traffic and success urls have proper routes.
      $trafficUrl = $formState->getValue('trafficUrl');
      if (stripos($trafficUrl, '/') !== 0 && stripos($trafficUrl, 'http') !== 0) {
        $formState->setErrorByName(
          'trafficUrl',
          'The traffic url must be either a full path like "https://synetic.nl" or a relative path like "/home"'
        );
      }
      $successUrl = $formState->getValue('successUrl');
      if (stripos($successUrl, '/') !== 0 && stripos($successUrl, 'http') !== 0) {
        $formState->setErrorByName(
          'successUrl',
          'The success url must be either a full path like "https://synetic.nl" or a relative path like "/home"'
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Always start from an empty array,
    // automatically handles update and remove as experiments are keyed by id.
    $experiments = [];
    foreach ($form_state->getValue('container') as $key => $experiment) {
      // If the key is not an experiment, skip it.
      if (!stristr($key, self::EXPERIMENT_PREFIX)) {
        continue;
      }
      $variations = [];
      $variationId = 0;
      foreach ($experiment['variations'] as $variationKey => $variation) {
        // If the key is not a variation, skip it.
        if (!stristr($variationKey, self::VARIATION_PREFIX)) {
          continue;
        }
        $variations[$variationId] = [
          'id'   => $variationId,
          'name' => $variation['name'],
        ];
        $variationId++;
      }
      $experiments[$experiment['id']] = [
        'id'                 => $experiment['id'],
        'startDate'          => $experiment['startDate'],
        'endDate'            => $experiment['endDate'],
        'variations'         => $variations,
      ];
    }
    $trafficUrl = $form_state->getValue('trafficUrl');
    $successUrl = $form_state->getValue('successUrl');

    $moduleConfig = $this->config(self::CONFIG_NAME);
    // If our dynamic urls are changed, we must rebuild routing.
    if ($trafficUrl !== $moduleConfig->get('trafficUrl') ||
      $successUrl !== $moduleConfig->get('successUrl')
    ) {
      $this->routeBuilder->setRebuildNeeded();
    }

    // Set and save the configured data based on the form data.
    $moduleConfig->set('vendorName', $form_state->getValue('vendorName'))
      ->set('siteName', $form_state->getValue('siteName'))
      ->set('trafficUrl', $trafficUrl)
      ->set('successUrl', $successUrl)
      ->set('experiments', $experiments)
      ->set('trackingId', $form_state->getValue('trackingId'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
