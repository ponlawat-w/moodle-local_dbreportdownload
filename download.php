<?php

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/downloadoptions_form.php');

require_login();

$dataid = required_param('d', PARAM_INT);
$fallbackurl = new moodle_url('/local/dbreportdownload/options.php', ['d' => $dataid]);

/**
 * @var \moodle_database $DB
 * @var \moodle_page $PAGE
 */
$data = $DB->get_record('data', ['id' => $dataid], '*', MUST_EXIST);
require_login($data->course);

$cm = get_coursemodule_from_instance('data', $data->id);
$context = context_module::instance($cm->id, MUST_EXIST);

$PAGE->set_context($context);

$form = new downloadoptions_form($dataid);
if (!$form->is_submitted()) {
    redirect($fallbackurl);
    exit;
}

$fromform = $form->get_data();

$format = $fromform->format;
if ($format != 'doc') {
    throw new moodle_exception('Unsupported format', 'local_dbreportdownload', $fallbackurl);
}

$fieldids = [];
foreach ($fromform->fields as $fieldid => $isenbaled) if ($isenbaled) {
    $fieldids[] = $fieldid;
}

if (!count($fieldids)) {
    throw new moodle_exception('No fields selected', 'local_dbreportdownload', $fallbackurl);
}

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
$parser = local_dbreportdownload_gettemplate($data, $fieldids);
foreach ($records as $record) {
    echo $parser->parse_entries([$record]);
    echo html_writer::start_tag('hr');
    echo html_writer::start_tag('br', ['style' => 'page-break-before: always;']);
}
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
