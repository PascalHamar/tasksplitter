<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class containing data for the tasksplitter block.
 *
 * @package    block_tasksplitter
 * @copyright  2024 Pascal Hamar <@.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_tasksplitter\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;

class completed_tasksplitter implements renderable, templatable {
    private $dbresult = null;

    public function __construct($dbresult) {
        $this->dbresult = $dbresult;
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) : stdClass {
        $data = new stdClass();
        $data->tasksplitters = [];

        // temp storage for data
        $tempTasksplitters = [];

        foreach ($this->dbresult as $row) {
            // check if tasksplitter already exists, if not create new one and add to temp storage
            if (!isset($tempTasksplitters[$row->tasksplitter_id])) {
                $tempTasksplitters[$row->tasksplitter_id] = new stdClass();
                $tempTasksplitters[$row->tasksplitter_id]->assign_name = $row->assign_name;
                $tempTasksplitters[$row->tasksplitter_id]->assign_intro = $row->assign_intro;
                $tempTasksplitters[$row->tasksplitter_id]->subtasks = [];
            }
            // store subtask data in stdClass
            $subtask = new stdClass();
            $subtask->description = $row->subtask_description;
            $subtask->rating = $row->subtask_rating;
            $subtask->feedback = $row->subtask_feedback;
            // add subtask to temp storage
            $tempTasksplitters[$row->tasksplitter_id]->subtasks[] = $subtask;
        }
        // convert temp storage to return array
        foreach ($tempTasksplitters as $tasksplitter) {
            $data->tasksplitters[] = $tasksplitter;
        }
        return $data;
    }
}
