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

$string['accesses:addphone'] = 'Dar de alta otro móvil';
$string['accesses:allgood'] = 'En los últimos {$a} días, todas las entradas las aprobaste tú.';
$string['accesses:approved'] = 'Aprobada';
$string['accesses:approvers'] = 'Aprueban tus entradas: {$a}.';
$string['accesses:at'] = '{$a->day} a las {$a->time}';
$string['accesses:back'] = 'Volver a «Tus accesos»';
$string['accesses:chats'] = 'Chats que aprueban';
$string['accesses:chatsince'] = 'Unido {$a}';
$string['accesses:dateformat'] = '%d de %B';
$string['accesses:dateformatyear'] = '%d de %B de %Y';
$string['accesses:denied'] = 'Parada con «No soy yo»';
$string['accesses:expired'] = 'Sin respuesta';
$string['accesses:history'] = 'Entradas con tu contraseña';
$string['accesses:historyintro'] = 'Cada línea es una vez que alguien escribió tu contraseña y se pidió aprobar la entrada en el móvil, en los últimos {$a} días. Si no reconoces alguna, cambia la contraseña.';
$string['accesses:lastused'] = 'Aprobó por última vez {$a}';
$string['accesses:neverused'] = 'Aún no ha aprobado ninguna';
$string['accesses:nohistory'] = 'En los últimos {$a} días no se ha pedido aprobar ninguna entrada.';
$string['accesses:nophones'] = 'Ningún móvil aprueba tus entradas.';
$string['accesses:onday'] = 'el {$a}';
$string['accesses:pending'] = 'Esperando respuesta';
$string['accesses:phones'] = 'Móviles que aprueban';
$string['accesses:removedphone'] = 'Hecho: ese móvil ya no puede aprobar tus entradas.';
$string['accesses:removephone'] = 'Quitar este móvil';
$string['accesses:removephoneconfirm'] = '{$a} dejará de aprobar tus entradas. Para volver a usarlo, tendrás que darlo de alta otra vez.';
$string['accesses:since'] = 'Dado de alta {$a}';
$string['accesses:stoppedmany'] = '{$a->count} entradas se pararon; la última, {$a->when}. Si no fuiste tú, cambia la contraseña.';
$string['accesses:stoppedone'] = 'Una entrada se paró {$a}. Si no fuiste tú, cambia la contraseña.';
$string['accesses:summaryboth'] = '{$a->phones} y {$a->chats}';
$string['accesses:summarychats'] = '{$a} chats';
$string['accesses:summaryonechat'] = '1 chat';
$string['accesses:summaryonephone'] = '1 móvil';
$string['accesses:summaryphones'] = '{$a} móviles';
$string['accesses:timeformat'] = '%H:%M';
$string['accesses:title'] = 'Tus accesos';
$string['accesses:today'] = 'Hoy';
$string['accesses:via:app'] = 'en la app';
$string['accesses:via:telegram'] = 'por Telegram';
$string['accesses:via:web'] = 'en la web';
$string['accesses:via:whatsapp'] = 'por WhatsApp';
$string['accesses:wrong'] = 'Parada: número equivocado';
$string['accesses:yesterday'] = 'Ayer';
$string['action:manage'] = 'Tus accesos';
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
$string['approve:nobody'] = 'Nadie del centro te pedirá que toques un número. Si alguien te lo pide, toca «No soy yo».';
$string['approve:nobodytyped'] = 'Nadie del centro te pedirá que escribas un número. Si alguien te lo pide, toca «No soy yo».';
$string['approve:none'] = 'Ahora no hay nada que aprobar.';
$string['approve:notenrolled'] = 'Este móvil no está dado de alta para aprobar entradas. Dalo de alta en la web, en tus preferencias: Autenticación multifactor, «Aprobar desde el móvil».';
$string['approve:pick'] = 'Si eres tú, toca el número que ves en la otra pantalla.';
$string['approve:pickhint'] = 'Si tocas otro, paramos la entrada.';
$string['approve:title'] = '¿Eres tú?';
$string['approve:type'] = 'Si eres tú, escribe el número que ves en la otra pantalla.';
$string['approve:typebutton'] = 'Aprobar';
$string['approve:typehint'] = 'Si escribes otro, paramos la entrada.';
$string['approve:typelabel'] = 'El número de la otra pantalla';
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
$string['managefactor'] = 'Tus accesos';
$string['manageinfo'] = 'Lo que aprueba tus entradas: {$a}.';
$string['marandu:label'] = 'entrada';
$string['marandu:login_check'] = 'Aprobar una entrada';
$string['marandu:login_check_button'] = 'Abrir';
$string['marandu:login_check_desc'] = 'Cuando alguien entra con la cuenta, para aprobarlo con el número de la pantalla';
$string['marandu:login_check_option_deny'] = 'No he sido yo';
$string['marandu:login_check_template'] = '¿Eres tú? Entrada en {{1}}: {{2}}. Si eres tú, toca el número que ves en la pantalla: {{3}}, {{4}} o {{5}}. Nadie del centro te pedirá que toques uno: si alguien te lo pide, toca «No he sido yo».';
$string['marandu:login_check_typed'] = 'Aprobar una entrada tecleando el número';
$string['marandu:login_check_typed_button'] = 'Abrir';
$string['marandu:login_check_typed_desc'] = 'Para los administradores del sitio, que escriben el número en la app en vez de elegirlo: el chat solo les avisa y les deja parar la entrada.';
$string['marandu:login_check_typed_option_deny'] = 'No he sido yo';
$string['marandu:login_check_typed_template'] = '¿Eres tú? Entrada en {{1}}: {{2}}. Si eres tú, abre la app y escribe allí el número que ves en la pantalla. Nadie del centro te lo pedirá: si alguien te lo pide, toca «No he sido yo».';
$string['messageprovider:approval'] = 'Peticiones para aprobar una entrada';
$string['messageprovider:newphone'] = 'Un móvil nuevo puede aprobar tus entradas';
$string['messageprovider:recovery'] = 'Códigos de recuperación creados o usados';
$string['mobile:title'] = '¿Eres tú?';
$string['newphone:body'] = '{$a->device} se dio de alta el {$a->time} para aprobar tus entradas en {$a->site}. Si no has sido tú, quítalo en «Tus accesos» y cambia la contraseña.';
$string['newphone:link'] = 'Tus accesos';
$string['newphone:subject'] = 'Un móvil nuevo puede aprobar tus entradas en {$a->site}';
$string['notify:body'] = '¿Entras tú? {$a}. Toca para aprobar o parar.';
$string['notify:link'] = 'Aprobar o parar';
$string['notify:subject'] = '¿Eres tú? Entrada en {$a}';
$string['pluginname'] = 'Aprobar desde el móvil';
$string['privacy:metadata:factor_oksigeniaaty_codes'] = 'Cada código de recuperación de la persona, solo su huella; se guarda hasta que se crean otros.';
$string['privacy:metadata:factor_oksigeniaaty_codes:timecreated'] = 'Cuándo se creó.';
$string['privacy:metadata:factor_oksigeniaaty_codes:timeused'] = 'Cuándo se usó.';
$string['privacy:metadata:factor_oksigeniaaty_codes:userid'] = 'De quién es el código.';
$string['privacy:metadata:factor_oksigeniaaty_enrol'] = 'Cada código para dar de alta un móvil, guardado un día.';
$string['privacy:metadata:factor_oksigeniaaty_enrol:status'] = 'Si se usó.';
$string['privacy:metadata:factor_oksigeniaaty_enrol:timecreated'] = 'Cuándo se hizo.';
$string['privacy:metadata:factor_oksigeniaaty_enrol:userid'] = 'La persona cuyo móvil da de alta.';
$string['privacy:metadata:factor_oksigeniaaty_req'] = 'Cada petición para aprobar una entrada, guardada 30 días para que la persona la vea en «Tus accesos».';
$string['privacy:metadata:factor_oksigeniaaty_req:device'] = 'El navegador y el sistema de la entrada.';
$string['privacy:metadata:factor_oksigeniaaty_req:ip'] = 'La dirección desde la que se entró.';
$string['privacy:metadata:factor_oksigeniaaty_req:status'] = 'Qué pasó con la petición.';
$string['privacy:metadata:factor_oksigeniaaty_req:timeanswered'] = 'Cuándo se respondió.';
$string['privacy:metadata:factor_oksigeniaaty_req:timecreated'] = 'Cuándo se hizo.';
$string['privacy:metadata:factor_oksigeniaaty_req:userid'] = 'Quien entra.';
$string['privacy:metadata:factor_oksigeniaaty_req:via'] = 'Dónde se respondió: la app, la web o un chat.';
$string['recovery:create'] = 'Crear códigos';
$string['recovery:createnew'] = 'Crear códigos nuevos';
$string['recovery:createnewconfirm'] = 'Los códigos que tienes dejarán de valer. Imprime o guarda los nuevos.';
$string['recovery:empty'] = 'Escribe uno de tus códigos de recuperación.';
$string['recovery:enter'] = 'Entrar con el código';
$string['recovery:intro'] = 'Para el día que no tengas el móvil a mano: cada código te deja entrar una vez, en lugar de aprobar en el móvil.';
$string['recovery:label'] = 'Código de recuperación';
$string['recovery:lastused'] = 'El último se usó {$a}.';
$string['recovery:left'] = 'Te quedan {$a->left} de {$a->total}, creados {$a->made}.';
$string['recovery:madebody'] = 'Se crearon el {$a->time}, y los anteriores ya no valen. Si no has sido tú, cambia la contraseña y crea otros en «Tus accesos».';
$string['recovery:madesubject'] = 'Códigos de recuperación nuevos en {$a->site}';
$string['recovery:mark'] = 'Marca la casilla de cada código cuando lo uses.';
$string['recovery:none'] = 'Aún no tienes códigos.';
$string['recovery:once'] = 'Cada código sirve una vez.';
$string['recovery:only'] = 'Ningún móvil ni chat puede aprobar esta entrada: entra con uno de tus códigos de recuperación.';
$string['recovery:pagefor'] = '{$a->name}, en {$a->site}. Creados el {$a->date}.';
$string['recovery:pageintro'] = 'Guárdalos en papel, en un sitio seguro. Solo se ven ahora: si los pierdes, crea otros.';
$string['recovery:pagetitle'] = 'Tus códigos de recuperación';
$string['recovery:print'] = 'Imprimir';
$string['recovery:summary'] = '¿No tienes el móvil? Usa un código de recuperación';
$string['recovery:title'] = 'Códigos de recuperación';
$string['recovery:usedbody'] = 'Alguien entró con uno de tus códigos de recuperación el {$a->time}. Te quedan {$a->left}. Si no has sido tú, cambia la contraseña y crea códigos nuevos en «Tus accesos».';
$string['recovery:usedsubject'] = 'Se ha usado un código de recuperación en {$a->site}';
$string['recovery:wrong'] = 'Ese código no vale: no es tuyo, ya se usó o está mal escrito.';
$string['revokefactorconfirmation'] = '¿Quitar «{$a}»?';
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
$string['setup:since'] = 'desde {$a}';
$string['setup:step1'] = 'Abre en el móvil la app del centro, con tu cuenta.';
$string['setup:step2'] = 'Lee este código con el móvil (con la app o con la cámara) y ábrelo en la app.';
$string['setup:waiting'] = 'Esperando a la app… El código dura {$a} minutos.';
$string['setupfactor'] = 'Dar de alta un móvil para aprobar';
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
$string['wait:type'] = 'En el móvil, escribe este número:';
$string['wait:unsent'] = 'No hemos podido mandarte el aviso. Entra de otra forma, aquí abajo.';
$string['wait:valid'] = 'El aviso vale {$a} minutos.';
$string['where'] = '{$a->device}, a las {$a->time}';
