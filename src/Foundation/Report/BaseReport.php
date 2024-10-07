<?php

namespace Nhd\Foundation\Foundation\Report;

class BaseReport {
    public static function sections() {
        return [
        //     [
        //         'title' => 'Title',
        //         'filters' => [
        //             'test' => [
        //                 'type' => 'text',
        //                 'label' => 'Label',
        //                 'traverse' => 'Traverse.Traverse',
        //                 'items' => [],
        //                 'query' => function($query, $value) {}
        //             ],
        //         ],
        //         'columns' => [
        //             'created_at_j' => [
        //                 'label' => 'Label',
        //                 'traverse' => 'Traverse.Traverse',
        //             ],
        //         ],
        //     ],
        ];
    }

    public static function result() {
        return [
            // [
            //     'label' => 'Label',
            //     'html' => true,
            //     'fit' => true,
            //     'value' => function($model) {
            //         return;
            //     }
            // ],
        ];
    } 

    public static function resultColumnsCount() {
        return count(static::result());
    } 
}