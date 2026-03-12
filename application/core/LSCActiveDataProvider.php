<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

class LSCActiveDataProvider extends CActiveDataProvider
{
    
    /**
     * Fetches the data from the persistent data storage.
     *
     * Method content is copied from original CActiveDataProvider class, except for decryption part

     * @return array list of data items
     */
    protected function fetchData()
    {
        $criteria = clone $this->getCriteria();

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->setItemCount($this->getTotalItemCount());
            $pagination->applyLimit($criteria);
        }

        $baseCriteria = $this->model->getDbCriteria(false);

        if (($sort = $this->getSort()) !== false) {
            // set model criteria so that CSort can use its table alias setting
            if ($baseCriteria !== null) {
                $c = clone $baseCriteria;
                $c->mergeWith($criteria);
                $this->model->setDbCriteria($c);
            } else {
                $this->model->setDbCriteria($criteria);
            }
            $sort->applyOrder($criteria);
        }

        $this->model->setDbCriteria($baseCriteria !== null ? clone $baseCriteria : null);
        $data = $this->model->findAll($criteria);
        
        // decryption
        if ($this->model->bEncryption) {
            foreach ($data as $row) {
                if (!empty($row)) {
                    $row->decrypt();
                }

                // decrypt all related models
                foreach ($row->relations() as $key => $related) {
                    if ($row->hasRelated($key) && !is_null($row->$key)) {
                        $row->$key->decrypt();
                    }
                }
            }
        }

        $this->model->setDbCriteria($baseCriteria);  // restore original criteria
        return $data;
    }
    

    /**
     * Fetches the data item keys from the persistent data storage.
     * @return array list of data item keys.
     */
    protected function fetchKeys()
    {
        $keys = array();
        foreach ($this->getData() as $i => $data) {
            $key = $this->keyAttribute === null ? $data->getPrimaryKey() : $data->{$this->keyAttribute};
            $keys[$i] = is_array($key) ? implode(',', $key) : $key;
        }
        return $keys;
    }

    /**
     * Calculates the total number of data items.
     * @return integer the total number of data items.
     */
    protected function calculateTotalItemCount()
    {
        $baseCriteria = $this->model->getDbCriteria(false);
        if ($baseCriteria !== null) {
            $baseCriteria = clone $baseCriteria;
        }
        $count = $this->model->count($this->getCountCriteria());
        $this->model->setDbCriteria($baseCriteria);
        return $count;
    }
}
