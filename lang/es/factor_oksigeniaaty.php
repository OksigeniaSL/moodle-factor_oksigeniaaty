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

$string['approve:approved'] = 'Listo: ya puedes seguir en la otra pantalla.';
$string['approve:changepassword'] = 'Cambiar la contraseña';
$string['approve:denied'] = 'Nadie ha entrado. Cambia la contraseña para que no lo intenten otra vez.';
$string['approve:deny'] = 'No soy yo: parar la entrada';
$string['approve:denytitle'] = 'Entrada parada';
$string['approve:donetitle'] = 'Aprobada';
$string['approve:expired'] = 'Este aviso ha caducado. Si sigues entrando, pide otro en la pantalla.';
$string['approve:expiredtitle'] = 'Aviso caducado';
$string['approve:failed'] = 'No se ha podido enviar. Comprueba la conexión y vuelve a intentarlo.';
$string['approve:leftminute'] = 'Queda 1 minuto para contestar.';
$string['approve:leftminutes'] = 'Quedan {$a} minutos para contestar.';
$string['approve:leftseconds'] = 'Queda menos de un minuto para contestar.';
$string['approve:nobody'] = 'Toca un número solo si eres tú quien entra ahora. Nadie del centro te pedirá que toques uno.';
$string['approve:none'] = 'Ahora no hay nada que aprobar.';
$string['approve:notenrolled'] = 'Este móvil no está dado de alta para aprobar entradas. Dalo de alta en la web, en tus preferencias: Autenticación multifactor, «Aprobar desde el móvil».';
$string['approve:pick'] = 'Si eres tú, toca el número que ves en la otra pantalla.';
$string['approve:pickhint'] = 'Si tocas otro, paramos la entrada.';
$string['approve:title'] = '¿Eres tú?';
$string['approve:who'] = 'Hay una entrada en {$a} con tu cuenta:';
$string['approve:wrong'] = 'Ese no es el número de la pantalla, así que hemos parado la entrada. Si eras tú, vuelve a intentarlo.';
$string['approve:wrongtitle'] = 'Entrada parada';
$string['chan:telegram'] = 'Telegram';
$string['chan:whatsapp'] = 'WhatsApp';
$string['device'] = '{$a->browser} en {$a->os}';
$string['device:app'] = 'La app';
$string['device:unknown'] = 'Un navegador desconocido';
$string['enrol:done'] = 'Listo: este móvil aprueba tus entradas.';
$string['enrol:donehint'] = 'Puedes volver a la otra pantalla.';
$string['enrol:failed'] = 'No se ha podido dar de alta este móvil: el código ya se usó o caducó, o es de otra cuenta. Vuelve a abrir la página en la web.';
$string['enrol:title'] = 'Dar de alta este móvil';
$string['enrol:working'] = 'Dando de alta este móvil…';
$string['enrolpage:intro'] = 'Este código da de alta el móvil en la app del centro.';
$string['enrolpage:noapp'] = 'Si no se abre nada, instala la app del centro, entra con tu cuenta y vuelve a leer el código.';
$string['enrolpage:nocode'] = 'Este enlace no está completo. Vuelve a abrir la página en la web.';
$string['enrolpage:open'] = 'Abrir en la app';
$string['enrolpage:title'] = 'Dar de alta este móvil';
$string['error:notapproved'] = 'Aún no lo has aprobado en el móvil.';
$string['info'] = 'Cada entrada se aprueba desde un móvil dado de alta, en la app o en un chat unido desde estas preferencias.';
$string['label'] = 'Móvil';
$string['logindesc'] = 'Esta entrada se aprueba desde tu móvil.';
$string['loginoption'] = 'Aprobar desde el móvil';
$string['loginsubmit'] = 'Ya lo he aprobado';
$string['logintitle'] = 'Aprueba la entrada desde el móvil';
$string['marandu:label'] = 'entrada';
$string['marandu:login_check'] = 'Aprobar una entrada';
$string['marandu:login_check_button'] = 'Abrir';
$string['marandu:login_check_desc'] = 'Cuando alguien entra con la cuenta, para aprobarlo con el número de la pantalla';
$string['marandu:login_check_option_deny'] = 'No he sido yo';
$string['marandu:login_check_template'] = '¿Eres tú? Entrada en {{1}}: {{2}}. Si eres tú, toca el número que ves en la pantalla: {{3}}, {{4}} o {{5}}. Nadie del centro te pedirá que toques uno.';
$string['messageprovider:approval'] = 'Peticiones para aprobar una entrada';
$string['messageprovider:newphone'] = 'Un móvil nuevo puede aprobar tus entradas';
$string['mobile:title'] = '¿Eres tú?';
$string['newphone:body'] = '{$a->device} se dio de alta el {$a->time} para aprobar tus entradas en {$a->site}. Si no has sido tú, quítalo en tus preferencias (Autenticación multifactor) y cambia la contraseña.';
$string['newphone:link'] = 'Mis móviles';
$string['newphone:subject'] = 'Un móvil nuevo puede aprobar tus entradas en {$a->site}';
$string['notify:body'] = '¿Entras tú? {$a}. Toca para aprobar o parar.';
$string['notify:link'] = 'Aprobar o parar';
$string['notify:subject'] = '¿Eres tú? Entrada en {$a}';
$string['pluginname'] = 'Aprobar desde el móvil';
$string['privacy:metadata:factor_oksigeniaaty_enrol'] = 'Cada código para dar de alta un móvil, guardado un día.';
$string['privacy:metadata:factor_oksigeniaaty_enrol:status'] = 'Si se usó.';
$string['privacy:metadata:factor_oksigeniaaty_enrol:timecreated'] = 'Cuándo se hizo.';
$string['privacy:metadata:factor_oksigeniaaty_enrol:userid'] = 'La persona cuyo móvil da de alta.';
$string['privacy:metadata:factor_oksigeniaaty_req'] = 'Cada petición para aprobar una entrada, guardada un día.';
$string['privacy:metadata:factor_oksigeniaaty_req:device'] = 'El navegador y el sistema de la entrada.';
$string['privacy:metadata:factor_oksigeniaaty_req:ip'] = 'La dirección desde la que se entró.';
$string['privacy:metadata:factor_oksigeniaaty_req:status'] = 'Qué pasó con la petición.';
$string['privacy:metadata:factor_oksigeniaaty_req:timeanswered'] = 'Cuándo se respondió.';
$string['privacy:metadata:factor_oksigeniaaty_req:timecreated'] = 'Cuándo se hizo.';
$string['privacy:metadata:factor_oksigeniaaty_req:userid'] = 'Quien entra.';
$string['privacy:metadata:factor_oksigeniaaty_req:via'] = 'Dónde se respondió: la app, la web o un chat.';
$string['settings:description'] = 'Cada entrada se aprueba desde un aviso en un móvil dado de alta antes, en las preferencias de la autenticación multifactor y tras pasar otro factor: la app lee allí un QR y recibe una clave propia, y cada respuesta va firmada con ella. La pantalla de entrada enseña un número, y el móvil ofrece tres números y «No soy yo»: tocar el de la pantalla deja seguir; cualquier otra respuesta para la entrada y frena las peticiones nuevas durante un rato. Con Oksigenia Marandu, también puede responder un chat de Telegram o WhatsApp unido desde esa misma página.';
$string['settings:shortdescription'] = 'Aprobar cada entrada desde un aviso en el móvil, tocando el número de la pantalla.';
$string['settings:validity'] = 'Tiempo para responder';
$string['settings:validity_help'] = 'Cuánto tiene el móvil para responder una petición. Pasado ese tiempo, la pantalla de entrada ofrece mandar otra.';
$string['setup:again'] = 'El código de esta página ya se usó o caducó. Vuelve a abrir la página para tener otro.';
$string['setup:chats'] = 'O aprueba por un chat (uniéndolo desde aquí; un chat unido en otro sitio no aprueba):';
$string['setup:done'] = 'Hecho';
$string['setup:intro'] = 'Este móvil aprobará tus entradas con una clave propia.';
$string['setup:notyet'] = 'El móvil aún no está dado de alta: lee el código con la app.';
$string['setup:ownchats'] = 'Chats que pueden aprobar tus entradas (quita el que no sea tuyo):';
$string['setup:qralt'] = 'Código para dar de alta el móvil';
$string['setup:registered'] = 'Listo: el móvil está dado de alta.';
$string['setup:remove'] = 'Quitar';
$string['setup:samephone'] = '¿Estás ahora en ese móvil?';
$string['setup:samephonelink'] = 'Dar de alta este móvil';
$string['setup:since'] = 'desde el {$a}';
$string['setup:step1'] = 'Abre en el móvil la app del centro, con tu cuenta.';
$string['setup:step2'] = 'Lee este código con el móvil (con la app o con la cámara) y ábrelo en la app.';
$string['setup:waiting'] = 'Esperando a la app… El código dura {$a} minutos.';
$string['stopped:again'] = 'Volver a entrar';
$string['stopped:denied'] = 'Esta entrada se ha parado desde el móvil. Nadie ha entrado.';
$string['stopped:title'] = 'Entrada parada';
$string['stopped:wrong'] = 'En el móvil se ha tocado otro número, así que hemos parado esta entrada. Vuelve a entrar y toca el número que veas aquí.';
$string['summarycondition'] = 'ha dado de alta un móvil, con la app o con un chat';
$string['task:cleanup'] = 'Borrar las peticiones viejas para aprobar una entrada';
$string['unlinked'] = 'Hecho: ese chat ya no puede aprobar tus entradas.';
$string['wait'] = 'Esperando a tu móvil…';
$string['wait:again'] = 'Mandar otro aviso';
$string['wait:codehidden'] = 'Número que tienes que tocar en el móvil:';
$string['wait:expired'] = 'El aviso ha caducado (dura {$a} minutos).';
$string['wait:left'] = 'Quedan {$a} para aprobarlo en el móvil.';
$string['wait:pick'] = 'En el móvil te saldrán tres números. Toca este:';
$string['wait:resend'] = 'Mandar otro aviso';
$string['wait:resendhint'] = '¿No te llega? Mira en la campana de la app o en tu Telegram o WhatsApp.';
$string['wait:sayhalf'] = 'Quedan treinta segundos para aprobarlo en el móvil.';
$string['wait:sayone'] = 'Queda un minuto para aprobarlo en el móvil.';
$string['wait:saytwo'] = 'Quedan dos minutos para aprobarlo en el móvil.';
$string['wait:sentapp'] = 'Te hemos mandado un aviso a la app del móvil.';
$string['wait:sentboth'] = 'Te hemos mandado un aviso a la app del móvil y un mensaje por Telegram o WhatsApp.';
$string['wait:sentchat'] = 'Te hemos mandado un mensaje por Telegram o WhatsApp.';
$string['wait:stopped'] = 'Hace poco se paró una entrada desde tu móvil. Espera un rato o entra de otra forma, aquí abajo.';
$string['wait:toomany'] = 'Has pedido varios avisos seguidos. Espera 15 minutos o entra de otra forma, aquí abajo.';
$string['wait:unsent'] = 'No hemos podido mandarte el aviso. Entra de otra forma, aquí abajo.';
$string['wait:valid'] = 'El aviso vale {$a} minutos.';
$string['where'] = '{$a->device}, a las {$a->time}';
