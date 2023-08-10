<?php
defined('MOODLE_INTERNAL') or die();

function local_dbreportdownload_extend_settings_navigation(settings_navigation $settings, context $context) {
    if (!($context instanceof context_module)) {
        return;
    }
    if (!has_capability('local/dbreportdownload:downloadmyreport', $context)) {
        return;
    }
    $cm = $settings->get_page()->cm;
    if (!$cm) {
        return;
    }
    $node = $settings->add(
        get_string('downloadmyreport', 'local_dbreportdownload'),
        new moodle_url('/local/dbreportdownload/options.php', ['d' => $cm->instance]), null, null, 'modulesettings'
    );
    $node->set_show_in_secondary_navigation(false);
}
