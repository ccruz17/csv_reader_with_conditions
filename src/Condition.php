<?php
/**
 * Class for the object Conditions
 *
 * This files allow to create an object of Condition,
 *
 * @category   CCRUZ CSV Reader
 * @package    ccruz17/csv_reader_with_conditions
 * @author     Christian Cruz Garrido <ccruz.ga7@gmail.com>
 * @copyright  2016-2017 CCRUZ
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt  GNU GENERAL PUBLIC LICENSE V3
 * @link       https://github.com/ccruz17/csv_reader_with_conditions
 */

namespace ccruz17;

class Condition {

    private $type = '';
    private $condition = array();
    private $apply_for = '';

    /**
     * Construct for this class
     * @param [string] $type      Type de condition, avaliable
     * @param [array] $condition [description]
     * @param [type] $apply_for [description]
     */

    public function __construct($type, $condition, $apply_for) {
        $this->type = $type;
        $this->condition = $condition;
        $this->apply_for = $apply_for;
    }

    /**
     * Setter for type
     * @param string $type Set type of condition
     */
    public function set_type($type) {
        $this->type = $type;
    }

    /**
     * Getter for type
     * @return string Return type of condition
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Setter for condition
     * @param array $condition Set a condition
     */
    public function set_condition($condition) {
        $this->condition = $condition;
    }

    /**
     * Getter for condition
     * @return array Return the condition
     */
    public function get_condition() {
        return $this->condition;
    }

    /**
     * Setter for apply_for
     * @param string $apply_for only accept the value: targets or values
     */
    public function set_apply_for($apply_for) {
        $this->apply_for = $apply_for;
    }

    /**
     * Getter for apply_for
     * @return string Return apply_for, only return the value: targets or values
     */
    public function get_apply_for() {
        return $this->apply_for;
    }

    public static function exist_condition($type, $for, $conditions) {
        $exist = false;
        foreach ($conditions as $key => $val) {
            $exist = $val->get_type() == $type && $val->get_apply_for() == $for ? true : $exist;
        }
        return $exist;

    }

}
