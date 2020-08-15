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
 * Snapshot upload handler
 *
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package    quizaccess_faceverificationquiz
 * @copyright  2020 Daniel Gonçalves da Silva <danielgoncalvesti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
global $SESSION;
define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true);

require_once(__DIR__ . '/../../../../config.php');

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/gdlib.php");
$PAGE->set_url('/mod/quiz/accessrule/faceverificationquiz/upload.php');

require_login(get_site(), true, null, true, true);
$sessionid = required_param('sesskey', PARAM_RAW);
$facevalues = required_param('descriptor', PARAM_RAW);
$euclidean_distance = required_param('euclidean_distance', PARAM_RAW);
$courseid = required_param('courseid', PARAM_RAW);
$quizid = required_param('quizid', PARAM_RAW);
$facedetectionscore = required_param('facedetectionscore', PARAM_RAW);
 
$systemcontext = context_system::instance();
$array = ['errors' => [], 'status' => false];

echo $OUTPUT->header(); // Send headers.

if (!confirm_sesskey($sessionid)) {
    $array['errors'][] = get_string('failed:sesskey', 'faceverificationquiz');
}
print_r($array['errors']);

if (empty($array['errors'])) {

    $context = context_user::instance($USER->id, MUST_EXIST);

    $faceverification = new stdClass();
    $faceverification->username = $USER->username;
    $faceverification->sesskey = $sessionid;
    $faceverification->quizid = $quizid;
    $faceverification->courseid = $courseid;
    $faceverification->euclidean_distance = $euclidean_distance;
    $faceverification->facevalues = $facevalues;
    $faceverification->facedetectionscore = $facedetectionscore;
    $faceverification->timecreated = time();
    $DB->insert_record('fvquiz_validation', $faceverification);
    $SESSION->faceverification = $faceverification;

    $array['status'] = true;

}

echo json_encode($array);