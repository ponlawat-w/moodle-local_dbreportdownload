<?php

defined('MOODLE_INTERNAL') or die();

require_once(__DIR__ . '/../../../lib/formslib.php');

class downloadoptions_form extends moodleform {
    public $dataid;

    public function __construct($dataid, $action = null) {
        $this->dataid = $dataid;
        parent::__construct($action ? $action : new moodle_url('/local/dbreportdownload/download.php'));
    }

    private function getcheckboxes() {
        /**
         * @var \moodle_database $DB
         */
        global $DB;

        $mform = $this->_form;

        $fields = $DB->get_records('data_fields', ['dataid' => $this->dataid], '', 'id, name');
        $checkboxes = [];
        foreach ($fields as $field) {
            $checkboxes[] = $mform->createElement('advcheckbox', $field->id, '', $field->name, ['group' => 1]);
            $mform->setDefault("fields[{$field->id}]", 1);
        }
        return $checkboxes;
    }

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'd', $this->dataid);
        $mform->setType('d', PARAM_INT);
        $mform->addElement('hidden', 'format', 'doc');
        $mform->setType('format', PARAM_TEXT);

        $mform->addGroup($this->getcheckboxes(), 'fields', get_string('exportedfields', 'local_dbreportdownload'));
        $this->add_checkbox_controller(1);

        $this->add_action_buttons(false, get_string('download'));
    }
}
