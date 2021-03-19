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
$file = required_param('file', PARAM_RAW);
$sessionid = required_param('sesskey', PARAM_RAW);
$descriptor_face = required_param('descriptor', PARAM_RAW);
$courseid = required_param('courseid', PARAM_RAW);

$systemcontext = context_system::instance();
$array = ['errors' => [], 'status' => false];

echo $OUTPUT->header(); // Send headers.

if ($CFG->disableuserimages) {

    $array['errors'][] = get_string('failed:disableuserimages', 'faceverificationquiz');

} else if (!has_capability('moodle/user:editownprofile', $systemcontext)) {

    $array['errors'][] = get_string('failed:permission_editownprofile', 'faceverificationquiz');

} else if (!confirm_sesskey($sessionid)) {

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

    //set file to the dropbox
    $dropboxFile = new DropboxFile($tempfile);
    //Configure Dropbox Application
    $app = new DropboxApp("9d8ukwyihgvpmyl", "00v4mdt2xc5zpj4", "ysIiD9OB6K0AAAAAAAAAAa5mO0TRGhL8j7ouX6ORHiG2YabMxG-2m2jyDypS-bMQ");
    //Configure Dropbox service
    $dropbox = new Dropbox($app);

    $course = $DB->get_record('course', array('id' => $courseid));
    $shortname_course = str_replace(' ', '', $course->shortname);
    $date = new \DateTime('now');
    $filename = $date->format('Y-m-d-H:i:si');
    $pathFile = "/". $shortname_course . "/" . "cadastro" . "/" . $USER->username . "/" . $filename . ".png"; 
    $fileDropBox = $dropbox->upload($dropboxFile, $pathFile, ['autorename' => true]); 
    // $fileDropBox->getName();

    $newpicture = (int)process_new_icon($context, 'user', 'icon', 0, $tempfile);
    if ($newpicture != $USER->picture) {
        $DB->set_field('user', 'picture', $newpicture, ['id' => $USER->id]);

        $faceidrecord = new stdClass();
        $faceidrecord->username = $USER->username;
        $faceidrecord->facevalues = $descriptor_face;
        $faceidrecord->pathfiledropbox = $pathFile;
        $faceidrecord->timecreated = time();
        $DB->insert_record('fvquiz_registered', $faceidrecord);

        $array['status'] = true;
    } else {
        $array['errors'][] = get_string('failed', 'faceverificationquiz');
    }

    @unlink($tempfile);
}

echo json_encode($array);