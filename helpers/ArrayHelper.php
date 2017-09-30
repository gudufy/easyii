<?php
namespace yii\easyii\helpers;

use Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

class ArrayHelper extends yii\helpers\ArrayHelper
{
    /**
     * Builds a map (key-value pairs) from a multidimensional array or an array of objects.
     * The `$from` and `$to` parameters specify the key names or property names to set up the map.
     * Optionally, one can further group the map according to a grouping field `$group`.
     *
     * For example,
     *
     * ```php
     * $array = [
     *     ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
     *     ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
     *     ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
     * ];
     *
     * $result = ArrayHelper::map($array, 'id', ['name']);
     * // the result is:
     * // [
     * //     '123' => 'aaa',
     * //     '124' => 'bbb',
     * //     '345' => 'ccc',
     * // ]
     *
     * $result = ArrayHelper::map($array, 'id', ['name'], 'class');
     * // the result is:
     * // [
     * //     'x' => [
     * //         '123' => 'aaa',
     * //         '124' => 'bbb',
     * //     ],
     * //     'y' => [
     * //         '345' => 'ccc',
     * //     ],
     * // ]
     * ```
     *
     * @param array $array
     * @param string|\Closure $from
     * @param string|\Closure $to
     * @param string|\Closure $group
     * @return array
     */
     public static function mapMulti($array, $from, $tos, $splitChart= '/', $group = null)
     {
         $result = [];
         foreach ($array as $element) {
             $key = static::getValue($element, $from);
             $value = [];
             foreach($tos as $to){
                 if (($val = static::getValue($element, $to)))
                    $value[] = $val;
             }
             if ($group !== null) {
                 $result[static::getValue($element, $group)][$key] = join($splitChart,$value);
             } else {
                 $result[$key] = join($splitChart,$value);
             }
         }
 
         return $result;
     }
}