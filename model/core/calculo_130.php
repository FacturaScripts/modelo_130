<?php
/**
 * This file is part of modelo_130
 * Copyright (C) 2014-2019  Carlos Garcia Gomez <neorazorx@gmail.com>
 * Copyright (C) 2017       Pablo Zerón Gea     <pablozg@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\model;

/**
 * Calculo modelo 130.
 *
 * @author Carlos García Gómez  <neorazorx@gmail.com>
 * @author Pablo Zerón Gea      <pablozg@gmail.com>
 */
class calculo_130 extends \fs_model
{

    /**
     * Clave primaria.
     * @var type
     */
    public $idmod130;

    /**
     * ID del asiento generado.
     * @var type
     */
    public $idasiento;
    public $pagado;
    public $contabilizar;
    public $complementaria;
    public $cerrado;
    public $codejercicio;
    public $fechaasiento;
    public $fechafin;
    public $fechainicio;
    public $periodo;
    public $casilla_1;
    public $casilla_2;
    public $casilla_3;
    public $casilla_4;
    public $casilla_5;
    public $casilla_6;
    public $casilla_7;
    public $casilla_8;
    public $casilla_9;
    public $casilla_10;
    public $casilla_11;
    public $casilla_12;
    public $casilla_13;
    public $casilla_14;
    public $casilla_15;
    public $casilla_16;
    public $casilla_17;
    public $casilla_18;
    public $casilla_19;
    public $importe_deduccion_c19;

    public function __construct($r = false)
    {
        parent::__construct('co_mod130');
        if ($r) {
            $this->idmod130 = $this->intval($r['idmod130']);
            $this->idasiento = $this->intval($r['idasiento']);
            $this->pagado = $r['pagado'];
            $this->contabilizar = $r['contabilizar'];
            $this->complementaria = $r['complementaria'];
            $this->cerrado = $r['cerrado'];
            $this->codejercicio = $r['codejercicio'];
            $this->fechaasiento = Date('d-m-Y', strtotime($r['fechaasiento']));
            $this->fechafin = Date('d-m-Y', strtotime($r['fechafin']));
            $this->fechainicio = Date('d-m-Y', strtotime($r['fechainicio']));
            $this->periodo = $r['periodo'];
            $this->casilla_1 = $r['casilla_1'];
            $this->casilla_2 = $r['casilla_2'];
            $this->casilla_3 = $r['casilla_3'];
            $this->casilla_4 = $r['casilla_4'];
            $this->casilla_5 = $r['casilla_5'];
            $this->casilla_6 = $r['casilla_6'];
            $this->casilla_7 = $r['casilla_7'];
            $this->casilla_8 = $r['casilla_8'];
            $this->casilla_9 = $r['casilla_9'];
            $this->casilla_10 = $r['casilla_10'];
            $this->casilla_11 = $r['casilla_11'];
            $this->casilla_12 = $r['casilla_12'];
            $this->casilla_13 = $r['casilla_13'];
            $this->casilla_14 = $r['casilla_14'];
            $this->casilla_15 = $r['casilla_15'];
            $this->casilla_16 = $r['casilla_16'];
            $this->casilla_17 = $r['casilla_17'];
            $this->casilla_18 = $r['casilla_18'];
            $this->casilla_19 = $r['casilla_19'];
            $this->importe_deduccion_c19 = $r['importe_deduccion_c19'];
        } else {
            $this->idmod130 = null;
            $this->idasiento = null;
            $this->pagado = false;
            $this->contabilizar = true;
            $this->complementaria = false;
            $this->cerrado = false;
            $this->codejercicio = null;
            $this->fechaasiento = null;
            $this->fechafin = null;
            $this->fechainicio = null;
            $this->periodo = null;
            $this->casilla_1 = 0.0;
            $this->casilla_2 = 0.0;
            $this->casilla_3 = 0.0;
            $this->casilla_4 = 0.0;
            $this->casilla_5 = 0.0;
            $this->casilla_6 = 0.0;
            $this->casilla_7 = 0.0;
            $this->casilla_8 = 0.0;
            $this->casilla_9 = 0.0;
            $this->casilla_10 = 0.0;
            $this->casilla_11 = 0.0;
            $this->casilla_12 = 0.0;
            $this->casilla_13 = 0.0;
            $this->casilla_14 = 0.0;
            $this->casilla_15 = 0.0;
            $this->casilla_16 = 0.0;
            $this->casilla_17 = 0.0;
            $this->casilla_18 = 0.0;
            $this->casilla_19 = 0.0;
            $this->importe_deduccion_c19 = 0.0;
        }
    }

    protected function install()
    {
        return '';
    }

    public function url()
    {
        if (is_null($this->idmod130)) {
            return 'index.php?page=modelo_130';
        }

        return 'index.php?page=modelo_130&id=' . $this->idmod130;
    }

    public function asiento_url()
    {
        if (is_null($this->idasiento)) {
            return 'index.php?page=contabilidad_asientos';
        }

        return 'index.php?page=contabilidad_asiento&id=' . $this->idasiento;
    }

    public function ejercicio_url()
    {
        if (is_null($this->codejercicio)) {
            return 'index.php?page=contabilidad_ejercicios';
        }

        return 'index.php?page=contabilidad_ejercicio&cod=' . $this->codejercicio;
    }

    public function get_partidas()
    {
        if (isset($this->idasiento)) {
            $partida = new \partida();
            return $partida->all_from_asiento($this->idasiento);
        }

        return false;
    }

    public function get($id)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idmod130 = " . $this->var2str($id) . ";");
        if ($data) {
            return new \calculo_130($data[0]);
        }

        return false;
    }

    public function exists()
    {
        if (is_null($this->idmod130)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name
                . " WHERE idmod130 = " . $this->var2str($this->idmod130) . ";");
    }

    public function test()
    {
        return true;
    }

    public function save()
    {
        if ($this->exists()) {
            $sql = "UPDATE " . $this->table_name . " SET codejercicio = " . $this->var2str($this->codejercicio)
                . ", fechaasiento = " . $this->var2str($this->fechaasiento)
                . ", fechafin = " . $this->var2str($this->fechafin)
                . ", fechainicio = " . $this->var2str($this->fechainicio)
                . ", idasiento = " . $this->var2str($this->idasiento)
                . ", pagado = " . $this->var2str($this->pagado)
                . ", contabilizar = " . $this->var2str($this->contabilizar)
                . ", complementaria = " . $this->var2str($this->complementaria)
                . ", cerrado = " . $this->var2str($this->cerrado)
                . ", periodo = " . $this->var2str($this->periodo)
                . ", casilla_1 = " . $this->var2str($this->casilla_1)
                . ", casilla_2 = " . $this->var2str($this->casilla_2)
                . ", casilla_3 = " . $this->var2str($this->casilla_3)
                . ", casilla_4 = " . $this->var2str($this->casilla_4)
                . ", casilla_5 = " . $this->var2str($this->casilla_5)
                . ", casilla_6 = " . $this->var2str($this->casilla_6)
                . ", casilla_7 = " . $this->var2str($this->casilla_7)
                . ", casilla_8 = " . $this->var2str($this->casilla_8)
                . ", casilla_9 = " . $this->var2str($this->casilla_9)
                . ", casilla_10 = " . $this->var2str($this->casilla_10)
                . ", casilla_11 = " . $this->var2str($this->casilla_11)
                . ", casilla_12 = " . $this->var2str($this->casilla_12)
                . ", casilla_13 = " . $this->var2str($this->casilla_13)
                . ", casilla_14 = " . $this->var2str($this->casilla_14)
                . ", casilla_15 = " . $this->var2str($this->casilla_15)
                . ", casilla_16 = " . $this->var2str($this->casilla_16)
                . ", casilla_17 = " . $this->var2str($this->casilla_17)
                . ", casilla_18 = " . $this->var2str($this->casilla_18)
                . ", casilla_19 = " . $this->var2str($this->casilla_19)
                . ", importe_deduccion_c19 = " . $this->var2str($this->importe_deduccion_c19)
                . "  WHERE idmod130 = " . $this->var2str($this->idmod130) . ";";

            return $this->db->exec($sql);
        }

        $sql = "INSERT INTO " . $this->table_name . " (codejercicio,fechaasiento,fechafin,fechainicio,idasiento,pagado,contabilizar,"
            . "complementaria, cerrado, periodo,casilla_1,casilla_2,casilla_3,casilla_4,casilla_5,casilla_6,casilla_7,casilla_8,casilla_9,"
            . "casilla_10,casilla_11,casilla_12,casilla_13,casilla_14,casilla_15,casilla_16,casilla_17,casilla_18,"
            . "casilla_19,importe_deduccion_c19) VALUES (" . $this->var2str($this->codejercicio)
            . "," . $this->var2str($this->fechaasiento)
            . "," . $this->var2str($this->fechafin)
            . "," . $this->var2str($this->fechainicio)
            . "," . $this->var2str($this->idasiento)
            . "," . $this->var2str($this->pagado)
            . "," . $this->var2str($this->contabilizar)
            . "," . $this->var2str($this->complementaria)
            . "," . $this->var2str($this->cerrado)
            . "," . $this->var2str($this->periodo)
            . "," . $this->var2str($this->casilla_1)
            . "," . $this->var2str($this->casilla_2)
            . "," . $this->var2str($this->casilla_3)
            . "," . $this->var2str($this->casilla_4)
            . "," . $this->var2str($this->casilla_5)
            . "," . $this->var2str($this->casilla_6)
            . "," . $this->var2str($this->casilla_7)
            . "," . $this->var2str($this->casilla_8)
            . "," . $this->var2str($this->casilla_9)
            . "," . $this->var2str($this->casilla_10)
            . "," . $this->var2str($this->casilla_11)
            . "," . $this->var2str($this->casilla_12)
            . "," . $this->var2str($this->casilla_13)
            . "," . $this->var2str($this->casilla_14)
            . "," . $this->var2str($this->casilla_15)
            . "," . $this->var2str($this->casilla_16)
            . "," . $this->var2str($this->casilla_17)
            . "," . $this->var2str($this->casilla_18)
            . "," . $this->var2str($this->casilla_19)
            . "," . $this->var2str($this->importe_deduccion_c19) . ");";

        if ($this->db->exec($sql)) {
            $this->idmod130 = $this->db->lastval();
            return true;
        }

        return false;
    }

    public function update_mod130($campos, $valores, $id)
    {
        $this->idmod130 = $id;

        if ($this->exists()) {
            $encabezado = "UPDATE " . $this->table_name . " SET ";
            $cuerpo = '';
            foreach ($campos as $index => $data) {
                $cuerpo = $cuerpo . $data . " = " . $this->var2str($valores[$index]) . ", ";
            }

            $cuerpo = substr_replace($cuerpo, '', -2, -1);
            $sql = $encabezado . $cuerpo . "  WHERE idmod130 = " . $this->var2str($id) . ";";
            if ($this->db->exec($sql)) {
                return true;
            }
        }

        return false;
    }

    public function last_row($codeejercicio = null)
    {
        if ($codeejercicio) {
            $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codeejercicio = " . $this->var2str($this->codejercicio) . " AND idmod130 = (SELECT MAX(idmod130) FROM " . $this->table_name . ");");
        } else {
            $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idmod130=(SELECT MAX(idmod130) FROM " . $this->table_name . ");");
        }

        if ($data) {
            return $data[0]['idmod130'];
        }
    }

    public function delete()
    {
        if ($this->db->exec("DELETE FROM " . $this->table_name . " WHERE idmod130 = " . $this->var2str($this->idmod130) . ";")) {
            /// si hay un asiento asociado lo eliminamos
            if (isset($this->idasiento)) {
                $asiento = new \asiento();
                $as0 = $asiento->get($this->idasiento);
                if ($as0) {
                    $as0->delete();
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Devuelve todas las regularizaciones.
     * @return \calculo_130
     */
    public function all()
    {
        $reglist = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY idmod130 DESC;");
        if ($data) {
            foreach ($data as $r) {
                $reglist[] = new \calculo_130($r);
            }
        }

        return $reglist;
    }

    /**
     * Devuelve todas los modelos 130 del ejercicio.
     * @param type $codejercicio
     * @return \calculo_130
     */
    public function all_from_ejercicio($codejercicio)
    {
        $reglist = array();
        $sql = "SELECT * FROM " . $this->table_name . " WHERE codejercicio = " . $this->var2str($codejercicio)
            . " ORDER BY fechafin ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $r) {
                $reglist[] = new \calculo_130($r);
            }
        }

        return $reglist;
    }

    public function all_from_ejercicio_periodo($codejercicio, $trimestre)
    {
        $reglist = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE periodo = " . $this->var2str($trimestre) . " AND codejercicio = " . $this->var2str($codejercicio) . " ORDER BY fechafin ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $r) {
                $reglist[] = new \calculo_130($r);
            }
        }

        return $reglist;
    }

    public function all_from_ejercicio_fecha($codejercicio, $fecha)
    {
        $reglist = array();

        $sql = "SELECT * FROM " . $this->table_name . " WHERE fechafin < " . $this->var2str($fecha)
            . " AND codejercicio = " . $this->var2str($codejercicio) . " ORDER BY fechafin ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $r) {
                $reglist[] = new \calculo_130($r);
            }
        }

        return $reglist;
    }

    /**
     * Devuelve las cuentas del ejercicio $codeje cuya cuenta madre
     * está marcada como cuenta especial $id.
     * @param type $id
     * @param type $codeje
     * @return \subcuenta
     */
    public function all_from_cuenta_ejer($id, $codeje)
    {
        $cuentas = array();
        $sql = "SELECT * FROM co_subcuentas WHERE codcuenta = " . $this->var2str($id)
            . " AND codejercicio = " . $this->var2str($codeje) . " ORDER BY codsubcuenta ASC;";

        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $d) {
                $cuentas[] = new \subcuenta($d);
            }
        }

        return $cuentas;
    }
}
