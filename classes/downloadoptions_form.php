<?php

defined('MOODLE_INTERNAL') or die();

require_once(__DIR__ . '/../lib.php');
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

        $fields = local_dbreportdownload_getorderedfieldids($DB->get_record('data', ['id' => $this->dataid], '*', MUST_EXIST));
        $checkboxes = [];
        foreach ($fields as $id => $name) {
            $checkboxes[] = $mform->createElement('advcheckbox', $id, '', $name, ['group' => 1]);
            $mform->setDefault("fields[{$id}]", 1);
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
