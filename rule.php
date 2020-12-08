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
 * faceverificationquiz access plugin
 *
 * @package    quizaccess_faceverificationquiz
 * @copyright  2020 Daniel Gonçalves da Silva <danielgoncalvesti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');


/**
 * 
 *
 * @copyright   2020 Daniel Goncalves <danielgoncalvesti@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_faceverificationquiz extends quiz_access_rule_base {

    var $is_registered_face = false;

    public function is_preflight_check_required($attemptid) {
        return empty($attemptid);
    }

    public function add_preflight_check_form_fields(mod_quiz_preflight_check_form $quizform,
            MoodleQuickForm $mform, $attemptid) {
                
        // var_dump($this->quiz->course);
        global $PAGE, $CFG, $USER, $DB;
        $username = $USER->username;
        $type_of_access;
        $is_registered_face = $DB->record_exists('fvquiz_registered', array('username'=>$username));
        if ($is_registered_face){
            $type_of_access = 'verification';
        } else {
            $type_of_access = 'registered';
        }
        
        // $face_registered = $DB->get_record('quizaccess_faceid', array('username' => $username), 'faceid');
        $array_face_registered = $DB->get_records_sql("
                        SELECT facevalues FROM {fvquiz_registered} WHERE username = :username ORDER BY timecreated DESC", ['username' => $username]);
        $arrayvalues_face_registered = array_values($array_face_registered);
        if(empty($arrayvalues_face_registered)){
            $arrayvalues_face_registered[0] = ''; 
        }
        // $url_parameters_str = http_build_query($_GET);
        // parse_str(html_entity_decode($url_parameters_str), $url_parameters_arr);

        $mform->addElement('static', 'notifications', '',
                get_string('labelformessages', 'quizaccess_faceverificationquiz'));

        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/quiz/accessrule/faceverificationquiz/lib/face-api.js'), true);

        $jsmodule = [
            'name' => 'faceverificationquiz',
            'fullpath' => '/mod/quiz/accessrule/faceverificationquiz/lib/module.js',
            'requires' => ['io-base'],
        ];

        $PAGE->requires->js_init_call('M.faceverificationquiz.init', [
            $CFG->wwwroot . '/mod/quiz/accessrule/faceverificationquiz/swf/snapshot.swf?' . time(),
            $CFG->wwwroot . '/mod/quiz/accessrule/faceverificationquiz/swf/expressInstall.swf',
            [
                'sessionid' => $USER->sesskey,
                'uploadPath' => $CFG->wwwroot . '/mod/quiz/accessrule/faceverificationquiz/ajax.php',
                'faceverificationPath' => $CFG->wwwroot . '/mod/quiz/accessrule/faceverificationquiz/ajaxverification.php', 
                'face_registered' => $arrayvalues_face_registered[0],
                'type_of_access' => $type_of_access,
                'coursepage_url' =>$CFG->wwwroot,
                'quizid' => $this->quiz->id,
                'courseid' => $this->quiz->course
            ],
        ], false, $jsmodule);

        // $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/quiz/accessrule/faceverificationquiz/lib/main.js'), true);
        $mform->addElement('html',
        '<style>
        .loader {
            position: absolute;
            top: 30%;
            left: 35%;
            transform: translate(-50%, -50%);
          border: 16px solid #f3f3f3;
          border-radius: 50%;
          border-top: 16px solid #3498db;
          width: 120px;
          height: 120px;
          -webkit-animation: spin 2s linear infinite; /* Safari */
          animation: spin 2s linear infinite;
        }
        
        /* Safari */
        @-webkit-keyframes spin {
          0% { -webkit-transform: rotate(0deg); }
          100% { -webkit-transform: rotate(360deg); }
        }
        
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
        </style>
        ');
        if ($is_registered_face){
            $mform->addElement('header', 'fvquizheader', get_string('fvquizheaderfacialcheck', 'quizaccess_faceverificationquiz'));
            $mform->addElement('html', 
            '<div id="snapshotholder_webrtc" style="display: none; position: relative;overflow: hidden;" >
            <div id="cssloader" style="height: 320px; width: 480px; position: absolute; top:0;left:0;"><div class="loader"></div></div>
            <canvas id="render" width="480px" height="320px" style="position: absolute; top: 0;left: 0;"></canvas>
            <video id="videostreaming" autoplay playsinline style="top: 0;left: 0;opacity: 10%; width: 480px" width="480" height="320"></video>
            <canvas id="canvasFaceCrop" style="z-index:100; margin-left: 400px;display:none;" width="480" height="320" ></canvas>
            <canvas id="canvasFrame" style="position: relative; top:0; left:0; z-index:1; display:none;" width="480px" height="320px" ></canvas>
            <img id="img_preview" width="200px" src="">
            <div id="previewholder" style="display: none;">
                <canvas id="preview" width="" height="" style="display: none;"></canvas>
            </div>
            </div>');
            $mform->addElement('html', get_string('facialcheckbtn', 'quizaccess_faceverificationquiz'));
        } else {
            $mform->addElement('header', 'fvquizheader', get_string('fvquizheaderfacialregister', 'quizaccess_faceverificationquiz'));
            $mform->addElement('html', 
            '<div id="snapshotholder_webrtc" style="display: none; position: relative;overflow: hidden;" >
                <div id="cssloader" style="height: 320px; width: 480px; position: absolute; top:0;left:0;"><div class="loader"></div></div>
                <canvas id="render" width="480px" height="320px" style=" position: absolute; top: 0;left: 0;"></canvas>
                <video id="videostreaming" autoplay playsinline style="top: 0;left: 0;opacity: 10%; width: 480px" width="480" height="320"></video>
                <canvas id="canvasFaceCrop" style="z-index:100; margin-left: 400px;display:none;" width="480" height="320" ></canvas>
                <canvas id="canvasFrame" style="position: relative; top:0; left:0; z-index:1; display:none;" width="480px" height="320px" ></canvas>
                <img id="img_preview" width="200px" src="">
                <div id="previewholder" style="display: none;">
                    <canvas id="preview" width="" height="" style="display: none;"></canvas>
                </div>
            </div>');
            $mform->addElement('html', get_string('facialregisterbtn', 'quizaccess_faceverificationquiz'));
        }
    }

    public function validate_preflight_check($data, $files, $errors, $attemptid) {
        global $DB, $USER;

        $username = $USER->username;
        // if ($this->is_registered_face){ //temos que validar
            
        // } else { // temos que cadastrar a face do usuário

        //     if (!$DB->record_exists('fvquiz_registered', array('username'=>$this->username))) {
        //         $errors['notifications'] = get_string('userhavetoberegistered', 'quizaccess_faceverificationquiz');
        //     } else {
        //         $errors['notifications'] = get_string('userhavetoberegistered', 'quizaccess_faceverificationquiz');
        //     }
            
        // }
        $timenow = strtotime('+1 minutes', time());;
        $last_ten_minutes = strtotime('-10 minutes', time());
        $is_user_registered = $DB->record_exists('fvquiz_registered', array('username'=>$username));
        $user_checked_face;
        $arrayvalues_user_checked_faces = array_values($DB->get_records_sql(
            'SELECT * FROM {fvquiz_validation} where expired = 0 and timecreated > :last_ten_minutes and  timecreated < :timenow  ORDER BY timecreated DESC', 
            ['timenow'=>$timenow, 'last_ten_minutes'=>$last_ten_minutes]));
        if (!empty($arrayvalues_user_checked_faces)){
            // print_r($arrayvalues_user_checked_faces[0]->euclidean_distance);
            $user_checked_face = $arrayvalues_user_checked_faces[0];
        }
        
        // $arrayvalues_user_checked_faces = array_values($user_checked_faces);

        if (!$is_user_registered) {
            $errors['notifications'] = get_string('userhavetoberegistered', 'quizaccess_faceverificationquiz');
            
        } else {
            if(empty($arrayvalues_user_checked_faces)){
                $errors['notifications'] = get_string('userhavetoverifyface', 'quizaccess_faceverificationquiz');
            } else {
                if ($user_checked_face->euclidean_distance < 0.6) {
                    $DB->set_field('fvquiz_validation', 'expired', 1, array('id'=>$user_checked_face->id));
                    // $errors['notifications'] = get_string('ok', 'quizaccess_faceverificationquiz' );
                } else {
                    $errors['notifications'] = get_string('youarenotsimilartotheuser', 'quizaccess_faceverificationquiz' );
                    $DB->set_field('fvquiz_validation', 'expired', 1, array('id'=>$user_checked_face->id));
                }
            }   
        }
        return $errors;
    }

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {

        if (empty($quizobj->get_quiz()->fvquizenabled)) {
            return null;
        }
        return new self($quizobj, $timenow);
    }

    public static function add_settings_form_fields(
            mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        $mform->addElement('select', 'fvquizenabled',
                get_string('checkrequiredsettings', 'quizaccess_faceverificationquiz'),
                array(
                    0 => get_string('notrequiredoptionsettings', 'quizaccess_faceverificationquiz'),
                    1 => get_string('requiredoptionsettings', 'quizaccess_faceverificationquiz'),
                ));
        $mform->addHelpButton('fvquizenabled',
                'checkrequiredsettings', 'quizaccess_faceverificationquiz');
        
        // $mform->addElement('html','<input type="range" min="0" max="100">');
        
    }

    public static function save_settings($quiz) {
        global $DB;
        if (empty($quiz->fvquizenabled)) {
            $DB->delete_records('fvquiz_quizaccess', array('quizid' => $quiz->id));
        } else {
            if (!$DB->record_exists('fvquiz_quizaccess', array('quizid' => $quiz->id))) {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->fvquizenabled = 1;
                $DB->insert_record('fvquiz_quizaccess', $record);
            }
        }
    }

    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('fvquiz_quizaccess', array('quizid' => $quiz->id));    }

    public static function get_settings_sql($quizid) {
        return array(
            'fvquizenabled',
            'LEFT JOIN {fvquiz_quizaccess} fvquiz_quizaccess ON fvquiz_quizaccess.quizid = quiz.id',
            array());
    }
}