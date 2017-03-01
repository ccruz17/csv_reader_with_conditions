<?php
/**
 * Class Csv_reader, you can read and excel file
 *
 * This class can extract information from a target or multiple targets and each of them with one or more values
 *
 * @category   CCRUZ CSV Reader
 * @package    ccruz17/csv_reader_with_conditions
 * @author     Christian Cruz Garrido <ccruz.ga17@gmail.com>
 * @copyright  2016-2017 CCRUZ
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt  GNU GENERAL PUBLIC LICENSE V3
 * @link       https://github.com/ccruz17/csv_reader_with_conditions
 */

namespace ccruz17;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use PHPExcel_Cell_DataType;
use ccruz17\Condition;

class Csv_reader
{
    private $APPLY_FOR_TARGETS = 'targets';
    private $APPLY_FOR_VALUES = 'values';

    /**
    * @param string $file           Path of file to read
    * @param object $configuration  Object with configuration required, multiple_targets, row_targets,
    *                               column_targets, multiple_values, row_values, column_values, sheet
    * @param array $conditions      Array of Objects Conditions, example: array(ObjectCondition, ObjectCondition)
    */
    public function read($file, $configuration, $conditions = array()) {
        $objPHPExcel = PHPExcel_IOFactory::load($file);
        $objWorksheet = null;
        if($configuration->sheet != null) {
            $sheets = $objPHPExcel->getSheetNames();
            if(in_array($configuration->sheet, $sheets)) {
                $objWorksheet = $objPHPExcel->setActiveSheetIndexByName($configuration->sheet);
            } else {
                $return_targets_values[] = array('target' => $file, 'values' => array());
                return $return_targets_values;
            }
        } else {
            $objPHPExcel->setActiveSheetIndex(0);
            $objWorksheet = $objPHPExcel->getActiveSheet();
        }

        $data = array();

        if($configuration->multiple_values) {
            //Detect limits of sheet
            $highestRow         = $objWorksheet->getHighestRow();
            $highestColumn      = $objWorksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            //get row and column for targets
            $column_targets = PHPExcel_Cell::columnIndexFromString($configuration->column_targets) - 1;
            $row_targets = $configuration->row_targets;
            //get row and column for values
            $column_values = PHPExcel_Cell::columnIndexFromString($configuration->column_values) -1;
            $row_values = $configuration->row_values;

            //Check if exist conditions for vales of the target
            $exist_match_condition_for_values = Condition::exist_condition('match_column', $this->APPLY_FOR_VALUES, $conditions);
            $exist_match_current_condition_for_values = Condition::exist_condition('match_current', $this->APPLY_FOR_VALUES, $conditions);
            $exist_sum_condition_for_targets = Condition::exist_condition('sum_rows', $this->APPLY_FOR_TARGETS, $conditions);
            $exist_sum_condition_for_values = Condition::exist_condition('sum_rows', $this->APPLY_FOR_VALUES, $conditions);
            $exist_length_condition_for_values = Condition::exist_condition('length_values', $this->APPLY_FOR_VALUES, $conditions);
            $exist_length_condition_for_targets = Condition::exist_condition('length_targets', $this->APPLY_FOR_TARGETS, $conditions);

            $return_targets_values = array();

            if($exist_length_condition_for_targets) {
                foreach ($conditions as $key => $val) {
                    if($val->get_type() == 'length_targets' && $val->get_apply_for() == $this->APPLY_FOR_TARGETS) {
                        $condition = $val->get_condition();
                        $highestRow = $condition['length'];
                    }
                }
            }

            if(!$configuration->multiple_targets){
                $cell_targets = $objWorksheet->getCellByColumnAndRow($column_targets, $row_targets);
                $target = $cell_targets->getValue();
            }

            for ($row = $row_targets; $row <= $highestRow; $row++) {
                $current_row = $row;
                if($configuration->multiple_targets){
                    $cell_targets = $objWorksheet->getCellByColumnAndRow($column_targets, $current_row);
                    $target = $cell_targets->getValue();
                }

                if($exist_sum_condition_for_values) {
                    foreach ($conditions as $key => $val) {
                        if($val->get_type() == 'sum_rows' && $val->get_apply_for() == $this->APPLY_FOR_VALUES) {
                            $current_row = $current_row + $val->get_condition()['sum'];
                        }
                    }
                }

                if($exist_match_condition_for_values) {
                    $continue = true;
                    foreach ($conditions as $key => $val) {
                        if($val->get_type() == 'match_column' && $val->get_apply_for() == $this->APPLY_FOR_VALUES) {
                            $condition = $val->get_condition();
                            $column_condition = PHPExcel_Cell::columnIndexFromString($condition['column']) - 1;
                            $value_condition = $objWorksheet->getCellByColumnAndRow($column_condition, $current_row)->getValue();
                            if($value_condition != $condition['value']) {
                                $continue = false;
                            }
                        }
                    }
                    if(!$continue) { continue; }
                }

                $length_values = $highestColumnIndex;
                if($exist_length_condition_for_values) {
                    foreach ($conditions as $key => $val) {
                        if($val->get_type() == 'length_values' && $val->get_apply_for() == $this->APPLY_FOR_VALUES) {
                            $condition = $val->get_condition();
                            $length_values = $condition['length'];
                        }
                    }
                }

                $values = array();


                for ($col = $column_values; $col < $column_values+$length_values; ++ $col) {
                    $cell = $objWorksheet->getCellByColumnAndRow($col, $current_row);

                    if($exist_match_current_condition_for_values) {
                        foreach ($conditions as $key => $val) {
                            if($val->get_type() == 'match_current' && $val->get_apply_for() == $this->APPLY_FOR_VALUES) {
                                $match_value = $val->get_condition()['value'];
                                if($match_value == $cell->getValue()) {
                                    $values[$col] = $cell->getValue();
                                }
                            }
                        }
                    } else {
                        $values[$col] = $cell->getValue();
                    }
                }

                $return_targets_values[] = array('target' => (string)$target, 'values' => $values);

                if($exist_sum_condition_for_targets) {
                    foreach ($conditions as $key => $val) {
                        if($val->get_type() == 'sum_rows' && $val->get_apply_for() == $this->APPLY_FOR_TARGETS) {
                            $row = $row + $val->get_condition()['sum'];
                        }
                    }
                }
            }
            return $return_targets_values;
        } else {

        }
    }
}
