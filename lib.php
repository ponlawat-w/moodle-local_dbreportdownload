<?php
defined('MOODLE_INTERNAL') or die();

require_once(__DIR__ . '/../../mod/data/classes/manager.php');
require_once(__DIR__ . '/../../mod/data/classes/template.php');

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

/**
 * @param stdClass $dataid
 * @param string[] $fieldids
 * @return \mod_data\template
 */
function local_dbreportdownload_gettemplate($data, $fieldids) {
    /**
     * @var \moodle_database $DB
     */
    global $DB;

    $contents = [];
    foreach ($fieldids as $fieldid) {
        $field = $DB->get_record('data_fields', ['id' => $fieldid], 'id,name', MUST_EXIST);
        $contents[] = "<strong>{$field->name}</strong>: [[{$field->name}]]";
    }

    $content = implode('<br>', $contents);

    return new \mod_data\template(\mod_data\manager::create_from_instance($data), $content);
}
