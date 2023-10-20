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
        $column = $field->type === 'textarea' ? "{$field->name}<br>([[{$field->name}#wordCount]])" : $field->name;
        $contents[] = "<tr><th>{$column}</th><td>[[{$field->name}]]</td></tr>";
    }

    $html .= implode('<br>', $contents);

    $html .= '</tbody></table>';

    return new \mod_data\template(\mod_data\manager::create_from_instance($data), $html);
}

/**
 * @param string $content
 * @return int
 */
function local_dbreportdownload_countwords($content) {
    $content = preg_replace("(<.*?>|&nbsp;|\\\\n)", ' ', $content);
    $words = explode(' ', $content);
    $count = 0;
    foreach ($words as $word) {
        if (trim($word)) {
            $count++;
        }
    }
    return $count;
}

/**
 * @param \mod_data\template $parser
 * @param stdClass $record
 * @return string
 */
function local_dbreportdownload_parseentry($parser, $record) {
    /**
     * @var \moodle_database $DB
     */
    global $DB;
    $parsed = $parser->parse_entries([$record]);

    $finds = [];
    $replaces = [];
    $textareas = $DB->get_records('data_fields', ['dataid' => $record->dataid, 'type' => 'textarea']);
    foreach ($textareas as $textarea) {
        $find = "[[{$textarea->name}#wordCount]]";
        if (!str_contains($parsed, $find)) {
            continue;
        }
        $content = $DB->get_record('data_content', ['fieldid' => $textarea->id, 'recordid' => $record->id]);
        $wordcount = $content ? local_dbreportdownload_countwords($content->content) : 0;
        $finds[] = $find;
        $replaces[] = get_string($wordcount === 1 ? 'wordcount_singular' : 'wordcount_plural', 'local_dbreportdownload', $wordcount);
    }

    $parsed = str_ireplace($finds, $replaces, $parsed);

    return $parsed;
}
