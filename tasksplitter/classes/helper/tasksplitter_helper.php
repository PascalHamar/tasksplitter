<?php

namespace block_tasksplitter\helper;

defined('MOODLE_INTERNAL') || die();

use context_course;
use stdClass;
use Exception;

/**
 * Helper class for tasksplitter block.
 */
class tasksplitter_helper {
    /**
     * Gets all open assignments of enrolled courses for a user, where no tasksplitter object and no submission exist, grouped by course.
     *
     * @param int $userid User ID
     * @return array open assignments grouped by course
     */
    public static function get_open_assignments_grouped_by_course($userid) {
        global $DB;
        $open_assignments = [];
        // Use core function (enroll_get_all_users_courses) to get all courses of the user
        $user_courses = enrol_get_all_users_courses($userid, true);
        // Extract course ids
        $courseids = array_map(function($course) { return $course->id; }, $user_courses);

        // Prepare IN clause for SQL to query all courses at once
        list($inSql, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        // Query for all open assignments of enrolled courses for a user where no tasksplitter object exists and no submission exists
        $sql = "SELECT a.id, a.name, a.course
                FROM {assign} a
                LEFT JOIN {block_tasksplitter} ts ON ts.assignid = a.id
                LEFT JOIN {assign_submission} sub ON sub.assignment = a.id AND sub.status = 'submitted'
                WHERE a.course $inSql
                AND ts.assignid IS NULL
                AND sub.assignment IS NULL";

        $assignments = $DB->get_records_sql($sql, $params);

        foreach ($assignments as $assignment) {
            // Check if user has capability to view the assignment
            $context = context_course::instance($assignment->course);
            if (!has_capability('mod/assign:view', $context, $userid)) {
                continue;
            }
            // Group open assignments by course
            $courseName = $user_courses[$assignment->course]->fullname;
            $open_assignments[$courseName][] = [
                'id' => $assignment->id,
                'name' => format_string($assignment->name)
            ];
        }
        return $open_assignments;
    }

    /**
     * Creates a new tasksplitter object and a new tasksplitter_subtask object in the database.
     *
     * @param stdClass $data Data from form
     */
    public function create_tasksplitter($data) {
        global $DB, $USER;

        // Start of transaction
        $transaction = $DB->start_delegated_transaction();

        try {
            // First: create tasksplitter object
            $tasksplitter = new stdClass();
            $tasksplitter->assignid = $data->assignmentid;
            $tasksplitter->userid = $USER->id;
            $tasksplitter->status = 'open';

            // Insert tasksplitter object into database and get the id
            $tasksplitterid = $DB->insert_record('block_tasksplitter', $tasksplitter);

            // Second: create tasksplitter_subtask object with tasksplitter id as foreign key for reference
            $tasksplitter_subtask = new stdClass();
            $tasksplitter_subtask->tasksplitterid = $tasksplitterid;
            $tasksplitter_subtask->description = $data->subtaskdescription;
            $tasksplitter_subtask->status = 'open';

            // Insert tasksplitter_subtask object into database
            $DB->insert_record('block_tasksplitter_subtask', $tasksplitter_subtask);

            // Commit of the transaction if successful
            $transaction->allow_commit();
            } catch (Exception $e) {
            // In case of an exception, rollback the transaction so that no changes are made to the database
            $transaction->rollback($e);
        }
    }
    /**
     * Get all completed tasksplitter objects with subtask and assign information.
     *
     * @param int $userid User ID
     * @return array tasksplitter object id, assign name, assign intro, subtask description, subtask rating and subtask feedback
     */
    public static function get_completed_tasksplitter($userid) {
        global $DB;

        $sql = "SELECT ts.id AS tasksplitter_id, a.name AS assign_name, a.intro AS assign_intro,
        sub.description AS subtask_description, sub.rating AS subtask_rating, sub.feedback AS subtask_feedback
        FROM {block_tasksplitter} ts
        JOIN {assign} a ON ts.assignid = a.id
        JOIN {block_tasksplitter_subtask} sub ON ts.id = sub.tasksplitterid
        WHERE ts.userid = :userid
        AND ts.status = 'completed'";

        $params = [
            'userid' => $userid
        ];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Gets all open tasksplitter object ids with the name of the linked assign.
     *
     * @param int $userid User ID
     * @return array open tasksplitter object id and assign name
     */
    public static function get_open_tasksplitter_assign_names($userid) {
        global $DB;

        $sql = "SELECT ts.id AS tasksplitter_id, a.name AS assign_name
        FROM {block_tasksplitter} ts
        JOIN {assign} a ON ts.assignid = a.id
        WHERE ts.userid = :userid
        AND ts.status = 'open'";

        $params = [
            'userid' => $userid
        ];

        return $DB->get_records_sql($sql, $params);
    }



}
