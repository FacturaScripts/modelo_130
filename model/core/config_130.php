<?php

/*
 * This file is part of modelo_130
 * Copyright (C) 2017 Pablo Zerón Gea pablozg@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\model;

/**
 * Configuración modelo 130.
 *
 * @author Pablo Zerón Gea <pablozg@gmail.com>
 */
class config_130 extends \fs_model
{

    /**
     * Clave primaria.
     * @var type
     */
    public $codejercicio;

    /**
     * ID del asiento generado.
     * @var type
     */
    public $hipoteca;
    public $descuento100;
    public $difjust;

    public function __construct($r = false)
    {
        parent::__construct('conf_mod130');
        if ($r) {
            $this->codejercicio = $r['codejercicio'];
            $this->hipoteca = $r['hipoteca'];
            $this->descuento100 = $r['descuento100'];
            $this->difjust = $r['difjust'];
        } else {
            $this->codejercicio = null;
            $this->hipoteca = true;
            $this->descuento100 = false;
            $this->difjust = false;
        }
    }

    protected function install()
    {
        return '';
    }

    public function url()
    {
        return 'index.php?page=modelo_130#config';
    }

    public function get($ejercicio)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codejercicio = " . $this->var2str($ejercicio) . ";");
        if ($data) {
            return new \config_130($data[0]);
        } else {
            return false;
        }
    }

    public function exists()
    {
        if (is_null($this->codejercicio)) {
            return false;
        } else {
            return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codejercicio = " . $this->var2str($this->codejercicio) . ";");
        }
    }

    public function test()
    {
        return true;
    }

    public function save()
    {
        if ($this->exists()) {
            $sql = "UPDATE " . $this->table_name . " SET hipoteca = " . $this->var2str($this->hipoteca)
                    . ", descuento100 = " . $this->var2str($this->descuento100)
                    . ", difjust = " . $this->var2str($this->difjust)
                    . "  WHERE codejercicio = " . $this->var2str($this->codejercicio) . ";";

            return $this->db->exec($sql);
        } else {
            $sql = "INSERT INTO " . $this->table_name . " (codejercicio,hipoteca,descuento100,difjust) VALUES ("
                    . $this->var2str($this->codejercicio)
                    . "," . $this->var2str($this->hipoteca)
                    . "," . $this->var2str($this->descuento100)
                    . "," . $this->var2str($this->difjust) . ");";

            if ($this->db->exec($sql)) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function delete()
    {
        return true;
    }
}
