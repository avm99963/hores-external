# Verificación en dos pasos
La verificación en dos pasos es un sistema que evita que terceros no autorizados puedan iniciar sesión si te roban la contraseña. Añade una capa de seguridad por encima de tu contraseña que permite verificar con mayor fiabilidad que realmente eres tú quien intenta iniciar sesión.

## ¿Cómo funciona?
Después de configurar la verificación en dos pasos, cuando inicies sesión, a parte de introducir tu contraseña, deberás introducir un código generado en una aplicación en tu móvil. De esta manera, solo un atacante que consiga tu contraseña y acceso a tu móvil sería capaz de iniciar sesión en tu cuenta, algo que es bastante más improbable que si solo tuviera que conseguir tu contraseña.

<div style="text-align: center;"><video controls autoplay loop muted src="../../img/verification-code.mp4"></video></div>

A parte, también se puede configurar como segundo factor, a parte del código, una llave de seguridad. La llave de seguridad puede ser una llave de seguridad física USB o Bluetooth, o un mecanismo de autenticación de tu dispositivo como puede ser por ejemplo tu huella dactilar.

<div style="text-align: center;"><video controls autoplay loop muted src="../../img/webauthn.mp4"></video></div>

!!! note "Compatibilidad de las llaves de seguridad"
    No todos los navegadores soportan el estándar WebAuthn (aunque la gran mayoría sí), que es el que se ha usado en el desarrollo de la aplicación para poder implementar la funcionalidad de las llaves de seguridad. Es por esto que esta funcionalidad podría no funcionar en varios navegadores, donde solo se podría utilizar el código de verificación como segundo factor. Se puede consultar una lista de los navegadores compatibles [aquí](https://caniuse.com/#feat=webauthn).

    Además, las llaves de seguridad de software, como son por ejemplo la autenticación mediante huella dactilar, están disponibles solo en algunos sistemas operativos y navegadores específicos, como Chrome en Android o navegadores de escritorio. Desafortunadamente, los iPads y iPhones, aunque soportan llaves de seguridad físicas, todavía no soportan llaves de seguridad de software. Aun así, Apple tiene en los planes para el 2020 incorporar las llaves de seguridad de software en Safari.

## ¿Cómo se configura?
Para configurarlo, accede a la sección **Seguridad** desde el menú lateral. Allí, en la sección **Verificación en dos pasos**, haz clic en el botón **Empezar**.

Sigue los pasos para instalar la aplicación que generarará los códigos en tu móvil y configrarla, y una vez hecho esto, introduce el código que genera la aplicación para verificar que está todo bien configurado. Una vez hecho esto, ya estará configurada correctamente la verificación en dos pasos.

## ¿Cómo se añaden llaves de seguridad?
Para gestionar tus llaves de seguridad, haz clic en el botón **Llaves de seguridad** de la sección **Seguridad**. Aparecerá una tabla con tus llaves que hayas configurado y la opción para borrarlas.

Si haces clic en el botón <i class="material-icons">add</i>, podrás añadir una nueva llave de seguridad. En el diálogo que aparece, deberás introducir el nombre que quieras dar a la llave de seguridad (para poder reconocerla después en la tabla, en caso que tengas varias) y hacer clic en el botón **Registrar**. Luego, el navegador te guiará por el proceso de registro, y una vez terminado, la llave quedará registrada.

## ¿Cómo se quita la verificación en dos pasos?
Para quitar la verificación en dos pasos, ve a la sección **Seguridad** y haz clic en **Desactivar verificación en 2 pasos**. Por motivos de seguridad deberás introducir tu contraseña para realizar esta acción, y al hacer clic en **Desactivar** quedará desactivada. A partir de este punto ya podrás desinstalar la aplicación que genera códigos de tu móvil.

## No puedo iniciar sesión por culpa de la verificación en dos pasos. ¿Qué hago?
Si te has quedado bloqueado porque, por ejemplo, has perdido el móvil o has desinstalado la aplicación que genera códigos, puedes pedir al administrador del aplicativo que te desactive la verificación en dos pasos para poder iniciar sesión únicamente con la contraseña. Luego, puedes volver a configurar la verificación en dos pasos si lo deseas.
