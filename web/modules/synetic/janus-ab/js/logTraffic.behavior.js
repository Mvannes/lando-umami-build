(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.loggingTraffic = {
        attach: function (context, settings) {
            var moduleSettingsTraffic = drupalSettings.janus_ab.traffic;

            // Early return if any variable is not set. This means that we can't do the request.
            if (typeof moduleSettingsTraffic.experimentId === 'undefined' ||
                typeof moduleSettingsTraffic.variationId === 'undefined' ||
                typeof moduleSettingsTraffic.userIdCookie === 'undefined' ||
                typeof moduleSettingsTraffic.trafficUrl === 'undefined') {
s
                return;
            }

            var userId = $.cookie(moduleSettingsTraffic.userIdCookie)

            $('body', context).once('loggingTraffic').each( function () {
                $.ajax({
                    url: moduleSettingsTraffic.trafficUrl,
                    async: true,
                    data: {
                        'experiment': moduleSettingsTraffic.experimentId,
                        'variation':  moduleSettingsTraffic.variationId,
                        'userId':     userId
                    },
                    type: 'POST'
                });

            });
        }
    };
})(jQuery, Drupal, drupalSettings);
