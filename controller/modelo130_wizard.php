<?php

/*
 * This file is part of modelo_130
 * Copyright (C) 2017  Pablo Zerón Gea  pablozg@gmail.com
 * Copyright (C) 2014-2017  Carlos Garcia Gomez  neorazorx@gmail.com
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

require_model('cuenta_especial.php');

/**
 * Description of modelo130_wizard
 *
 * Basado en el wizard de facturacion_base
 *
 * @author Pablo Zerón Gea
 */
class modelo130_wizard extends fs_controller
{
    public $cuenta_especial;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Instalación modelo 130', 'admin', false, false);
    }

    protected function private_core()
    {

        /// ¿Hay errores? Usa informes > Errores
        if ($this->get_errors()) {
            $this->new_message('Puedes solucionar la mayoría de errores en la base de datos ejecutando el '
                    . '<a href="index.php?page=informe_errores" target="_blank">informe de errores</a> '
                    . 'sobre las tablas.');
        }

        $this->cuenta_especial = new cuenta_especial();

        /// Crea cuenta especial para ingresos si no existe
        $cesp = $this->cuenta_especial->get('M130I');
        if (!$cesp) {
            $cesp = new cuenta_especial();
            $cesp->idcuentaesp = 'M130I';
        }

        $cesp->descripcion = "Cuenta especial para calculo de ingresos del modelo 130";

        if ($cesp->save()) {
            $this->new_message('Cuenta especial (M130I) para ingresos creada correctamente.');
        } else {
            $this->new_error_msg('Error al crear la cuenta especial (M130I).');
        }

        $this->cuenta_especial = new cuenta_especial();

        /// Crea cuenta especial para gastos si no existe
        $cesp = $this->cuenta_especial->get('M130G');
        if (!$cesp) {
            $cesp = new cuenta_especial();
            $cesp->idcuentaesp = 'M130G';
        }

        $cesp->descripcion = "Cuenta especial para calculo de gastos del modelo 130";

        if ($cesp->save()) {
            $this->new_message('Cuenta especial (M130G) para gastos creada correctamente.');
        } else {
            $this->new_error_msg('Error al crear la cuenta especial (M130G).');
        }

        $this->cuenta_especial = new cuenta_especial();

        /// Crea cuenta especial para las retenciones si no existe
        $cesp = $this->cuenta_especial->get('M130R');
        if (!$cesp) {
            $cesp = new cuenta_especial();
            $cesp->idcuentaesp = 'M130R';
        }

        $cesp->descripcion = "Cuenta especial para calculo de retenciones del modelo 130";

        if ($cesp->save()) {
            $this->new_message('Cuenta especial (M130R) para retenciones creada correctamente.');
        } else {
            $this->new_error_msg('Error al crear la cuenta especial (M130R).');
        }

        $this->check_menu();
    }

    /**
     * Cargamos el menú en la base de datos, pero en varias pasadas.
     */
    private function check_menu()
    {
        if (file_exists(__DIR__)) {
            $max = 25;

            /// leemos todos los controladores del plugin
            foreach (scandir(__DIR__) as $f) {
                if ($f != '.' and $f != '..' and is_string($f) and strlen($f) > 4 and ! is_dir($f) and $f != __CLASS__ . '.php') {
                    /// obtenemos el nombre
                    $page_name = substr($f, 0, -4);

                    /// lo buscamos en el menú
                    $encontrado = false;
                    foreach ($this->menu as $m) {
                        if ($m->name == $page_name) {
                            $encontrado = true;
                            break;
                        }
                    }

                    if (!$encontrado) {
                        require_once __DIR__ . '/' . $f;
                        $new_fsc = new $page_name();

                        if (!$new_fsc->page->save()) {
                            $this->new_error_msg("Imposible guardar la página " . $page_name);
                        }

                        unset($new_fsc);

                        if ($max > 0) {
                            $max--;
                        } elseif (!$this->get_errors()) {
                            $this->recargar = true;
                            $this->new_message('Instalando el menú... &nbsp; <i class="fa fa-refresh fa-spin"></i>');
                            break;
                        }
                    }
                }
            }
        } else {
            $this->new_error_msg('No se encuentra el directorio ' . __DIR__);
        }

        $this->load_menu(true);
    }
}
