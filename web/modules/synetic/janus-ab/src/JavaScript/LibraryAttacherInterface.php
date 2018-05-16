<?php

declare(strict_types = 1);

namespace Drupal\janus_ab\JavaScript;

/**
 * Helper class to attach javascript libraries within this module in hooks.
 */
interface LibraryAttacherInterface {

  /**
   * Attaches the success library for the given experiment id.
   *
   * If the experiment is not active, the original variables are returned as the
   * library can not be attached.
   *
   * @param array $variables
   *   The variables to attach the library to.
   * @param string $experimentId
   *   The experiment to use when attaching the library.
   *
   * @return array
   *   The updated $variables.
   */
  public function attachSuccessLibraryForExperimentId(
    array $variables,
    string $experimentId
  ): array;

  /**
   * Attaches the traffic library for the given experiment id.
   *
   * If the experiment is not active, the original variables are returned as the
   * library can not be attached.
   *
   * @param array $variables
   *   The variables to attach the library to.
   * @param string $experimentId
   *   The experiment to use when attaching the library.
   *
   * @return array
   *   The updated $variables.
   */
  public function attachTrafficLibraryForExperimentId(
    array $variables,
    string $experimentId
  ): array;

  /**
   * Attaches the event library for the given experiment, event and selector.
   *
   * If the experiment is not active, the original variables are returned as the
   * library can not be attached.
   *
   * @param array $variables
   *   The variables to attach the library to.
   * @param string $selector
   *   A valid JQuery selector.
   * @param string $event
   *   A valid JQuery event.
   * @param string $experimentId
   *   The experiment to use when attaching the library.
   *
   * @return array
   *   The updated $variables.
   */
  public function attachSuccessEventLibraryForExperimentId(
    array $variables,
    string $selector,
    string $event,
    string $experimentId
  ): array;

  /**
   * Attaches the event library for the given experiment, event and selector.
   *
   * If the experiment is not active, the original variables are returned as the
   * library can not be attached.
   *
   * @param array $variables
   *   The variables to attach the library to.
   * @param string $selector
   *   A valid JQuery selector.
   * @param string $event
   *   A valid JQuery event.
   * @param string $experimentId
   *   The experiment to use when attaching the library.
   *
   * @return array
   *   The updated $variables.
   */
  public function attachTrafficEventLibraryForExperimentId(
    array $variables,
    string $selector,
    string $event,
    string $experimentId
  ): array;

}
