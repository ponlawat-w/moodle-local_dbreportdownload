<?php
defined('MOODLE_INTERNAL') or die();

function local_dbreportdownload_extend_settings_navigation(settings_navigation $settings, context $context) {
    /**
     * @var \moodle_page $PAGE
     */
    global $PAGE;
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
        new moodle_url('/local/dbreportdownload/download.php', ['d' => $cm->id, 'format' => 'doc']), null, null, 'modulesettings'
    );
    $node->set_show_in_secondary_navigation(false);
}
