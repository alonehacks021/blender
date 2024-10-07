<?php

namespace Nhd\Foundation\Foundation\Model;

trait Filterable {
    public function scopeFilter($query) {
        $filters = self::filters();
        $rels = [];

        foreach($filters as $name => $filter) {
            if($filter['ignore'] ?? false) {
                continue;
            }

            $value = request($name);
            if((!empty($value) || $value == '0') && $value != '-1') {
                $type = is_array($filter) ? $filter['type'] : $filter;
                $pos = strrpos($name, '__');
                if($pos !== false) {
                    $rel = substr($name, 0, $pos);
                    $field = str_replace($rel.'__', '', $name);
                    $rel = str_replace('__', '.', $rel);

                    $rels[$rel][] = [
                        'name' => $name,
                        'field' => $field,
                        'type' => $type,
                        'value' => $value
                    ];
                }
                else {
                    $this->applyQuery($query, $name, $name, $type, $value);
                }
            }
        }

        foreach($rels as $rel => $items) {
            $query->whereHas($rel, function($query) use ($items) {
                foreach($items as $item) {
                    $this->applyQuery($query, $item['field'], $item['name'], $item['type'], $item['value']);
                }
            });
        }
    }

    public function scopeLivewireFilter($query, $sections = [], $data = []) {
        foreach($sections as $section) {
            $filters = $section['filters'];
            $rels = [];

            foreach($filters as $name => $filter) {
                if($filter['ignore'] ?? false) {
                    continue;
                }

                $value = $data[$name] ?? null;
                if((!empty($value) || $value == '0') && $value != '-1') {
                    $type = is_array($filter) ? $filter['type'] : $filter;
                    $customQuery = $filter['query'] ?? null;

                    $pos = strrpos($name, '__');
                    if($pos !== false) {
                        $rel = substr($name, 0, $pos);
                        $field = str_replace($rel.'__', '', $name);
                        $rel = str_replace('__', '.', $rel);

                        $rels[$rel][] = [
                            'name' => $name,
                            'field' => $field,
                            'type' => $type,
                            'value' => $value,
                            'custom_query' => $customQuery,
                        ];
                    }
                    else {
                        $this->applyQuery($query, $name, $name, $type, $value, $customQuery);
                    }
                }
            }

            foreach($rels as $rel => $items) {
                $query->with($rel);

                $query->whereHas($rel, function($query) use ($items) {
                    foreach($items as $item) {
                        $this->applyQuery($query, $item['field'], $item['name'], $item['type'], $item['value'], $item['custom_query']);
                    }
                });
            }
        }
    }

    public function scopeLivewireFilter2($query, $data = []) {
        $filters = self::filters();
        $rels = [];

            foreach($filters as $name => $filter) {
                if($filter['ignore'] ?? false) {
                    continue;
                }

                $value = $data[$name] ?? null;
                if((!empty($value) || $value == '0') && $value != '-1') {
                    $type = is_array($filter) ? $filter['type'] : $filter;
                    $customQuery = $filter['query'] ?? null;

                    $pos = strrpos($name, '__');
                    if($pos !== false) {
                        $rel = substr($name, 0, $pos);
                        $field = str_replace($rel.'__', '', $name);
                        $rel = str_replace('__', '.', $rel);

                        $rels[$rel][] = [
                            'name' => $name,
                            'field' => $field,
                            'type' => $type,
                            'value' => $value,
                            'custom_query' => $customQuery,
                        ];
                    }
                    else {
                        $this->applyQuery($query, $name, $name, $type, $value, $customQuery);
                    }
                }
            }

            foreach($rels as $rel => $items) {
                $query->with($rel);

                $query->whereHas($rel, function($query) use ($items) {
                    foreach($items as $item) {
                        $this->applyQuery($query, $item['field'], $item['name'], $item['type'], $item['value'], $item['custom_query']);
                    }
                });
            }
    }

    private function applyQuery($query, $field, $name, $type, $value, $customQuery = null) {
        if(!is_null($customQuery)) {
            $customQuery($query, $value);
        }
        else {
            switch($type) {
                case 'ajax': {
                    $query->where($field, $value);
                    break;
                }
                case 'text': {
                    $query->where($field, 'LIKE', '%'.str_replace(' ', '%', $value).'%');
                    break;
                }
                case 'number': {
                    $query->where($field, 'LIKE', '%'.str_replace(' ', '%', $value).'%');
                    break;
                }
                case 'select': {
                    if(is_array($value)) {
                        $query->whereIn($field, $value);
                    }
                    else {
                        $query->where($field, $value);
                    }
                    break;
                }
                case 'date': {
                    $query->whereBetween($field, ["$value 00:00:00", "$value 23:59:59"]);
                    break;
                }
                case 'date-range': {
                    $start = $value[0] ?? null;
                    $end = $value[1] ?? null;

                    if($start && $end) {
                        $query->whereBetween($field, [$start, $end]);
                    }
                    else if($start) {
                        $query->where($field, '>=', $start);
                    }
                    else if($end) {
                        $query->where($field, '<=', $end);
                    }

                    break;
                }
                case 'range': {
                    $start = $value[0] ?? null;
                    $end = $value[1] ?? null;

                    if($start && $end) {
                        $query->whereBetween($field, [$start, $end]);
                    }
                    else if($start) {
                        $query->where($field, '>=', $start);
                    }
                    else if($end) {
                        $query->where($field, '<=', $end);
                    }

                    break;
                }
            }
        }
    }

    public function filters() {
        return [];
    }
}
