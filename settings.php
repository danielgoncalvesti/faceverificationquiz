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
 * Settings
 *
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 **/
defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configtext(
    'quizaccess_faceverificationquiz/appkey_dropbox',
    new lang_string('appkey_dropbox', 'quizaccess_faceverificationquiz'),
    '',
    ''
));

$settings->add(new admin_setting_configtext(
    'quizaccess_faceverificationquiz/appsecret_dropbox',
    new lang_string('appsecret_dropbox', 'quizaccess_faceverificationquiz'),
    '',
    ''
));

$settings->add(new admin_setting_configtext(
    'quizaccess_faceverificationquiz/accesstoken_dropbox',
    new lang_string('accesstoken_dropbox', 'quizaccess_faceverificationquiz'),
    '',
    ''
));

$settings->add(new admin_setting_configtext(
    'quizaccess_faceverificationquiz/foldername_dropbox',
    new lang_string('foldername_dropbox', 'quizaccess_faceverificationquiz'),
    '',
    ''
));