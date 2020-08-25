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
$string['checkrequiredsettings'] = 'Habilitar Face Verification Quiz antes de iniciar o questionário';
$string['checkrequiredsettings_help'] = 'checkrequiredsettings_help';
$string['notrequiredoptionsettings'] = 'Desabilitado';
$string['requiredoptionsettings'] = 'Habilitado';
$string['fvquizheaderfacialcheck'] = 'Verificar face por foto - <a href="https://forms.gle/9Rxtm9XLN5RUtNUS8">avalie esse plugin</a>';
$string['fvquizheaderfacialregister'] = 'Registrar face por foto - <a href="https://forms.gle/9Rxtm9XLN5RUtNUS8">avalie esse plugin</a>';
$string['userhavetoberegistered'] = 'Por favor, registre sua face por foto antes de iniciar o questionário.';
$string['userhavetoverifyface'] = 'Por favor, Verique sua face por foto antes de iniciar o questionário.';
$string['youarenotsimilartotheuser'] = 'Desculpe, você não parece com a pessoa registrada. Tente novamente.';
$string['ok'] = 'ok';
$string['facialcheckbtn'] = '<button id="snapshot" class="btn btn-primary">Verificar Face</button>';
$string['facialregisterbtn'] = '<button id="snapshot" class="btn btn-primary">Registar Face</button>';
$string['failed:sesskey'] = 'Por favor, atualize a página e tente novamente';