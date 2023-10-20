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
 * @param stdClass $data
 * @return array
 */
function local_dbreportdownload_getorderedfieldids($data) {
    /**
     * @var \moodle_database $DB
     */
    global $DB;
    $fields = $DB->get_records('data_fields', ['dataid' => $data->id], '', 'id,name');
    $manager = \mod_data\manager::create_from_instance($data);
    $template = $manager->get_template('singletemplate', ['search' => '', 'page' => 0]);
    $content = $template->get_template_content();
    $strposes = [];
    foreach ($fields as $field) {
        $pos = strpos($content, "[[{$field->name}]]");
        $strposes[] = $pos === false ? strlen($content) : $pos;
    }
    array_multisort($strposes, $fields);
    $results = [];
    foreach ($fields as $field) {
        $results[$field->id] = $field->name;
    }
    return $results;
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

    $html = '<table><tbody>';

    foreach ($fieldids as $fieldid) {
        $field = $DB->get_record('data_fields', ['id' => $fieldid], 'id,type,name', MUST_EXIST);
        $column = $field->type === 'textarea' ? "{$field->name} ([[[WORD_COUNT({$field->name})]]])" : $field->name;
        $contents[] = "<tr><th>{$column}</th><td>[[{$field->name}]]</td></tr>";
    }

    $html .= implode('<br>', $contents);

    $html .= '</tbody></table>';

    return new \mod_data\template(\mod_data\manager::create_from_instance($data), $html);
}
