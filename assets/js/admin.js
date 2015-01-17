/*global jQuery, document*/
jQuery(document).ready(function ($) {
    'use strict';

    jQuery('.developerfuel-time').datetimepicker({
        timeFormat: 'h:mm tt',
        timeOnly: true
    });
});
