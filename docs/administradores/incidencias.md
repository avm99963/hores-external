# Incidencias
Las incidencias son elementos que se sobreponen a los horarios base de los trabajadores para acabar de concretar qué horario exacto ha seguido un trabajador un día en específico.

## Tipos de incidencia
Cada incidencia es de un tipo diferente. Por ejemplo, una incidencia puede consistir de una baja médica, trabajar horas extras o asistir a una formación. Todas estas incidencias se deben tratar de manera diferente, puesto que, por ejemplo, unas implican que el trabajador está presente en el espacio de trabajo y otras implican que está ausente, entre otras cosas. Es por ello que los administradores deben configurar varios tipos de incidencias con las repercusiones específicas que tienen cada una. Una vez hecho esto, al crear una incidencia se deberá escoger un tipo, y esto junto a la configuración de ese tipo determinará cómo tratará el aplicativo dicha incidencia.

Los tipos de incidencia se pueden configurar desde la sección **Configuración**.

### Opciones para los tipos de incidencia
Cada tipo de incidencia tiene configuradas las siguientes opciones:

* **Presente** (<i class="material-icons" style="color: rgba(0,0,0,.54);">business</i>): Indica si el trabajador está físicamente presente en el espacio de trabajo durante la incidencia. Si está activada, las incidencias de este tipo cuentan como horas positivas en el recuento de horas que aparece en los PDF que se exportan, y en caso de estar desactivada cuentan como horas negativas.
* **Remunerada** (<i class="material-icons" style="color: rgba(0,0,0,.54);">euro_symbol</i>): Indica si el trabajador es remunerado las horas que dura la incidencia. No tiene ningún efecto en el aplicativo, pero aparece en las exportaciones CSV que se hacen de incidencias.
* **Puede autorrellenarse** (<i class="material-icons" style="color: rgba(0,0,0,.54);">face</i>): Indica si se permite que el trabajador pueda rellenar una incidencia de este tipo él mismo (con la posterior verificación por parte de un administrador).
* **Notifica** (<i class="material-icons" style="color: rgba(0,0,0,.54);">email</i>): Indica si la introducción de una incidencia de este tipo notifica por correo electrónico a las personas especificadas en la categoría del trabajador.
* **Se autovalida** (<i class="material-icons" style="color: rgba(0,0,0,.54);">verified_user</i>): Indica si al introducir una incidencia de este tipo se autovalida sin necesidad de ser validada posteriormente por el trabajador.

## Cómo añadir incidencias
Hay diferentes maneras de añadir una incidencia:

### Añadir una incidencia puntual a una persona
Se puede agregar una incidencia puntual a una persona de muchas maneras dentro del aplicativo, pero una manera de realizarlo es acceder a la sección **Incidencias**, hacer clic en el botón <i class="material-icons">add</i> en la esquina inferior derecha de la pantalla y rellenar el formulario que aparece.

### Añadir una incidencia recurrente a una persona
También hay varias maneras de realizar esto, pero una de ellas es acceder a la sección **Incidencias** y hacer clic en el botón <i class="material-icons">repeat</i>.

### Añadir una incidencia puntual a varias personas a la vez
Para realizar esto, hay que acceder a la sección **Trabajadores**, seleccionar los trabajadores a los cuales se les quiere añadir la incidencia, y hacer clic en el botón <i class="material-icons">note_add</i>.

## Estado de una incidencia
Cada incidencia puede estar en diferentes estados en el transcurso del tiempo:

* **Pendiente de revisión** (<i class="material-icons" style="color: #ff9800;">new_releases</i>): una incidencia está en este estado cuando la ha añadido un trabajador y todavía no ha sido revisada por los administradores.
* **Programada** (<i class="material-icons" style="color: #ff9800;">schedule</i>): cuando una incidencia se ha añadido correctamente (y si la ha introducido un trabajador ya se ha revisado) pero ocurrirá en el futuro.
* **Registrada** (<i class="material-icons" style="color: #4caf50;">check</i>): cuando una incidencia ya ha sucedido.
* **Validada** (<i class="material-icons" style="color: #4caf50;">verified_user</i>): cuando una incidencia ya ha sucedido y el trabajador la ha validado (para más información, véase el apartado [validaciones](validaciones.md)).
* **Rechazada al revisar** (<i class="material-icons" style="color: #f44336;">block</i>): cuando una incidencia creada por un trabajador se rechaza en el proceso de revisión. Estas incidencias no tienen ningún efecto en el aplicativo, pero se mantienen en el aplicativo para poderse consultar.
* **Invalidada manualmente** (<i class="material-icons" style="color: #f44336;">delete_forever</i>): cuando una incidencia que estaba registrada o validada se elimina, esta se mantiene en el aplicativo con el estado de invalidada. Estas incidencias no tienen ningún efecto en el aplicativo, y también se mantienen en el aplicativo únicamente para que se puedan consultar.

!!! success "PDFs exportados"
    Las únicas incidencias que se incluyen en los PDFs exportados son las registradas y las validadas.

## Vida de una incidencia
Este es un diagrama que expone los estados por los que puede pasar una incidencia a lo largo de su vida:

<div style="text-align: center;"><img src="../../img/life-of-an-incident.svg" style="max-width: 900px;"></div>

## Observaciones y archivos adjuntos
Las incidencias pueden tener asociadas observaciones y archivos adjuntos.

Existen dos campos de observaciones: uno donde puede escribir el trabajador y otro en el que solo pueden escribir los administradores. El primero sirve para dejar constancia de las observaciones en el aplicativo y que sean visibles por los administradores, mientras que el segundo se usa para añadir observaciones que serán visibles en el PDF exportado.

Con respecto a los archivos adjuntos, cada incidencia puede tener asociados varios archivos adjuntos. Tanto el trabajador como los administradores pueden añadir archivos adjuntos, y ambas partes pueden elimnar también los archivos adjuntos que hay añadidos en la incidencia.
