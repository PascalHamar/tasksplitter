<?php
// Stellen Sie sicher, dass diese Datei im Kontext von Moodle aufgerufen wird

namespace block_tasksplitter\form;
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

use block_tasksplitter\helper\tasksplitter_helper;

class open_tasksplitter_edit_form extends \moodleform {
    // Formularelemente definieren
    public function definition() {
        global $DB, $CFG;

        $mform = $this->_form;

        // Laden der TaskSplitter-ID aus der Session
        $tasksplitterid = isset($_SESSION['selectedtasksplitterid']) ? $_SESSION['selectedtasksplitterid'] : null;

        if (!empty($tasksplitterid)) {
            // Laden der TaskSplitter-Daten
            $tasksplitter = $DB->get_record('block_tasksplitter', ['id' => $tasksplitterid]);

            // Initialisieren des Formulars mit TaskSplitter-Daten
            $mform->addElement('hidden', 'tasksplitterid', $tasksplitter->id);
            $mform->setType('tasksplitterid', PARAM_INT);

            $mform->addElement('text', 'name', get_string('name', 'block_tasksplitter'), ['value' => $tasksplitter->name]);
            $mform->setType('name', PARAM_TEXT);

            // Hinzufügen von weiteren Formularelementen basierend auf den TaskSplitter-Daten...

            // Aktionsknöpfe
            $this->add_action_buttons(true, get_string('savechanges'));
        }
    }


    // Funktion zur Validierung der Formulardaten, falls notwendig
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Fügen Sie hier benutzerdefinierte Validierungslogik hinzu, falls erforderlich

        return $errors;
    }
}
