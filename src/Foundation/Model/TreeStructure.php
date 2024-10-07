<?php

namespace Nhd\Foundation\Foundation\Model;

trait TreeStructure {
    public static function bootTreeStructure() {
        self::saved(function($model) {
            self::flushEventListeners();

            $newPath = $model->parent ? 
                $model->parent->path . "$model->id/" : "/$model->id/";

            $rootId = $model->parent ? 
                $model->parent->root_id : $model->id;

            $model->path = $newPath;
            $model->depth = substr_count($newPath, '/') - 2;
            $model->root_id = $rootId;
            
            $model->save();
        });

        self::deleting(function($model) {
            self::flushEventListeners();

            $model->children()->delete();
        });
    }

    public function parent() {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children($all = true) {
        $children = $this->where('id', '<>', $this->id)->where('path', 'LIKE', '%/' . $this->id . '/%');

        if(!$all) {
            $children = $children->where('depth', '<=', $this->depth + 1);
        }

        return $children;
    }

    public function parents() {
        return $this->where('id', '<>', $this->id)
            ->where('path', 'LIKE', '%/' . $this->id . '/%')
            ->where('depth', '<', $this->depth)
            ->orderBy('depth', 'ASC');
    }

    public function getChildrenAttribute() {
        return $this->children(true)->get();
    }

    public function getParentsAttribute() {
        return $this->parents()->get();
    }
}