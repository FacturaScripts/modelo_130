<?php

/*
 * This file is part of modelo_130
 * Copyright (C) 2014-2017  Carlos Garcia Gomez  neorazorx@gmail.com
 * Copyright (C) 2017  Pablo Zerón Gea pablozg@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_model('asiento.php');
require_model('ejercicio.php');
require_model('factura_cliente.php');
require_model('factura_proveedor.php');
require_model('partida.php');
require_model('calculo_130.php');
require_model('subcuenta.php');
require_model('cuenta_banco.php');
require_model('cuenta.php');
require_model('config_130.php');

class modelo_130 extends fs_controller
{
    public $allow_delete;
    public $aux_mod130;
    public $factura_cli;
    public $factura_pro;
    public $fecha_desde;
    public $fecha_hasta;
    public $periodo;
    public $complementaria = false;
    public $mod130;
    public $s_mod130;
    public $casilla_1 = 0;
    public $casilla_2 = 0;
    public $casilla_3 = 0;
    public $rendimiento_ant = 0;
    public $casilla_4 = 0;
    public $casilla_5 = 0;
    public $casilla_6 = 0;
    public $casilla_7 = 0;
    public $casilla_8 = 0;
    public $casilla_9 = 0;
    public $casilla_10 = 0;
    public $casilla_11 = 0;
    public $casilla_12 = 0;
    public $casilla_13 = 0;
    public $casilla_14 = 0;
    public $casilla_15 = 0;
    public $casilla_16 = 0;
    public $casilla_17 = 0;
    public $casilla_18 = 0;
    public $casilla_19 = 0;
    public $importe_deduccion_c19 = 0;
    public $gasDifJust = 0;
    public $hipoteca = false;
    public $descuento = false;
    public $difjust = false;
    public $nombreSeparado;
    public $empresa;
    public $cuenta_banco;
    public $subcuenta;
    public $cuenta;
    public $ejercicio;
    public $partida;
    public $configuracion;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Modelo 130', 'informes');
    }

    protected function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);

        $this->mod130 = new calculo_130();
        $this->factura_cli = new factura_cliente();
        $this->factura_pro = new factura_proveedor();
        $this->empresa = new empresa();
        $this->cuenta_banco = new cuenta_banco();
        $this->subcuenta = new subcuenta();
        $this->cuenta = new cuenta();
        $this->ejercicio = new ejercicio();
        $this->partida = new partida();
        $this->configuracion = new config_130();

        //Descomponemos el nombre dentro de la empresa en nombre y apellidos independientes
        $this->nombreSeparado = $this->getNombreSplit($this->empresa->nombre);

        switch (Date('n')) {
            case '1':
                $this->fecha_desde = Date('01-01-Y', strtotime(Date('Y') . ' -1 year'));
                $this->fecha_hasta = Date('31-12-Y', strtotime(Date('Y') . ' -1 year'));
                $this->periodo = 'T4';
                break;

            case '2':
            case '3':
            case '4':
                $this->fecha_desde = Date('01-01-Y');
                $this->fecha_hasta = Date('31-03-Y');
                $this->periodo = 'T1';
                break;

            case '5':
            case '6':
            case '7':
                $this->fecha_desde = Date('01-01-Y');
                $this->fecha_hasta = Date('30-06-Y');
                $this->periodo = 'T2';
                break;

            case '8':
            case '9':
            case '10':
                $this->fecha_desde = Date('01-01-Y');
                $this->fecha_hasta = Date('30-09-Y');
                $this->periodo = 'T3';
                break;

            case '11':
            case '12':
                $this->fecha_desde = Date('01-01-Y');
                $this->fecha_hasta = Date('31-12-Y');
                $this->periodo = 'T4';
                break;
        }

        if (filter_input(INPUT_POST, 'desde')) {
            $this->fecha_desde = filter_input(INPUT_POST, 'desde');
        }

        if (filter_input(INPUT_POST, 'hasta')) {
            $this->fecha_hasta = filter_input(INPUT_POST, 'hasta');
        }

        // Cargarmos la configuracion para el ejercicio actual si existe
        $eje0 = $this->ejercicio->get_by_fecha($this->fecha_desde, true);
        $config = $this->configuracion->get($eje0->codejercicio);
        if ($config) {
            $this->hipoteca = $config->hipoteca;
            $this->difjust = $config->difjust;
            $this->descuento = $config->descuento100;
        }


        $this->s_mod130 = false;
        if (isset($_REQUEST['id'])) { // Si recibe el id se comprueba que hacer con él
            if (filter_input(INPUT_GET, 'pagado')) { // Si incluye el pagado se llama a la funcion del asiento de pago
                $this->guardar_asiento_130(filter_input(INPUT_GET, 'pagado'), filter_input(INPUT_GET, 'id'));
            } elseif (filter_input(INPUT_GET, 'complementaria')) { // si es una complementaria se carga los datos del id
                $this->s_mod130 = $this->mod130->get($_REQUEST['id']);
                $this->complementaria = true;
                $this->guardar_130();
            } else { // si solo es el id se muestra los datos del trimestre
                $this->s_mod130 = $this->mod130->get($_REQUEST['id']);
                if ($this->s_mod130) {
                    $this->page->title = 'Modelo 130 ' . $this->s_mod130->periodo . '@' . $this->s_mod130->codejercicio;
                }
            }
        } elseif (filter_input(INPUT_POST, 'proceso')) { // si no existe id pero si proceso
            if ($this->factura_cli->huecos()) {
                $this->template = false;
                echo '<div class="alert alert-danger">'
                . 'Tienes <a href="index.php?page=ventas_facturas">huecos en la facturación</a>'
                . ' y por tanto no puedes calcular el modelo 130.'
                . '</div>';
            } elseif ($this->facturas_sin_asiento()) {
                $this->template = false;
                echo '<div class="alert alert-danger">'
                . 'Tienes facturas sin asientos contables y por tanto no puedes calcular el modelo 130. '
                . 'Puedes generar los asientos usando el <b>plugin megafacturador</b>.'
                . '</div>';
            } elseif (filter_input(INPUT_POST, 'proceso') == 'guardar') {
                $this->guardar_130(); // Guarda los datos en un nuevo trimestre
            } else {
                $this->completar_130(); // Muestra la previsualización de los datos
            }
        } else {
            /// Si se pulsa guardar en la pantalla de configuración se leen los checkbox y se asigna el valor correcto
            if (filter_input(INPUT_GET, 'saveconfig')) {
                $eje0 = $this->ejercicio->get_by_fecha($this->fecha_desde, true);
                if ($eje0) {
                    $this->configuracion->codejercicio = $eje0->codejercicio;
                    if (filter_input(INPUT_POST, 'hipoteca')) {
                        $this->configuracion->hipoteca = true;
                    } else {
                        $this->configuracion->hipoteca = false;
                    }
                    if (filter_input(INPUT_POST, 'descuento')) {
                        $this->configuracion->descuento100 = true;
                    } else {
                        $this->configuracion->descuento100 = false;
                    }
                    if (filter_input(INPUT_POST, 'difjust')) {
                        $this->configuracion->difjust = true;
                    } else {
                        $this->configuracion->difjust = false;
                    }

                    if ($this->configuracion->save()) {
                        $this->new_message('<a href="' . $this->configuracion->url() . '">Configuración guardada correctamente.');
                        header('Location: ' . $this->configuracion->url());
                    } else {
                        $this->new_error_msg('Imposible guardar configuración.');
                    }
                } else {
                    $this->template = false;
                    echo '<div class="alert alert-danger">El ejercicio está cerrado.</div>';
                }
            }
            if (filter_input(INPUT_GET, 'delete')) {
                $continuar = true;
                $mod1300 = $this->mod130->get(filter_input(INPUT_GET, 'delete'));

                if (!$mod1300) {
                    $this->new_error_msg('Modelo 130 no encontrado.');
                    $continuar = false;
                }

                if ($continuar) {

                    // Si el trimestre que queremos borrar coincide con el último creado, se borra
                    if ($this->compruebaPeriodo($mod1300->codejercicio, $mod1300->periodo) == 2 and ! $mod1300->cerrado) {
                        if ($mod1300->delete()) {
                            // Si la borrada era una complementaria, contabilizamos la original
                            if ($mod1300->complementaria) {
                                $campos = array("contabilizar", "cerrado");
                                $valores = array('1', '0');
                                $this->mod130->update_mod130($campos, $valores, $this->mod130->last_row());
                            } else {
                                $campos = array("cerrado");
                                $valores = array('0');
                                $this->mod130->update_mod130($campos, $valores, $this->mod130->last_row());
                            }
                            $this->new_message('Modelo 130 eliminado correctamente.');
                        } else {
                            $this->new_error_msg('Imposible eliminar el modelo 130.');
                        }
                    } else {
                        $this->new_error_msg('No se puede eliminar este periodo, debe eliminar primero los periodos posteriores.');
                    }
                }
            }
        }
    }

    /// Funciones copiadas de modelo 303 de Carlos Garcia Gomez  neorazorx@gmail.com
    private function facturas_sin_asiento()
    {
        $hay = false;

        /// facturas de compra
        $sql = "SELECT COUNT(*) as num FROM facturasprov WHERE idasiento IS NULL"
                . " AND fecha >= " . $this->empresa->var2str($this->fecha_desde)
                . " AND fecha <= " . $this->empresa->var2str($this->fecha_hasta) . ";";
        $data = $this->db->select($sql);
        if ($data) {
            if (intval($data[0]['num']) > 0) {
                $hay = true;
            }
        }

        /// facturas de venta
        $sql = "SELECT COUNT(*) as num FROM facturascli WHERE idasiento IS NULL"
                . " AND fecha >= " . $this->empresa->var2str($this->fecha_desde)
                . " AND fecha <= " . $this->empresa->var2str($this->fecha_hasta) . ";";
        $data = $this->db->select($sql);
        if ($data) {
            if (intval($data[0]['num']) > 0) {
                $hay = true;
            }
        }

        return $hay;
    }

    private function completar_130()
    {
        $this->template = 'ajax/modelo_130';

        $this->aux_mod130 = array();

        $this->casilla_1 = 0;
        $this->casilla_2 = 0;

        $eje0 = $this->ejercicio->get_by_fecha($this->fecha_desde, true);
        if ($eje0) {
            $continuar = true;
            $saldo = 0;

            $this->calcula_resumen();

            if ($continuar) {
            } else {
                $this->template = false;
                echo '<div class="alert alert-danger">Error al leer las subcuentas.</div>';
            }
        } else {
            $this->template = false;
            echo '<div class="alert alert-danger">El ejercicio está cerrado.</div>';
        }
    }

    private function guardar_130()
    {
        $continuar = true;

        $eje0 = $this->ejercicio->get_by_fecha($this->fecha_desde, true); // Esta abierto el ejercicio?
        if ($eje0) {

            /// Comprobamos si es un nuevo trimestre o una complementaria
            if (!$this->complementaria) {  // Si complementaria es false, se crea un nuevo trimestre
                // Si ya esta creado el trimestre no se continua
                if ($this->mod130->all_from_ejercicio_periodo($eje0->codejercicio, filter_input(INPUT_POST, 'periodo'))) {
                    $this->new_error_msg('El modelo 130 correspondiente al periodo ' . filter_input(INPUT_POST, 'periodo') .
                            ' ya ha sido creado, si deseea generar una declaración complementaria, entre dentro de la declaración y pulse el botón "Generar Complementaria".');
                    $continuar = false;
                } else {
                    // Si el trimestre que queremos crear es posterior al último creado continuamos
                    if (!$this->compruebaPeriodo($eje0->codejercicio, filter_input(INPUT_POST, 'periodo'))) {
                        $this->new_error_msg('No es posible crear un trimestre anterior a uno ya creado, borre los trimestres posteriores y vuelva intentarlo.');
                        $continuar = false;
                    } else {
                        $campos = array("cerrado");
                        $valores = array('1');
                        $this->mod130->update_mod130($campos, $valores, $this->mod130->last_row());
                    }
                }
            } else {
                if ($this->s_mod130->cerrado) {
                    $this->new_error_msg('Este trimestre se encuentra cerrado, si deseea generar una declaración complementaria de este periodo'
                            . ', borre todos los trimestres posteriores.');
                    $continuar = false;
                } else {
                    $this->fecha_hasta = $this->s_mod130->fechafin;
                    $this->fecha_desde = $this->s_mod130->fechainicio;
                    // Se pone como cerrado el asiento origen de la complementaria
                    $campos = array("cerrado", "contabilizar");
                    $valores = array('1', '0');
                    $this->mod130->update_mod130($campos, $valores, $this->s_mod130->idmod130);
                }
            }

            if ($continuar) {
                $this->mod130 = new calculo_130();
                $this->mod130->codejercicio = $eje0->codejercicio;
                if ($this->complementaria) {
                    $this->mod130->fechafin = $this->s_mod130->fechafin;
                    $this->mod130->fechainicio = $this->s_mod130->fechainicio;
                    $this->mod130->periodo = $this->s_mod130->periodo . 'C';
                    $this->mod130->complementaria = true;
                } else {
                    $this->mod130->fechafin = $this->fecha_hasta;
                    $this->mod130->fechainicio = $this->fecha_desde;
                    $this->mod130->periodo = filter_input(INPUT_POST, 'periodo');
                }

                if (filter_input(INPUT_POST, 'proceso') == 'guardar' || $this->complementaria) {
                    $this->calcula_casillas();
                }

                $this->mod130->casilla_1 = $this->casilla_1;
                $this->mod130->casilla_2 = $this->casilla_2;
                $this->mod130->casilla_3 = $this->casilla_3;
                $this->mod130->casilla_4 = $this->casilla_4;
                $this->mod130->casilla_5 = $this->casilla_5;
                $this->mod130->casilla_6 = $this->casilla_6;
                $this->mod130->casilla_7 = $this->casilla_7;
                $this->mod130->casilla_8 = $this->casilla_8;
                $this->mod130->casilla_9 = $this->casilla_9;
                $this->mod130->casilla_10 = $this->casilla_10;
                $this->mod130->casilla_11 = $this->casilla_11;
                $this->mod130->casilla_12 = $this->casilla_12;
                $this->mod130->casilla_13 = $this->casilla_13;
                $this->mod130->casilla_14 = $this->casilla_14;
                $this->mod130->casilla_15 = $this->casilla_15;
                $this->mod130->casilla_16 = $this->casilla_16;
                $this->mod130->casilla_17 = $this->casilla_17;
                $this->mod130->casilla_18 = $this->casilla_18;
                $this->mod130->casilla_19 = $this->casilla_19;
                $this->mod130->importe_deduccion_c19 = $this->importe_deduccion_c19;

                if ($this->mod130->save()) {
                    $this->new_message('<a href="' . $this->mod130->url() . '">Modelo 130</a> guardado correctamente.');
                    header('Location: ' . $this->mod130->url());
                } else {
                    $this->new_error_msg('Error al guardar el modelo 130.');
                }
            }
        } else {
            $this->new_error_msg('El ejercicio está cerrado.');
        }
    }

    private function guardar_asiento_130($pagado, $id)
    {
        $asiento = new asiento();

        $this->s_mod130 = $this->mod130->get($id);

        $eje0 = $this->ejercicio->get_by_fecha($this->fecha_desde, true);
        if ($eje0) {
            $continuar = true;

            if (is_null($this->s_mod130->idasiento)) {
                if ($this->s_mod130->casilla_19 <= 0) {
                    $this->new_error_msg('Imposible crear asiento contable de pago con resultado negativo o cero.');
                    $continuar = false;
                }

                if ($pagado and $continuar) {

                    /// Si se elige pago por banco se lee el número de cuenta, si no se usa el de caja por defecto
                    $codcaja = '5700000000';
                    if (filter_input(INPUT_POST, 'PagoAsiento130')) {
                        if (filter_input(INPUT_POST, 'PagoAsiento130') != '') {
                            $codcaja = filter_input(INPUT_POST, 'PagoAsiento130');
                        }
                    }

                    $subc = $this->cuenta_banco->get($codcaja);
                    if ($subc) {
                        if (isset($subc->codsubcuenta)) {
                            $codcaja = $subc->codsubcuenta;
                        } else {
                            $codcaja = '5720000000';
                        }

                        $this->crea_subcuentas($eje0->codejercicio, '572', $codcaja, 'Bancos e instituciones de crédito c/c vista, euros - ' . $subc->descripcion);
                    }

                    /// guardamos el asiento
                    $asiento->codejercicio = $this->s_mod130->codejercicio;
                    $asiento->concepto = 'Pago modelo 130 - ' . $this->s_mod130->periodo;
                    $asiento->fecha = filter_input(INPUT_POST, 'mod130_pagado');
                    $asiento->editable = false;
                    $asiento->importe = $this->s_mod130->casilla_19;

                    if ($asiento->save()) {
                        $subc = $this->subcuenta->get_by_codigo('4730000000', $this->s_mod130->codejercicio);
                        if ($subc) {
                            $this->crea_partida($asiento->idasiento, 'EUR', 1, $asiento->concepto, $subc->idsubcuenta, $subc->codsubcuenta, $asiento->importe, 'D');
                        } else {
                            $this->new_error_msg('Subcuenta 4730000000 no encontrada.');
                            $continuar = false;
                        }

                        $subc = $this->subcuenta->get_by_codigo($codcaja, $this->s_mod130->codejercicio);
                        if ($subc) {
                            $this->crea_partida($asiento->idasiento, 'EUR', 1, $asiento->concepto, $subc->idsubcuenta, $subc->codsubcuenta, $asiento->importe, 'H');
                        } else {
                            $this->new_error_msg('Subcuenta ' . $codcaja . ' no encontrada.');
                            $continuar = false;
                        }
                        $this->new_message('Asiento guardado correctamente.');
                    } else {
                        $this->new_error_msg('Imposible guardar el asiento.');
                        $continuar = false;
                    }

                    if ($continuar) {
                        $campos = array("codejercicio", "fechaasiento", "idasiento", "pagado");
                        $valores = array($this->s_mod130->codejercicio, $asiento->fecha, $asiento->idasiento, '1');

                        if ($this->mod130->update_mod130($campos, $valores, $this->s_mod130->idmod130)) {
                            $this->new_message('<a href="' . $this->mod130->url() . '">Modelo 130 actualizado correctamente.');
                            header('Location: ' . $this->mod130->url());
                        } else {
                            $this->new_error_msg('Imposible actualizar modelo 130 como pagado.');
                            $continuar = false;
                        }
                    }
                }
            } else {

                /// marcar como impagada
                $campos = array("codejercicio", "fechaasiento", "idasiento", "pagado");
                $valores = array($this->s_mod130->codejercicio, null, null, '0');

                /// ¿Eliminamos el asiento de pago?
                $as1 = new asiento();
                $asiento = $as1->get($this->s_mod130->idasiento);
                if ($asiento) {
                    $asiento->delete();
                    $this->new_message('Asiento de pago eliminado.');
                }

                if ($this->mod130->update_mod130($campos, $valores, $this->s_mod130->idmod130)) {
                    $this->new_message('<a href="' . $this->mod130->url() . '">Modelo 130 marcado como impagado.');
                    header('Location: ' . $this->mod130->url());
                } else {
                    $this->new_error_msg('Error al modificar el modelo 130.');
                }
            }
        } else {
            $this->new_error_msg('El ejercicio está cerrado.');
        }
    }

    private function calcula_casillas()
    {
        $this->casilla_3 = 0;
        $this->casilla_4 = 0;
        $this->casilla_5 = 0;
        $this->casilla_6 = 0;
        $this->casilla_7 = 0;
        $this->casilla_8 = 0;
        $this->casilla_9 = 0;
        $this->casilla_10 = 0;
        $this->casilla_11 = 0;
        $this->casilla_12 = 0;
        $this->casilla_13 = 0;
        $this->casilla_14 = 0;
        $this->casilla_15 = 0;
        $this->casilla_16 = 0;
        $this->casilla_17 = 0;
        $this->casilla_18 = 0;
        $this->casilla_19 = 0;

        $casilla_7_sum_pos = 0;
        $casilla_16_sum = 0;
        $casilla_19_sum = 0;
        $casilla_19_deduccion = 0;
        $casRenAnt_1 = 0;
        $casRenAnt_2 = 0;
        $totalPagos = 0;

        $eje0 = $this->ejercicio->get_by_fecha($this->fecha_desde, true);

        $this->calcula_resumen();

        if ($this->difjust) {
            // se calcula el 5% por gastos de dificil justificación

            if (round(($this->casilla_1 - $this->casilla_2), FS_NF0) > 0) {
                $this->gasDifJust = round(($this->casilla_1 - $this->casilla_2) * 0.05, FS_NF0);

                if ($this->gasDifJust > 2000) {
                    $this->gasDifJust = 2000;
                }
            }

            $this->casilla_2 += $this->gasDifJust;
        }

        // Obtenemos el valor de la casilla 3 y 4
        $this->casilla_3 = round(($this->casilla_1 - $this->casilla_2), FS_NF0);
        if ($this->casilla_3 > 0) {
            $this->casilla_4 = round(($this->casilla_3 * 0.20), FS_NF0);
        }

        /// obtenemos el valor de la casilla 5
        foreach ($this->mod130->all_from_ejercicio_fecha($eje0->codejercicio, $this->fecha_hasta) as $datos) {
            /// Si esta marcada como a contabilizar se leen los datos, en caso contrario se obvia.
            if ($datos->contabilizar) {
                if ($datos->casilla_7 >= 0) {
                    $casilla_7_sum_pos += (float) $datos->casilla_7;
                }

                $casilla_16_sum += (float) $datos->casilla_16;
            }
            /// Si existe asiento sumamos el importe de la casilla 19
            if ($datos->idasiento) {
                $totalPagos += (float) $datos->casilla_19;
            }
        }

        $this->casilla_5 = round($casilla_7_sum_pos - $casilla_16_sum, FS_NF0);

        /// Obtenemos el valor de la casilla 6 (Rentenciones)

        $cuentas_retenciones = array("473");

        foreach ($cuentas_retenciones as $rentenciones) {
            foreach ($this->mod130->all_from_cuenta_ejer($rentenciones, $eje0->codejercicio) as $scta_cuenta) {
                $tot_cuenta = $this->partida->totales_from_subcuenta_fechas($scta_cuenta->idsubcuenta, $this->fecha_desde, $this->fecha_hasta);
                if ($tot_cuenta['saldo']) {
                    $this->casilla_6 += (($tot_cuenta['debe'] - $tot_cuenta['haber']));
                }
            }
        }

        /// Leemos las cuentas marcadas como retenciones en su cuenta especial
        foreach ($this->subcuenta->all_from_cuentaesp('M130R', $eje0->codejercicio) as $scta_cuenta) {
            if (!in_array($scta_cuenta->codcuenta, $cuentas_retenciones)) {
                $tot_cuenta = $this->partida->totales_from_subcuenta_fechas($scta_cuenta->idsubcuenta, $this->fecha_desde, $this->fecha_hasta);
                if ($tot_cuenta['saldo']) {
                    $this->casilla_6 += ($tot_cuenta['debe'] - $tot_cuenta['haber']);
                }
            }
        }
        
        /// Ya que las retenciones y los pagos del modelo comparten la cuenta 473 se descuenta del total los pagos efectuados por el modelo para
        /// dejar unicamente el valor de las retenciones.
        
        $this->casilla_6 -= $totalPagos;

        /// Obtenemos el valor de la casilla 7

        $this->casilla_7 = round($this->casilla_4 - $this->casilla_5 - $this->casilla_6, FS_NF0);

        /// Obtenemos el valor de la casilla 12

        $this->casilla_12 = round($this->casilla_7 + $this->casilla_11, FS_NF0);
        if ($this->casilla_12 < 0) {
            $this->casilla_12 = 0;
        }

        /// Casilla 13
        if ($this->descuento) {
            /// Se leen los datos del año pasado en caso de existir
            foreach ($this->mod130->all_from_ejercicio($eje0->codejercicio - 1) as $datos) {
                if ($datos->contabilizar) {
                    $casRenAnt_1 += (float) $datos->casilla_1;
                    $casRenAnt_2 += (float) $datos->casilla_2;
                }
            }

            $this->rendimiento_ant = $casRenAnt_1 - $casRenAnt_2;

            if ($this->rendimiento_ant <= 12000 and $this->rendimiento_ant >= 0) {
                if ($this->rendimiento_ant <= 9000) {
                    $this->casilla_13 = 100;
                }
                if ($this->rendimiento_ant > 9000 and $this->rendimiento_ant <= 10000) {
                    $this->casilla_13 = 75;
                }
                if ($this->rendimiento_ant > 10000 and $this->rendimiento_ant <= 11000) {
                    $this->casilla_13 = 50;
                }
                if ($this->rendimiento_ant > 11000 and $this->rendimiento_ant <= 12000) {
                    $this->casilla_13 = 25;
                }
            }
        }

        /// Obtenemos el valor de la casilla 14

        $this->casilla_14 = round($this->casilla_12 - $this->casilla_13, FS_NF0);

        /// Obtenemos el valor de la casilla 15

        if ($this->casilla_14 > 0) {
            foreach ($this->mod130->all_from_ejercicio_fecha($eje0->codejercicio, $this->fecha_hasta) as $datos) {
                if ($datos->contabilizar) {
                    if ($datos->casilla_19 < 0) {
                        $casilla_19_sum += (float) $datos->casilla_19;
                    }

                    $casilla_19_deduccion += (float) ($datos->importe_deduccion_c19);
                }
            }

            if ($casilla_19_sum != $casilla_19_deduccion) {
                if (($casilla_19_sum * -1) > $this->casilla_14) {
                    $this->casilla_15 = round(($this->casilla_14), FS_NF0);
                }
                if (($casilla_19_sum * -1) <= $this->casilla_14) {
                    $this->casilla_15 = round($casilla_19_sum * -1, FS_NF0) - $casilla_19_deduccion;
                }
                $this->importe_deduccion_c19 = $this->casilla_15;
            }
        }

        /// Obtenemos el valor de la casilla 16
        // TODO: difetenciar entre apartado I y II y además establecer variable para cambiar el limite

        if ($this->hipoteca == true and $this->casilla_14 > 0) {
            $this->casilla_16 = round($this->casilla_3 * 0.02, FS_NF0);
            if ($this->casilla_16 > 660.14) {
                $this->casilla_16 = 660.14;
            }
            if (($this->casilla_14 - $this->casilla_15) > $this->casilla_16) {
                $this->casilla_16 = round(($this->casilla_14 - $this->casilla_15), FS_NF0);
            }
        }

        /// Obtenemos el valor de la casilla 17

        $this->casilla_17 = round($this->casilla_14 - $this->casilla_15 - $this->casilla_16, FS_NF0);

        /// Obtenemos el valor de la casilla 18

        if ($this->s_mod130) {
            $tperiodo = $this->s_mod130->periodo;

            $modAnt = $this->db->select("SELECT * FROM co_mod130 WHERE codejercicio = " . $this->s_mod130->codejercicio . " AND periodo = '" . $tperiodo . "';");

            $this->casilla_18 = (float) $modAnt[0]['casilla_19'];
        }


        /// Obtenemos el valor de la casilla 19

        $this->casilla_19 = round($this->casilla_17 - $this->casilla_18, FS_NF0);
    }

    private function calcula_resumen()
    {
        $this->aux_mod130 = array();

        $cuentas_gastos = array("600", "601", "602", "607", "621", "622", "623", "624", "625", "626", "627", "628", "629", "631", "640", "641", "642", "643", "644", "649", "669", "678", "680", "681");
        $cuentas_ingresos = array("700", "701", "702", "703", "704", "705", "752", "753", "754", "755", "759", "771", "778");
        $cuentas_rappels_ventas = array("7090", "7091", "7092", "7093", "7094");
        $cuentas_rappels_compras = array("6090", "6091", "6092");


        $this->casilla_1 = 0;
        $this->casilla_2 = 0;

        $eje0 = $this->ejercicio->get_by_fecha($this->fecha_desde, true);

        /// INGRESOS
        /// obtenemos las ventas
        foreach ($cuentas_ingresos as $ingresos) {
            foreach ($this->mod130->all_from_cuenta_ejer($ingresos, $eje0->codejercicio) as $scta_cuenta) {
                $tot_cuenta = $this->partida->totales_from_subcuenta_fechas($scta_cuenta->idsubcuenta, $this->fecha_desde, $this->fecha_hasta);
                if ($tot_cuenta['saldo']) {
                    $this->aux_mod130[] = array(
                        'subcuenta' => $scta_cuenta,
                        'debe' => $tot_cuenta['debe'],
                        'haber' => $tot_cuenta['haber']
                    );

                    $this->casilla_1 += ($tot_cuenta['haber'] - $tot_cuenta['debe']);
                }
            }
        }

        /// Descontamos los rappels de ventas
        foreach ($cuentas_rappels_ventas as $rappel_venta) {
            foreach ($this->mod130->all_from_cuenta_ejer($rappel_venta, $eje0->codejercicio) as $scta_cuenta) {
                $tot_cuenta = $this->partida->totales_from_subcuenta_fechas($scta_cuenta->idsubcuenta, $this->fecha_desde, $this->fecha_hasta);
                if ($tot_cuenta['saldo']) {
                    $this->aux_mod130[] = array(
                        'subcuenta' => $scta_cuenta,
                        'debe' => $tot_cuenta['debe'],
                        'haber' => $tot_cuenta['haber']
                    );

                    $this->casilla_1 -= ($tot_cuenta['haber'] - $tot_cuenta['debe']);
                }
            }
        }

        /// Leemos las cuentas marcadas como ingresos en su cuenta especial
        foreach ($this->subcuenta->all_from_cuentaesp('M130I', $eje0->codejercicio) as $scta_cuenta) {
            if (!in_array($scta_cuenta->codcuenta, $cuentas_ingresos)) {
                $tot_cuenta = $this->partida->totales_from_subcuenta_fechas($scta_cuenta->idsubcuenta, $this->fecha_desde, $this->fecha_hasta);
                if ($tot_cuenta['saldo']) {
                    $this->aux_mod130[] = array(
                        'subcuenta' => $scta_cuenta,
                        'debe' => $tot_cuenta['debe'],
                        'haber' => $tot_cuenta['haber']
                    );

                    $this->casilla_1 += ($tot_cuenta['haber'] - $tot_cuenta['debe']);
                }
            }
        }

        /// GASTOS
        /// obtenemos las compras y gastos

        foreach ($cuentas_gastos as $gastos) {
            foreach ($this->mod130->all_from_cuenta_ejer($gastos, $eje0->codejercicio) as $scta_cuenta) {
                $tot_cuenta = $this->partida->totales_from_subcuenta_fechas($scta_cuenta->idsubcuenta, $this->fecha_desde, $this->fecha_hasta);
                if ($tot_cuenta['saldo']) {
                    $this->aux_mod130[] = array(
                        'subcuenta' => $scta_cuenta,
                        'debe' => $tot_cuenta['debe'],
                        'haber' => $tot_cuenta['haber']
                    );

                    $this->casilla_2 += ($tot_cuenta['debe'] - $tot_cuenta['haber']);
                }
            }
        }

        /// Leemos las cuentas marcadas como gastos en su cuenta especial
        foreach ($this->subcuenta->all_from_cuentaesp('M130G', $eje0->codejercicio) as $scta_cuenta) {
            if (!in_array($scta_cuenta->codcuenta, $cuentas_gastos)) {
                $tot_cuenta = $this->partida->totales_from_subcuenta_fechas($scta_cuenta->idsubcuenta, $this->fecha_desde, $this->fecha_hasta);
                if ($tot_cuenta['saldo']) {
                    $this->aux_mod130[] = array(
                        'subcuenta' => $scta_cuenta,
                        'debe' => $tot_cuenta['debe'],
                        'haber' => $tot_cuenta['haber']
                    );

                    $this->casilla_2 += ($tot_cuenta['debe'] - $tot_cuenta['haber']);
                }
            }
        }

        /// Descontamos los rappels de compras
        foreach ($cuentas_rappels_compras as $rappel_compra) {
            foreach ($this->mod130->all_from_cuenta_ejer($rappel_compra, $eje0->codejercicio) as $scta_cuenta) {
                $tot_cuenta = $this->partida->totales_from_subcuenta_fechas($scta_cuenta->idsubcuenta, $this->fecha_desde, $this->fecha_hasta);
                if ($tot_cuenta['saldo']) {
                    $this->aux_mod130[] = array(
                        'subcuenta' => $scta_cuenta,
                        'debe' => $tot_cuenta['debe'],
                        'haber' => $tot_cuenta['haber']
                    );

                    $this->casilla_2 -= ($tot_cuenta['haber'] - $tot_cuenta['debe']);
                }
            }
        }
    }

    /// Función para crear subcuentas
    private function crea_subcuentas($ejercicio, $cuenta, $codigo_cuenta, $descripcion)
    {
        $subc0 = $this->subcuenta->get_by_codigo((string) $codigo_cuenta, $ejercicio);
        if (!$subc0) {
            $datoscuenta = $this->cuenta->get_by_codigo($cuenta, $ejercicio);

            $subc0 = new subcuenta();

            $subc0->codcuenta = $cuenta;
            $subc0->codejercicio = $ejercicio;
            $subc0->codsubcuenta = $codigo_cuenta;
            $subc0->descripcion = $descripcion;
            $subc0->idcuenta = $datoscuenta->idcuenta;

            if (!$subc0->save()) {
                $this->new_error_msg('Error al crear la subcuenta ' . $codigo_cuenta);
            } else {
                $this->new_message('Creada subcuenta ' . $codigo_cuenta . ' - ' . $descripcion . ' correctamente');
            }
        }
    }

    /// Función para crear partidas
    private function crea_partida($idasiento, $coddivisa, $tasaconv, $concepto, $idsubcuenta, $codsubcuenta, $importe, $tipo)
    {
        $partida = new partida();
        $partida->idasiento = $idasiento;
        $partida->coddivisa = $coddivisa;
        $partida->tasaconv = $tasaconv;
        $partida->concepto = $concepto;
        $partida->idsubcuenta = $idsubcuenta;
        $partida->codsubcuenta = $codsubcuenta;
        if ($tipo == 'D') {
            $partida->debe = $importe;
        } else {
            $partida->haber = $importe;
        }

        $partida->save();
    }

    /// Función copiada de foro, falta intentar simplificarla

    private function getNombreSplit($nombreCompleto, $apellido_primero = false)
    {
        $nombreCompleto = $this->stripAccents($nombreCompleto);
        $chunks = ($apellido_primero) ? explode(" ", mb_strtoupper($nombreCompleto)) : array_reverse(explode(" ", mb_strtoupper($nombreCompleto)));

        $exceptions = ["DA", "DE", "LA", "DEL", "LOS", "LAS", "SAN", "SANTA"];
        $existen = array_intersect($chunks, $exceptions);
        $nombre = array("Materno" => "", "Paterno" => "", "Nombres" => "");
        $agregar_en = ($apellido_primero) ? "paterno" : "materno";
        $primera_vez = true;

        if ($apellido_primero) {
            if (!empty($existen)) {
                foreach ($chunks as $chunk) {
                    if ($primera_vez) {
                        $nombre["Paterno"] = $nombre["Paterno"] . " " . $chunk;
                        $primera_vez = false;
                    } else {
                        if (in_array($chunk, $exceptions)) {
                            if ($agregar_en == "paterno") {
                                $nombre["Paterno"] = $nombre["Paterno"] . " " . $chunk;
                            } elseif ($agregar_en == "materno") {
                                $nombre["Materno"] = $nombre["Materno"] . " " . $chunk;
                            } else {
                                $nombre["Nombres"] = $nombre["Nombres"] . " " . $chunk;
                            }
                        } else {
                            if ($agregar_en == "paterno") {
                                $nombre["Paterno"] = $nombre["Paterno"] . " " . $chunk;
                                $agregar_en = "materno";
                            } elseif ($agregar_en == "materno") {
                                $nombre["Materno"] = $nombre["Materno"] . " " . $chunk;
                                $agregar_en = "nombres";
                            } else {
                                $nombre["Nombres"] = $nombre["Nombres"] . " " . $chunk;
                            }
                        }
                    }
                }
            } else {
                foreach ($chunks as $chunk) {
                    if ($primera_vez) {
                        $nombre["Paterno"] = $nombre["Paterno"] . " " . $chunk;
                        $primera_vez = false;
                    } else {
                        if (in_array($chunk, $exceptions)) {
                            if ($agregar_en == "paterno") {
                                $nombre["Paterno"] = $nombre["Paterno"] . " " . $chunk;
                            } elseif ($agregar_en == "materno") {
                                $nombre["Materno"] = $nombre["Materno"] . " " . $chunk;
                            } else {
                                $nombre["Nombres"] = $nombre["Nombres"] . " " . $chunk;
                            }
                        } else {
                            if ($agregar_en == "paterno") {
                                $nombre["Materno"] = $nombre["Materno"] . " " . $chunk;
                                $agregar_en = "materno";
                            } elseif ($agregar_en == "materno") {
                                $nombre["Nombres"] = $nombre["Nombres"] . " " . $chunk;
                                $agregar_en = "nombres";
                            } else {
                                $nombre["Nombres"] = $nombre["Nombres"] . " " . $chunk;
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($chunks as $chunk) {
                if ($primera_vez) {
                    $nombre["Materno"] = $chunk . " " . $nombre["Materno"];
                    $primera_vez = false;
                } else {
                    if (in_array($chunk, $exceptions)) {
                        if ($agregar_en == "materno") {
                            $nombre["Materno"] = $chunk . " " . $nombre["Materno"];
                        } elseif ($agregar_en == "paterno") {
                            $nombre["Paterno"] = $chunk . " " . $nombre["Paterno"];
                        } else {
                            $nombre["Nombres"] = $chunk . " " . $nombre["Nombres"];
                        }
                    } else {
                        if ($agregar_en == "materno") {
                            $agregar_en = "paterno";
                            $nombre["Paterno"] = $chunk . " " . $nombre["Paterno"];
                        } elseif ($agregar_en == "paterno") {
                            $agregar_en = "nombres";
                            $nombre["Nombres"] = $chunk . " " . $nombre["Nombres"];
                        } else {
                            $nombre["Nombres"] = $chunk . " " . $nombre["Nombres"];
                        }
                    }
                }
            }
        }
        // LIMPIEZA DE ESPACIOS
        $nombre["Materno"] = trim($nombre["Materno"]);
        $nombre["Paterno"] = trim($nombre["Paterno"]);
        $nombre["Nombres"] = trim($nombre["Nombres"]);
        return $nombre;
    }

    /// Función para reemplazar caracteres no validos para el envio telematico en el nombre
    private function stripAccents($cadena)
    {

        //Ahora reemplazamos las letras
        $cadena = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $cadena);
        $cadena = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $cadena);
        $cadena = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $cadena);
        $cadena = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $cadena);
        $cadena = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $cadena);
        $cadena = str_replace(array('ç', 'Ç'), array('c', 'C'), $cadena);

        return $cadena;
    }

    /// Comprueba el periodo que se pasa como parametro con el último en la base de datos
    /// Devuelve true si es mayor que el último grabado, 2 si son iguales y false si es menor
    private function compruebaPeriodo($ejercicio, $periodo)
    {
        $act_per = intval(str_replace(array('T', 'C'), array('', ''), $periodo));
        $ult_per = 0;

        $data = $this->db->select("SELECT * FROM co_mod130 WHERE idmod130=(SELECT MAX(idmod130) FROM co_mod130) AND codejercicio = " . $ejercicio . ";");
        if ($data) {
            $cadena = str_replace(array('T', 'C'), array('', ''), $data[0]['periodo']);
            $ult_per = intval($cadena);
        }

        if ($act_per > $ult_per) {
            return true;
        } elseif ($act_per == $ult_per) {
            return 2;
        } else {
            return false;
        }
    }
}
