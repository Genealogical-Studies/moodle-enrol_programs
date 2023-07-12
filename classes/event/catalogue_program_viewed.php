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

namespace enrol_programs\event;

/**
 * Catalogue program viewed event.
 *
 * NOTE: this is learner view in catalogue only, management UI and My program does not trigger this.
 *
 * @package    enrol_programs
 * @copyright  2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class catalogue_program_viewed extends \core\event\base {
    /**
     * Helper for event creation.
     *
     * @param \stdClass $program
     *
     * @return catalogue_program_viewed|static
     */
    public static function create_from_program(\stdClass $program) {
        $context = \context::instance_by_id($program->contextid);
        $data = array(
            'context' => $context,
            'objectid' => $program->id,
        );
        /** @var static $event */
        $event = self::create($data);
        $event->add_record_snapshot('enrol_programs_programs', $program);
        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' viewed program in catalogue with id '$this->objectid'";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_catalogue_program_viewed', 'enrol_programs');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/enrol/programs/catalogue/program.php', ['id' => $this->objectid]);
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'enrol_programs_programs';
    }
}
