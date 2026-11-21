<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.


/**
 * Settings of the factor, with those every factor has (on and weight).
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('factor_oksigeniaaty/description', '',
        new lang_string('settings:description', 'factor_oksigeniaaty')));
    $settings->add(new admin_setting_heading('factor_oksigeniaaty/settings', new lang_string('settings', 'moodle'), ''));

    $enabled = new admin_setting_configcheckbox('factor_oksigeniaaty/enabled',
        new lang_string('settings:enablefactor', 'tool_mfa'),
        new lang_string('settings:enablefactor_help', 'tool_mfa'), 0);
    $enabled->set_updatedcallback(function () {
        \tool_mfa\manager::do_factor_action('oksigeniaaty', get_config('factor_oksigeniaaty', 'enabled') ? 'enable' : 'disable');
    });
    $settings->add($enabled);

    $settings->add(new admin_setting_configtext('factor_oksigeniaaty/weight',
        new lang_string('settings:weight', 'tool_mfa'),
        new lang_string('settings:weight_help', 'tool_mfa'), 100, PARAM_INT));

    $settings->add(new admin_setting_configduration('factor_oksigeniaaty/validity',
        new lang_string('settings:validity', 'factor_oksigeniaaty'),
        new lang_string('settings:validity_help', 'factor_oksigeniaaty'), 3 * MINSECS, MINSECS));
}
