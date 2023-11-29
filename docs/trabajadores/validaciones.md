# Validaciones
Con el fin de corroborar que los trabajadores están conformes con los registros horarios generados y las incidencias que hay en el aplicativo, estos se deben validar por los trabajadores.

## Cómo validar incidencias/registros horarios
Para realizar las validaciones, hay que acceder a la sección **Validaciones** desde el menú lateral del aplicativo. Si hay algún elemento pendiente de validar, la opción **Validaciones** del menú presentará el número de elementos que están pendientes de validar.

En el listado de incidencias/registros, se deben seleccionar aquellos que se deseen validar, comprobando antes que lo que esté establecido sea correcto. Se debe tener en cuenta que los registros horarios son los horarios base teóricos, mientras que las incidencias acaban de perfilar cuál ha sido el horario exacto seguido en un día concreto.

Después de seleccionar los elementos que se desean validar y pulsar el botón **Validar**, aparecerá una página donde se puede escoger el método de validación que se prefiera. Por el momento solo hay la validación por dirección IP, que guardará la dirección IP del trabajador y hora actual como elementos para dar validez a los elementos seleccionados.

Una vez hecho clic en el botón **Confirmar validación**, los elementos ya habrán quedado validados.

!!! note "Más información sobre la validación de incidencias"
    Cuando un trabajador crea una incidencia, esta se autovalida y no hace falta que se valide por el trabajador posteriormente, ya que se presupone que si el trabajador ha introducido una incidencia, está de acuerdo con lo que ha introducido.

    Aun así, si el administrador modifica algún aspecto de la incidencia, la autovalidación se eliminará, y hará falta que el trabajador valide la incidencia posteriormente.

    Además, el administrador puede configurar que se autovaliden ciertos ripos de incidencia aunque los cree el administrador. Esto está pensado para tipos de incidencia como por ejemplo bajas médicas, en las que no se espera que el trabajador tenga que estar pendiente de validar las incidencias creadas.

    ***

    Como apunte final, solo se pueden validar las incidencias que estén en el estado <i class="material-icons" style="color: #4caf50;">check</i> _Registrada_. Es por este motivo que algunas incidencias no saldrán en el listado de validaciones pendientes aunque no se hayan validado todavía.
