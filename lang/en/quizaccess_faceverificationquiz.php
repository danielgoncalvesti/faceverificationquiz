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
 * Strings for the quizaccess_faceverificationquiz plugin.
 *
 * @package    quizaccess_faceverification
 * @copyright  2020 Daniel Gonçalves da Silva <danielgoncalvesti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Face Verification Quiz';
$string['labelformessages'] = '';
$string['checkrequiredsettings'] = 'Enable face verification before quiz';
$string['checkrequiredsettings_help'] = 'checkrequiredsettings_help';
$string['notrequiredoptionsettings'] = 'Disabled';
$string['requiredoptionsettings'] = 'Enabled';
$string['fvquizheaderfacialcheck'] = 'Facial Check - <a href="https://forms.gle/9Rxtm9XLN5RUtNUS8">evaluate this plugin</a>';
$string['fvquizheaderfacialregister'] = 'Facial Register - <a href="https://forms.gle/9Rxtm9XLN5RUtNUS8">evaluate this plugin</a>';
$string['userhavetoberegistered'] = 'Please, register your face before start the quiz.';
$string['userhavetoverifyface'] = 'Please, check your face before start the quiz.';
$string['youarenotsimilartotheuser'] = 'Sorry, you do not look like the registered person.';
$string['ok'] = 'ok';
$string['facialcheckbtn'] = '<button id="snapshot" class="btn btn-primary" style="display:none;">To Check Face</button>';
$string['facialregisterbtn'] = '<button id="snapshot" class="btn btn-primary" style="display:none;">To Register Face</button>';
$string['failed:sesskey'] = 'Please, refresh the page and retry again';
$string['failed:fileisempty'] = 'File is empty';
$string['appkey_dropbox'] = 'App Key (Dropbox)';
$string['appsecret_dropbox'] = 'App Secret (Dropbox)';
$string['accesstoken_dropbox'] = 'Access Token (Dropbox)';
$string['foldername_dropbox'] = 'Folder name in Dropbox ';