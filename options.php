<?php

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/downloadoptions_form.php');

$dataid = required_param('d', PARAM_INT);
$cm = get_coursemodule_from_instance('data', $dataid, 0, false, MUST_EXIST);
/**
 * @var \moodle_database $DB
 */
require_login($cm->course);

$data = $DB->get_record('data', ['id' => $dataid], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_capability('local/dbreportdownload:downloadmyreport', $context);

/**
 * @var \moodle_page $PAGE
 * @var \core_renderer $OUTPUT
 */
$url = new moodle_url('/local/dbreportdownload/options.php', ['d' => $dataid]);
$PAGE->set_url($url);
$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_title(get_string('downloadmyreportfrom', 'local_dbreportdownload', $data->name));
$PAGE->set_heading($PAGE->title);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('downloadmyreport', 'local_dbreportdownload'));

$form = new downloadoptions_form($dataid);

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
