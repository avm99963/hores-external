# Personas
La sección **Personas** contiene una lista de las personas añadidas al aplicativo, que pueden ser tanto trabajadoras de la(s) empresa(s) como no. Cada una de las personas de la lista tiene asociado un usuario y contraseña con los cuales pueden iniciar sesión en el sitio web (exceptuando las personas con rol de Trabajador si la opción `$conf["enableWorkerUI"]` está desactivada).

Es posible que en la documentación a una persona se le domine también como *usuario*.

## Añadir personas
### Manualmente
Se pueden añadir personas manualmente haciendo clic en el botón <i class="material-icons">add</i> de la parte inferior derecha de la página, y rellenando el formulario que aparece.

### En masa mediante un archivo CSV
Para ello, haz clic en el botón <i class="material-icons">file_upload</i> de la parte inferior derecha de la página. Deberás subir un archivo CSV con el formato especificado en las intrucciones que aparecerán.

En resumen, debes subir un archivo CSV delimitado con `;`, y con la cabecera `dni;name;category;email;companies`. Luego, las siguientes líneas deben ser las personas que quieres agregar, donde la columna `category` contendrá los IDs de las categorías ya creadas en el aplicativo en las que querrás agregar cada persona (o -1 si no quieres agregarla a ninguna categoría), y la columna `companies` contendrá una cadena de caracteres con los IDs de las empresas separados por comas (sin espacios después o antes de la coma) de las empresas a las que querrás dar de alta cada persona. Las demás columnas contendrán texto.

Puedes generar el archivo CSV desde programas como OpenOffice, LibreOffice, Google Sheets o Excel.

## Categorías
Las categorias, que se configuran desde la sección **Configuración**, sirven para clasificar las personas en diferentes grupos. Esto aporta las siguientes ventajas:

* En el listado de personas se pueden filtrar personas por categoría.
* Cuando se exporta un PDF, en el selector de personas se pueden seleccionar o deseleccionar con un solo clic todos los trabajadores pertenecientes a una categoría.
* El aplicativo permite configurar un calendario de días lectivos/no lectivos/festivos que se aplica a todos los trabajadores, pero también es posible configurar un calendario al nivel de una categoría en el caso que el calendario sea diferente. Para más información, se puede consultar la [documentación para la sección de calendarios](calendarios.md).

Finalmente, esta es la configuración que admite cada categoría:

* Cada categoría puede tener configurada una **categoría padre**. Esto se usa únicamente para que todas las categorías que tengan configurada una categoría padre se asocien en una sola en los filtros del listado de personas.
* A cada categoría también se le pueden asignar una lista de **correos electrónicos de los responsables**, que se usa para definir los correos electrónicos de los responsables de los trabajadores de esa categoría. En el momento se usa para enviar ciertas notificaciones de correo electrónico cuando se crean ciertos tipos de incidencia (para más información, consúltese [este artículo](instalacion-y-configuracion/configuracion.md#notificaciones-por-correo-electronico)).
