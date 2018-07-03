/**
 * @file
 * Defines success logging behaviours for JanusAB
 */
(function ($, Drupal, drupalSettings) {
  "use strict";

  /**
   * This script does a "fire-and-forget" call to the configured success url.
   *
   * This url is found in the passed DrupalSettings, and is done once per page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches behaviors for JanusAB logging related to success.
   */
  Drupal.behaviors.janusABLoggingSuccess = {
    attach: function (context, settings) {
      $('body', context).once('janusABLoggingSuccess').each(function () {
        var moduleSettingsSuccess = drupalSettings.janus_ab.success;
        var userId = $.cookie(moduleSettingsSuccess.userIdCookie);

        $.ajax({
          url: moduleSettingsSuccess.successUrl,
          async: true,
          data: {
            'experiment': moduleSettingsSuccess.experimentId,
            'variation': moduleSettingsSuccess.variationId,
            'userId': userId
          },
          type: 'POST'
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
