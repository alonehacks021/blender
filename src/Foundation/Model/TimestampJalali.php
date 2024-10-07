<?php

namespace Nhd\Foundation\Foundation\Model;

trait TimestampJalali {
    public function getCreatedAtJAttribute() {
        return $this->created_at ? jdate()->fromDateTime($this->created_at)->format('Y/m/d H:i:s') : null;
    }

    public function getUpdatedAtJAttribute() {
        return $this->updated_at ? jdate()->fromDateTime($this->updated_at)->format('Y/m/d H:i:s') : null;
    }

    public function getJalali($field, $format = 'Y/m/d H:i') {
        return $this->$field ? jdate()->fromDateTime($this->$field)->format($format) : null;
    } 
}