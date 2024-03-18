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

namespace enrol_programs\local\form;

use enrol_programs\local\content\set;
use enrol_programs\local\content\top;

/**
 * Edit program content item.
 *
 * @package    enrol_programs
 * @copyright  2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class item_set_edit extends \local_openlms\dialog_form {
    protected function definition() {
        $mform = $this->_form;
        /** @var set $set */
        $set = $this->_customdata['set'];

        if ($set instanceof top) {
            $mform->addElement('static', 'fullname', get_string('fullname'), format_string($set->get_fullname()));
        } else {
            $mform->addElement('text', 'fullname', get_string('fullname'), 'maxlength="254" size="50"');
            $mform->setType('fullname', PARAM_TEXT);
            $mform->setDefault('fullname', format_string($set->get_fullname()));
            $mform->addRule('fullname', get_string('required'), 'required', null, 'client');
        }

        if (!$set instanceof top) {
            $mform->addElement('text', 'points', get_string('itempoints', 'enrol_programs'));
            $mform->setType('points', PARAM_INT);
            $mform->setDefault('points', $set->get_points());
        }

        $stypes = set::get_sequencetype_types();
        $mform->addElement('select', 'sequencetype', get_string('sequencetype', 'enrol_programs'), $stypes);
        $mform->setDefault('sequencetype', $set->get_sequencetype());

        $mform->addElement('text', 'minprerequisites', $stypes[set::SEQUENCE_TYPE_ATLEAST]);
        $mform->setType('minprerequisites', PARAM_INT);
        $mform->hideIf('minprerequisites', 'sequencetype', 'noteq', set::SEQUENCE_TYPE_ATLEAST);
        if ($set->get_sequencetype() === set::SEQUENCE_TYPE_ATLEAST) {
            $minprerequisites = $set->get_minprerequisites();
        } else {
            $minprerequisites = count($set->get_children());
        }
        $mform->setDefault('minprerequisites', $minprerequisites);

        $mform->addElement('text', 'minpoints', $stypes[set::SEQUENCE_TYPE_MINPOINTS]);
        $mform->setType('minpoints', PARAM_INT);
        $mform->hideIf('minpoints', 'sequencetype', 'noteq', set::SEQUENCE_TYPE_MINPOINTS);
        if ($set->get_sequencetype() === set::SEQUENCE_TYPE_MINPOINTS) {
            $minpoints = $set->get_minpoints();
        } else {
            $minpoints = 0;
            foreach ($set->get_children() as $child) {
                $minpoints += $child->get_points();
            }
        }
        $mform->setDefault('minpoints', $minpoints);

        $mform->addElement('duration', 'completiondelay', get_string('completiondelay', 'enrol_programs'),
            ['optional' => true, 'defaultunit' => DAYSECS]);
        $mform->setDefault('completiondelay', $set->get_completiondelay());

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $set->get_id());

        $this->add_action_buttons(true, get_string('updateset', 'enrol_programs'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        /** @var set $set */
        $set = $this->_customdata['set'];

        if (!$set instanceof top) {
            if ($data['points'] < 0) {
                $errors['points'] = get_string('error');
            }
            if (trim($data['fullname']) === '') {
                $errors['fullname'] = get_string('required');
            }
        }

        if ($data['sequencetype'] === set::SEQUENCE_TYPE_ATLEAST) {
            if ($data['minprerequisites'] <= 0) {
                $errors['minprerequisites'] = get_string('required');
            }
        }  else if ($data['sequencetype'] === set::SEQUENCE_TYPE_MINPOINTS) {
            if ($data['minpoints'] <= 0) {
                $errors['minpoints'] = get_string('required');
            }
        }

        return $errors;
    }
}
