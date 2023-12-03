# Instalación
## Requisitos
El servidor donde se va a instalar el aplicativo debe cumplir los siguientes requisitos:

* **Sistema operativo:** distro Linux, Mac o Windows (se recomienda Linux, ya que solo está probado con Linux)
* **Servidor:** Apache 2, PHP 8.3 (incluyendo las extensiones `mysql`, `curl` y `intl`), MySQL.

## Instalación
Para instalar el aplicativo, descarga el código fuente desde [https://github.com/avm99963/hores-external](https://github.com/avm99963/hores-external) y extraelo en el directorio raíz de Apache (`/var/www/html/` es el directorio raíz de Apache por defecto en Ubuntu).

Luego, crea una base de datos MySQL mediante el siguiente comando: `CREATE DATABASE hores CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`, donde `hores` es el nombre para la base de datos que prefieras.

Ahora, para activar los archivos adjuntos, se debe crear una carpeta en el servidor con permisos de lectura y escritura por el usuario del servidor (en el caso de Apache en Ubuntu es `www-data`) donde se guardarán los archivos adjuntos de las incidencias.

!!! warning "Asegúrate de que la carpeta no es accesible desde Internet"
    En caso que la carpeta estuviera dentro del directorio del aplicativo, esta sería accesible desde Internet, ¡y por lo tanto cualquier persona podría visualizar los archivos adjuntos!

!!! note "Tamaño máximo de las subidas de archivos"
    El tamaño de los archivos adjuntos está limitado a 6 MB des del aplicativo. Aun así, PHP por defecto podría tener la directiva `upload_max_filesize = 2M` en `php.ini`. Por este motivo, es conveniente comprobar cuál es el valor de la directiva, y aumentar el valor a como mínimo 6 MB para que el aplicativo funcione correctamente.

Seguidamente, haz una copia del archivo `config.default.php` a `config.php`, y edita esta última copia para configurar algunas opciones del aplicativo.

De momento, estos son los aspectos fundamentales que deberías configurar en ese archivo antes de proceder:

* **`$conf["db"]`**: las credenciales de acceso de la base de datos.
* **`$conf["path"]`**: es la dirección de la página web sin tener en cuenta el dominio. Se usará para configurar las cookies de inicio de sesión. Por ejemplo, si el aplicativo se sirve en `https://example.org/controlhorario/`, el valor se debería configurar como `/controlhorario/`, y si el aplicativo se sirve en `https://example.org/`, el valor se debería configurar como `/`.
* **`$conf["fullPath"]`**: es la dirección de la página web. Se usará para enlazar al aplicativo cuando se envíen notificaciones por correo electrónico. Por ejemplo, si el aplicativo se sirve en `https://example.org/`, el valor se debería configurar como `https://example.org/`.
* **`$conf["attachmentsFolder"]`**: es la ruta a la carpeta donde se guardarán los archivos adjuntos, con una barra al final. Por ejemplo: `/home/user/files/`.

Ahora, accede a una terminal, y ejecuta el siguiente comando en la carpeta `src` del aplicativo para configurar la base de datos y añadir el primer usuario administrador: `php install.php`.

Finalmente, configuraremos que se ejecute un script periódicamente, que será el que registrará cada día los horarios de los trabajadores de ese día, que se desprenden de cada uno de sus horarios base y los calendarios que hay configurados. Para ello, hay que hacer que se ejecute el script `src/cron/generateregistry.php` cada día por la tarde-noche.

!!! note "Cómo configurar la ejecución diaria del script en Ubuntu"
    En Ubuntu, hay un programa instalado por defecto llamado `crontab`, que permite configurar la ejecución periódica de un programa.

    Así pues, podemos ejecutar `crontab -e` y, en el fichero que se abre para editar, podemos añadir lo siguiente en una línea debajo del todo: `1 20 * * * php /var/www/html/src/cron/generateregistry.php`, cambiando la ruta al script a la ruta que corresponda en nustro servidor. Esto lo que hará es ejecutar el script cada día a las 20:01.

    Luego, si queremos ir limpiando la tabla de intentos de inicio de sesión, deberíamos añadir otra línea para el otro script.

A parte, hay otro script que también puede interesar ejecutar de vez en cuando. En la base de datos hay una tabla que se encarga de ir guardando los intentos de inicio de sesión que se hacen con el fin de prevenir posibles ataques de fuerza bruta. Si hay muchos intentos de inicio de sesión, esta tabla puede crecer mucho de tamaño, así que existe un script `cron/cleansigninattempts.php` que borra todos los registros de la tabla con antigüedad mayor a `$conf["signinThrottling"]["retentionDays"]`. Se puede configurar también la ejecución periódica de este script, por ejemplo con un periodo de 1 semana o menos, dependiendo de cuán rápido crece el número de registros de la tabla.
