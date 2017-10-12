# Tigo Money Woo #
**Contributors:**       Saul Morales Pacheco  
**Tags:**               tigo money, woocommerce, ecommerce, Paraguay  
**Author URI:**         https://saulmoralespa.com  
**Plugin URI:**         https://saulmoralespa.github.io/tigo-money-woo/  
**Requires at least:**  4.8.2  
**Tested up to:**       4.8.2  
**Stable tag:**         1.0.2  
**License:**            GPL-3.0+  
**License URI:**        http://www.gnu.org/licenses/gpl-3.0.html  


# Tigo Money para Woocommerce

## Descripción ##
Tigo  Money  es  una  plataforma  que permite medio de pago simple,práctico y seguro.

**Para cualquier pregunta o problema, ponerse en contacto [info@saulmoralespa.com](mailto:info@saulmoralespa.com)**

## Tabla de contenido

* [Requisitos](#requisitos)
* [Instalación](#instalación)
* [preguntas frecuentes](#preguntas-frecuentes)

## Requisitos ##

* Tener una cuenta de Tigo Money [Tigo Money Paraguay](https://money.tigo.com.py/empresas/boton-de-pago)
* Tener instalado WordPress y WooCommerce actualizado en la versión 3.0 o mayor.
* PHP >= 5.6.0

## Instalación ##

1. [Descarga el plugin](https://github.com/saulmoralespa/tigo-money-woo/archive/master.zip)
2. Ingresa al administrador de tu wordPress.
3. Ingresa a Plugins / Añadir-Nuevo / Subir-Plugin. 
4. Busca el plugin descargado en tu equipo y súbelo como cualquier otro archivo.
5. Después de instalar el .zip lo puedes ver en la lista de plugins instalados , puedes activarlo o desactivarlo.
6. Para configurar el plugin debes ir a: WooCommerce / Ajustes / Finalizar Compra y Ubica la pestaña Tigo Money Woo.
7. Configura el plugin ingresando  **Cuenta de agente**,**PIN de agente**,**client_id** y **client_secret**
8. Guarde Cambios, si no ha realizado correctamente la configuración se le mostrara un aviso, preste atención a este.
9. El nombre del comercio establecido en su cuenta de Tigo Money debe concordar con el nombre del sitio web para esto vaya dentro del panel de wordpress al menú ajustes / Generales y Título del sitio.

## Preguntas frecuentes ##

### ¿ Países en los cuales esta disponible su uso ? ###

Actualmente solo para Paraguay, [implementar nuevo país](mailto:info@saulmoralespa.com)

### ¿ Es requerido que use un certificado ssl ? ###

No.Pero es recomendable que lo considere usar ya que es revelante para google.

### ¿ Como pruebo su funcionamiento ? ###

Debe ir a las configuraciones de Woocommerce / finalizar compra / Tigo Money Woo y establecer el ambiente a pruebas, recuerde que también debe cambiar **cuenta de agente (número de linea de tigo usada como billetera electrónica), client_id y client_secret**. Lea la documentación proporcionada por Tigo Money punto 5.Billeteras electrónicas.

### ¿ Que debo tener en cuenta para producción ? ###

Requiere firma de contrato con Mobile Cash S.A y tener habilitada una línea telefónica Tigo la cual se usara como billetera electrónica.

###  ¿ Cómo informa de errores ? ###

Simple, cuando realiza cambios se le informara si hay un error. Durante el proceso de compra le saldra un aviso, errores frecuentes son por  **client_id y client_secret** no corresponden al ambiente.

### ¿ Qué más debo tener en cuenta, que no me hayas dicho ? ####

* Tener en cuenta que el nombre de agente establecido en su cuenta de Tigo Money concuerde con el nombre del sitio, para verificar ir dentro del panel de adminstracin de wordpress al menú ajustes / generales / Título del sitio.
* Las urls tanto redirectUri y callbackUr deben ser una sola, ejemplo https://www.ejemplo.com debe concidir con la que proporciona usar en Tigo Money y la del home de su instalación de wordpress para cerciorarse vaya al panel de administracion / ajustes / generales / Dirección del sitio (URL). 