/**
 * @file
 * Defines event logging behaviours for JanusAB
 */
(function ($, Drupal, drupalSettings) {
  "use strict";
  /**
   * This script does a "fire-and-forget" call to the configured event url.
   *
   * This url is found in the passed DrupalSettings, and is done once per page.
   * The call is only executed when the passed selector triggers the
   * passed JavaScript event.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches behaviors for JanusAB logging related to success.
   */
  Drupal.behaviors.janusABLoggingEvent = {
    attach: function (context, settings) {
      let moduleSettingsEvent = drupalSettings.janus_ab.event;
      let userId = $.cookie(moduleSettingsEvent.userIdCookie);

      $(moduleSettingsEvent.selector, context)
        .once('janusABLoggingPost')
        .on(moduleSettingsEvent.event, function () {
          $.ajax({
            url: moduleSettingsEvent.postUrl,
            async: true,
            data: {
              'experiment': moduleSettingsEvent.experimentId,
              'variation': moduleSettingsEvent.variationId,
              'userId': userId
            },
            type: 'POST'
          });

        });
    }
  };

})(jQuery, Drupal, drupalSettings);
