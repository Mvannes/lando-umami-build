(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.loggingTraffic = {
        attach: function (context, settings) {
            var moduleSettingsEvent = drupalSettings.janus_ab.event;
            // Early return if any variable is not set. This means that we can't do the request.
            if (typeof moduleSettingsEvent.selector === 'undefined' ||
                typeof moduleSettingsEvent.event    === 'undefined' ||
                typeof moduleSettingsEvent.experimentId === 'undefined'||
                typeof moduleSettingsEvent.variationId === 'undefined' ||
                typeof moduleSettingsEvent.userIdCookie === 'undefined' ||
                typeof moduleSettingsEvent.postUrl === 'undefined') {
                return;
            }

            var userId = $.cookie(moduleSettingsEvent.userIdCookie)

            $(moduleSettingsEvent.selector, context).once('loggingPost').on(moduleSettingsEvent.event, function () {
                $.ajax({
                    url: moduleSettingsEvent.postUrl,
                    async: true,
                    data: {
                        'experiment': moduleSettingsEvent.experimentId,
                        'variation':  moduleSettingsEvent.variationId,
                        'userId':     userId
                    },
                    type: 'POST'
                });

            });
        }
    };
})(jQuery, Drupal, drupalSettings);
