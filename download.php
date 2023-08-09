<?php

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../mod/data/classes/manager.php');

require_login();
$dataid = required_param('d', PARAM_INT);
$format = strtolower(optional_param('f', 'doc', PARAM_TEXT));
if ($format != 'doc') {
    throw new moodle_exception('Unsupported format');
}

/**
 * @var \moodle_database $DB
 */
$data = $DB->get_record('data', ['id' => $dataid], '*', MUST_EXIST);
require_login($data->course);

$cm = get_coursemodule_from_instance('data', $data->id);
$context = context_module::instance($cm->id, MUST_EXIST);

define('MOODLE_LOCAL_DBREPORTDOWNLOAD_DOC', 1);

header('Content-Type: application/vnd.ms-word');
header('Content-Disposition: attachment; filename=export.doc');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

echo '<!DOCTYPE html>';
echo html_writer::start_tag('html');
echo html_writer::start_tag('head');
echo html_writer::start_tag('meta', ['charset' => 'utf-8']);
echo html_writer::tag('style', '* { font-family: sans-serif; } table { border-collapse: collapse; } td, th { border: 1px solid #000000; }');
echo html_writer::end_tag('head');
echo html_writer::start_tag('body');
$records = $DB->get_records('data_records', ['dataid' => $data->id, 'userid' => $USER->id], 'timecreated ASC');
$manager = \mod_data\manager::create_from_instance($data);
$parser = $manager->get_template('singletemplate', ['search' => '', 'page' => 0]);
foreach ($records as $record) {
    echo $parser->parse_entries([$record]);
    echo html_writer::start_tag('hr');
    echo html_writer::start_tag('br', ['style' => 'page-break-before: always;']);
}
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
