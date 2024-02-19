<?php
namespace block_tasksplitter\form;
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

use block_tasksplitter\helper\tasksplitter_helper;

class new_tasksplitter_form extends \moodleform {
    protected function definition() {
        global $USER;
        $mform = $this->_form;

        // open assignements grouped by course
        $assignments = tasksplitter_helper::get_open_assignments_grouped_by_course($USER->id);

        $options = [];
        foreach ($assignments as $course_name => $course_assignments) {
            $group = [];
            foreach ($course_assignments as $assignment) {
                $group[$assignment['id']] = $assignment['name'];
            }
            $options[$course_name] = $group;
        }

        // Selectbox for assignment
        $mform->addElement('selectgroups', 'assignmentid', get_string('selectassignment', 'block_tasksplitter'), $options);
        $mform->setType('assignmentid', PARAM_INT);

        // Textfield for subtask description (required)
        $mform->addElement('text', 'subtaskdescription', get_string('subtaskdescription', 'block_tasksplitter'));
        $mform->setType('subtaskdescription', PARAM_TEXT);
        $mform->addRule('subtaskdescription', get_string('err_required', 'form'), 'required', null, 'server');

        // Submit button
        $this->add_action_buttons(false, get_string('submit'));
    }
}
