<?php


defined('MOODLE_INTERNAL') || die();

use block_tasksplitter\helper\tasksplitter_helper;
use block_tasksplitter\output\completed_tasksplitter;
use block_tasksplitter\form\new_tasksplitter_form;
class block_tasksplitter extends block_base {

    /**
     * Initialize class member variables
     */
    function init() {
        $this->title = get_string('pluginname','block_tasksplitter');
    }

     /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $OUTPUT, $USER, $PAGE;

        // If content is already set, return it
        if ($this->content !== null) {
            return $this->content;
        }

        // Initialize content
        $this->content = new stdClass;
        $this->content->header = '';
        $this->content->text = '';
        $this->content->footer = '';

        // Set default tab
        $currenttab = optional_param('tab', 'new', PARAM_ALPHA);

        // Tab data for nav.mustache template
        $tabs = [
            [
                'url' => new moodle_url($this->page->url, ['tab' => 'new']),
                'name' => get_string('section:new', 'block_tasksplitter'),
                'active' => ($currenttab === 'new'), // 'active' is true, if $currentTab is 'new'
            ],
            [
                'url' => new moodle_url($this->page->url, ['tab' => 'open']),
                'name' => get_string('section:open', 'block_tasksplitter'),
                'active' => ($currenttab === 'open')
            ],
            [
                'url' => new moodle_url($this->page->url, ['tab' => 'history']),
                'name' => get_string('section:done', 'block_tasksplitter'),
                'active' => ($currenttab === 'history')
            ]
        ];
        // Render nav.mustache template with tabs data
        $this->content->text .= $OUTPUT->render_from_template('block_tasksplitter/nav', ['tabs' => $tabs]);

        // Render content based on current tab
        $context = new stdClass; // Context for mustache templates (neccessary for rendering)
        switch ($currenttab) {
            case 'new':
                // Code for 'new' tab
                // Use the new_tasksplitter form
                $mform = new new_tasksplitter_form();
                if ($mform->is_cancelled()) {
                    // Form canceled
                } else if ($data = $mform->get_data()) {
                    // Process form data
                    $helper = new tasksplitter_helper();
                    $helper->create_tasksplitter($data);
                    // Redirect to page with success message
                    redirect(new moodle_url($this->page->url, ['tab' => 'new']), get_string('create:success', 'block_tasksplitter'), null, \core\output\notification::NOTIFY_SUCCESS);
                } else {
                    // Show form
                    $this->content->text .= $mform->render();
                }
                break;
            case 'open':
                // Code for 'open' tab
                $selectionForm = new \block_tasksplitter\form\open_tasksplitter_selection_form();
                if ($selectionForm->is_cancelled()) {
                    // Form canceled
                } else if($data = $selectionForm->get_data()) {
                    // Process form data
                 } else {
                    // Show form
                    $this->content->text .= $selectionForm->render();
                }
                break;

            case 'history':
                // Code for 'history' tab
                $helper = new tasksplitter_helper();
                $renderable = new completed_tasksplitter($helper->get_completed_tasksplitter($USER->id));
                $renderer = $PAGE->get_renderer('block_tasksplitter');
                $this->content->text .= $renderer->render_completed_tasksplitter($renderable);
                break;
        }

        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'course-view' => false, 'site-index' => false,'site' => false, 'my-index' => true, 'my' => true, 'mod' => false
        ];
    }
}
