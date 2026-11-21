<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.


/**
 * Strings for factor_oksigeniaaty, in Spanish.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['approve:approved'] = 'Listo: ya estás dentro. Puedes volver a la otra pantalla.';
$string['approve:changepassword'] = 'Cambiar la contraseña';
$string['approve:denied'] = 'Hemos parado esa entrada. Si no has sido tú, cambia la contraseña.';
$string['approve:deny'] = 'No he sido yo';
$string['approve:donetitle'] = 'Aprobada';
$string['approve:expired'] = 'Este aviso ha caducado. Si sigues entrando, pide otro en la pantalla.';
$string['approve:none'] = 'Ahora no hay nada que aprobar.';
$string['approve:pick'] = 'Si eres tú, toca el número que ves en la pantalla de entrada.';
$string['approve:title'] = '¿Eres tú?';
$string['approve:who'] = 'Alguien está entrando en {$a} con tu cuenta.';
$string['approve:wrong'] = 'Ese no es el número de la pantalla, así que hemos parado la entrada. Si eras tú, vuelve a intentarlo.';
$string['device'] = '{$a->browser} en {$a->os}';
$string['device:app'] = 'La app';
$string['device:unknown'] = 'Un navegador desconocido';
$string['error:notapproved'] = 'Todavía no está aprobada. Abre el aviso en el móvil y toca el número.';
$string['info'] = 'Cada entrada se aprueba desde el aviso que llega al móvil, en la app o en un chat.';
$string['label'] = 'Móvil';
$string['logindesc'] = 'Te mandamos un aviso al móvil para aprobar esta entrada.';
$string['loginoption'] = 'Aprobar desde el móvil';
$string['loginsubmit'] = 'Ya lo he aprobado';
$string['logintitle'] = 'Aprueba la entrada desde el móvil';
$string['marandu:login_check'] = 'Aprobar una entrada';
$string['marandu:login_check_button'] = 'Abrir';
$string['marandu:login_check_desc'] = 'Cuando alguien entra con la cuenta, para aprobarlo con el número de la pantalla';
$string['marandu:login_check_option_deny'] = 'No he sido yo';
$string['marandu:login_check_template'] = '¿Eres tú? Entrada en {{1}}: {{2}}. Si eres tú, toca el número que ves en la pantalla: {{3}}, {{4}} o {{5}}.';
$string['marandu:label'] = 'entrada';
$string['messageprovider:approval'] = 'Peticiones para aprobar una entrada';
$string['mobile:title'] = '¿Eres tú?';
$string['notify:body'] = '¿Eres tú? Entrada con tu cuenta: {$a}. Toca aquí y elige el número de la pantalla.';
$string['notify:link'] = 'Aprobar o parar';
$string['notify:subject'] = '¿Eres tú? Entrada en {$a}';
$string['pluginname'] = 'Aprobar desde el móvil';
$string['privacy:metadata:factor_oksigeniaaty_req'] = 'Cada petición para aprobar una entrada, guardada un día.';
$string['privacy:metadata:factor_oksigeniaaty_req:device'] = 'El navegador y el sistema de la entrada.';
$string['privacy:metadata:factor_oksigeniaaty_req:ip'] = 'La dirección desde la que se entró.';
$string['privacy:metadata:factor_oksigeniaaty_req:status'] = 'Qué pasó con la petición.';
$string['privacy:metadata:factor_oksigeniaaty_req:timeanswered'] = 'Cuándo se respondió.';
$string['privacy:metadata:factor_oksigeniaaty_req:timecreated'] = 'Cuándo se hizo.';
$string['privacy:metadata:factor_oksigeniaaty_req:userid'] = 'Quien entra.';
$string['privacy:metadata:factor_oksigeniaaty_req:via'] = 'Dónde se respondió: la app, la web o un chat.';
$string['settings:description'] = 'Cada entrada se aprueba desde un aviso en el móvil: la pantalla de entrada enseña un número, y el móvil ofrece tres números y «No he sido yo». Tocar el de la pantalla deja seguir; cualquier otra respuesta para la entrada, así que un toque con prisa no deja pasar a nadie. Vale para quien tiene la app en un móvil que recibe los avisos del sitio, y en Telegram y WhatsApp con Oksigenia Marandu.';
$string['settings:shortdescription'] = 'Aprobar cada entrada desde un aviso en el móvil, tocando el número de la pantalla.';
$string['settings:validity'] = 'Tiempo para responder';
$string['settings:validity_help'] = 'Cuánto tiene el móvil para responder una petición. Pasado ese tiempo, la pantalla de entrada ofrece mandar otra.';
$string['summarycondition'] = 'tiene la app en un móvil que recibe avisos, o un chat para ellos';
$string['wait'] = 'Esperando a tu móvil…';
$string['wait:again'] = 'Mandar otro aviso';
$string['wait:codearia'] = 'El número que hay que tocar en el móvil: {$a}';
$string['wait:expired'] = 'El aviso ha caducado.';
$string['wait:pick'] = 'Ábrelo y toca este número:';
$string['wait:toomany'] = 'Demasiados avisos seguidos. Espera unos minutos o elige otra forma de entrar.';
$string['wait:unsent'] = 'No hemos podido mandar el aviso a tu móvil. Elige otra forma de entrar.';
$string['where'] = '{$a->device}, a las {$a->time}';
