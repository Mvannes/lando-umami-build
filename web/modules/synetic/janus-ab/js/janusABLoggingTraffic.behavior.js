/**
 * @file
 * Defines traffic logging behaviours for JanusAB
 */
(function ($, Drupal, drupalSettings) {
  "use strict";
  /**
   * This script does a "fire-and-forget" call to the configured traffic url.
   *
   * This url is found in the passed DrupalSettings, and is done once per page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches behaviors for JanusAB logging related to success.
   */
  Drupal.behaviors.janusABLoggingTraffic = {
    attach: function (context, settings) {
      var moduleSettingsTraffic = drupalSettings.janus_ab.traffic;
      var userId = $.cookie(moduleSettingsTraffic.userIdCookie);

      $('body', context).once('janusABLoggingTraffic').each(function () {
        $.ajax({
          url: moduleSettingsTraffic.trafficUrl,
          async: true,
          data: {
            'experiment': moduleSettingsTraffic.experimentId,
            'variation': moduleSettingsTraffic.variationId,
            'userId': userId
          },
          type: 'POST'
        });

      });
    }
  };

})(jQuery, Drupal, drupalSettings);
