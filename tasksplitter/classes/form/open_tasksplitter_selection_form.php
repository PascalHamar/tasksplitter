<?php
namespace block_tasksplitter\form;
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

use block_tasksplitter\helper\tasksplitter_helper;

class open_tasksplitter_selection_form extends \moodleform {
    protected function definition() {
        global $USER;
        $mform = $this->_form;

        // Open assignments grouped by course
        $opentasksplitterassignnames = tasksplitter_helper::get_open_tasksplitter_assign_names($USER->id);

        $options = [];
        // Loop through the open tasksplitter objects to create the options array (tasksplitter_id : assign_name)
        foreach ($opentasksplitterassignnames as $record) {
            $options[$record->tasksplitter_id] = $record->assign_name;
        }

        // Selectbox for assignment (from the open tasksplitter objects)
        $mform->addElement('select', 'selectedtasksplitterid', get_string('selecttasksplitter', 'block_tasksplitter'), $options);
        $mform->setType('selectedtasksplitterid', PARAM_INT); // Änderung hier, um sicherzustellen, dass es sich um eine Ganzzahl handelt

        // Hidden field to store the selected value
        // Needed to pass the selected value to the next page
        $mform->addElement('hidden', 'submittedtasksplitterid');
        $mform->setType('submittedtasksplitterid', PARAM_INT); // Sicherstellen, dass die Validierung korrekt ist
        $mform->setDefault('submittedtasksplitterid', 0); // Standardwert auf 0 setzen

        // Edit button
        $this->add_action_buttons(false, get_string('edit', 'block_tasksplitter'));
    }
}
