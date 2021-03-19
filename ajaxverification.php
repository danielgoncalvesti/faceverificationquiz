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

define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true);

require_once(__DIR__ . '/../../../../config.php');

defined('MOODLE_INTERNAL') || die;

require 'vendor/autoload.php';
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

require_once("$CFG->libdir/gdlib.php");
$PAGE->set_url('/mod/quiz/accessrule/faceverificationquiz/upload.php');

require_login(get_site(), true, null, true, true);
$sessionid = required_param('sesskey', PARAM_RAW);
$euclidean_distance = required_param('euclidean_distance', PARAM_RAW);
$courseid = required_param('courseid', PARAM_RAW);
$quizid = required_param('quizid', PARAM_RAW);
$facevalues = required_param('descriptor', PARAM_RAW);
$facedetectionscore = required_param('facedetectionscore', PARAM_RAW);
$file = required_param('file', PARAM_RAW);
 
$systemcontext = context_system::instance();
$array = ['errors' => [], 'status' => false];

echo $OUTPUT->header(); // Send headers.

if (!confirm_sesskey($sessionid)) {
    $array['errors'][] = get_string('failed:sesskey', 'faceverificationquiz');
}

if (empty($array['errors'])) {

    if (stristr($file, 'base64,')) {
        // Convert webrtc.
        $file = explode('base64,', $file);
        $file = end($file);
    }

    // Decode.
    $file = base64_decode($file);

    if (empty($file)) {
        $array['errors'][] = get_string('failed', 'faceverificationquiz');
        die(json_encode($array));
    }  
    
    $context = context_user::instance($USER->id, MUST_EXIST);

    $tempfile = tempnam(sys_get_temp_dir(), 'faceverificationquiz');
    file_put_contents($tempfile, $file);

    $dropboxFile = new DropboxFile($tempfile);

    //Configure Dropbox Application
    $app = new DropboxApp("9d8ukwyihgvpmyl", "00v4mdt2xc5zpj4", "ysIiD9OB6K0AAAAAAAAAAa5mO0TRGhL8j7ouX6ORHiG2YabMxG-2m2jyDypS-bMQ");
    //Configure Dropbox service
    $dropbox = new Dropbox($app);

    $course = $DB->get_record('course', array('id' => $courseid));
    $shortname_course = str_replace(' ', '', $course->shortname);
    $quiz = $DB->get_record('quiz', array('id' => $quizid));
    $quizname = str_replace(' ', '', $quiz->name);
    $date = new \DateTime('now');
    $filename = $date->format('Y-m-d-H:i:si');
    $pathFile = "/". $shortname_course . "/" . "atividades" . "/" . $quizname . "/" . $USER->username . "/" . $filename . ".png";
    // Upload file
    $fileDropBox = $dropbox->upload($dropboxFile, $pathFile, ['autorename' => true]);

    $faceverification = new stdClass();
    $faceverification->username = $USER->username;
    $faceverification->sesskey = $sessionid;
    $faceverification->quizid = $quizid;
    $faceverification->courseid = $courseid;
    $faceverification->euclidean_distance = $euclidean_distance;
    $faceverification->facevalues = $facevalues;
    $faceverification->facedetectionscore = $facedetectionscore;
    $faceverification->pathfiledropbox = $pathFile;
    $faceverification->timecreated = time();
    $DB->insert_record('fvquiz_validation', $faceverification);

    $array['status'] = true;

    @unlink($tempfile);
}

echo json_encode($array);