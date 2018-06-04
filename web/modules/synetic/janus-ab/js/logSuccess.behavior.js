(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.loggingSuccess = {
        attach: function (context, settings) {
            $('body', context).once('loggingSuccess').each(function () {
                var  moduleSettingsSuccess = drupalSettings.janus_ab.success;
                if (typeof moduleSettingsSuccess.experimentId === 'undefined' ||
                    typeof moduleSettingsSuccess.variationId === 'undefined' ||
                    typeof moduleSettingsSuccess.successUrl === 'undefined' ||
                    typeof moduleSettingsSuccess.userIdCookie === 'undefined') {
                    return;
                }
                var userId = $.cookie(moduleSettingsSuccess.userIdCookie)

                $.ajax({
                    url: moduleSettingsSuccess.successUrl,
                    async: true,
                    data: {
                        'experiment': moduleSettingsSuccess.experimentId,
                        'variation':  moduleSettingsSuccess.variationId,
                        'userId':     userId
                    },
                    type: 'POST'
                });
            });
        }
    };
})(jQuery, Drupal, drupalSettings);
