=== Tigo Money Woo ===
Contributors: saulmoralespa
Tags: commerce, e-commerce, commerce, wordpress ecommerce, store, sales, sell, shop, shopping, cart, checkout, configurable, tigo, money, paraguay
Requires at least: 4.8.2
Tested up to: 4.8.2
Requires PHP: 5.6.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pago simple con tigo, solo necesita una linea telefónica como billetera electrónica.

== Description ==

Tigo Money es una plataforma que permite medio de pago simple,práctico y seguro.

Ahora su integración con Woocommerce es tan sencilla como instalar y empezar a recibir pagos de sus usuarios.


== Installation ==


1. Descarga el plugin
2. Ingresa al administrador de tu wordPress.
3. Ingresa a Plugins / Añadir-Nuevo / Subir-Plugin.
4. Busca el plugin descargado en tu equipo y súbelo como cualquier otro archivo.
5. Después de instalar el .zip lo puedes ver en la lista de plugins instalados , puedes activarlo o desactivarlo.
6. Para configurar el plugin debes ir a: WooCommerce / Ajustes / Finalizar Compra y Ubica la pestaña Tigo Money Woo.
7. Configura el plugin ingresando Cuenta de agente,PIN de agente,client_id y client_secret
8. Guarde Cambios, si no ha realizado correctamente la configuración se le mostrara un aviso, preste atención a este.
9. El nombre del comercio establecido en su cuenta de Tigo Money debe concordar con el nombre del sitio web para esto vaya dentro del panel de wordpress al menú ajustes / Generales y Título del sitio.


== Frequently Asked Questions ==

= ¿ Países en los cuales esta disponible su uso ? =

Actualmente solo para Paraguay, implementar nuevo país

= ¿ Es requerido que use un certificado ssl ? =

No.Pero es recomendable que lo considere usar ya que es revelante para google.

= ¿ Como pruebo su funcionamiento ? =

Debe ir a las configuraciones de Woocommerce / finalizar compra / Tigo Money Woo y establecer el ambiente a pruebas, recuerde que también debe cambiar cuenta de agente (número de linea de tigo usada como billetera electrónica), client_id y client_secret. Lea la documentación proporcionada por Tigo Money punto 5.Billeteras electrónicas.

= ¿ Que debo tener en cuenta para producción ? =

Requiere firma de contrato con Mobile Cash S.A y tener habilitada una línea telefónica Tigo la cual se usara como billetera electrónica.

= ¿ Cómo informa de errores ? =

Simple, cuando realiza cambios se le informara si hay un error. Durante el proceso de compra le saldra un aviso, errores frecuentes son por client_id y client_secret no corresponden al ambiente.

= ¿ Qué más debo tener en cuenta, que no me hayas dicho ? =

1. Tener en cuenta que el nombre de agente establecido en su cuenta de Tigo Money concuerde con el nombre del sitio, para verificar ir dentro del panel de adminstracin de wordpress al menú ajustes / generales / Título del sitio.
2. Las urls tanto redirectUri y callbackUr deben ser una sola, ejemplo https://www.ejemplo.com debe concidir con la que proporciona usar en Tigo Money y la del home de su instalación de wordpress para cerciorarse vaya al panel de administracion / ajustes / generales / Dirección del sitio (URL).
3. Recuerde que para ambiente de pruebas debe contar con dos lineas telefónicas una sera el agente de la cuenta y la otra la del suscriptor.


== Screenshots ==

1. Configuración medio pago corresponde a screenshot-1.png

== Changelog ==

= 1.0.0 =
* Initial stable release
= 1.0.1 =
* added payment fields
