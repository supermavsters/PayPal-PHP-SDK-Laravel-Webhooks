# PayPal con Webhooks en Laravel 5.8 by Mavsters

![Home Image](https://raw.githubusercontent.com/wiki/paypal/PayPal-PHP-SDK/images/homepage.jpg)
Imagen tomada de: https://github.com/paypal/PayPal-PHP-SDK

## Nota aclaratoria
El siguiente repositorio es un proyecto personal que toma como ejemplo la libreria de [PayPal-PHP-SDK](https://paypal.github.io/PayPal-PHP-SDK/); la cual, intenta mejorar sus funciones con un código más facil para cualquier usuario, a su vez utilizando a su vez Webhooks siendo esto desarrollado en el Framework Laravel 5.8.

Igualmente, se apreciara que por parte del REST API del repositorio se estaran creando cada uno de los servicio de Aplicando y realizando cada uno de los servicios de [Webhooks con PHP](https://developer.paypal.com/docs/api/webhooks/v1/#webhooks_get-all) que proporciona la documentación de Paypal SDK PHP.

## Prerequisitos

   - PHP 5.3 o superior
   - [Laravel 5.8](https://laravel.com/docs/5.8)
   - [nodejs/npm](https://nodejs.org/en/download/) y/o [yarn](https://yarnpkg.com/en/docs/install#windows-stable)
   - [composer](https://getcomposer.org/download/)
   - [json](https://secure.php.net/manual/en/book.json.php) & [openssl](https://secure.php.net/manual/en/book.openssl.php) extensions must be enabled

## Getting Started

> **Tener presente que este proyecto seguira creciendo por ende cambiara ciertos detalles y se solucionaran Issues que se presenten.**

1. Descargar el repositorio [aquí](https://github.com/supermavster/PayPal-PHP-SDK-Laravel-Webhooks) o utilizando el comando: 
```Bash
git clone https://github.com/supermavster/PayPal-PHP-SDK-Laravel-Webhooks.git
```
en la consola de git o terminal preferida.

2. Copiar ```.env.example``` a ```.env```; utilizando el comando ```cp .env.example .env```

3. Genera e instala las librerias necesarias utilizando:
 ```Bash
 npm install --save
 composer install --no-dev --ignore-platform-reqs 
 ```

4. Genera e instala credenciales necesarias utilizando:
```Bash
php artisan key:generate
```

5. Añadir las credenciales en ```.env```

```
PAYPAL_MODE=sandbox
PAYPAL_BASE_URL=https://localhost/

PAYPAL_LIVE_PLAN_ID=
PAYPAL_LIVE_CLIENT_ID=
PAYPAL_LIVE_SECRET=

PAYPAL_SANDBOX_PLAN_ID=
PAYPAL_SANDBOX_CLIENT_ID=
PAYPAL_SANDBOX_SECRET=
PAYPAL_PAYMENT_SALE_COMPLETED_WEBHOOK_ID=
```

Detallare más adelante como sacar estos IDs.

6. Los servicios se encuentran en esta colección de Postman: [Paypal - Laravel (Mavsters)](https://documenter.getpostman.com/view/7100025/SVYuqGo9)

Durante el transcurso del tiempo actualizare este repositorio y su Readme. Por el momento quien este leyendo tengame un poco de paciencia, gracias.

## SDK Documentación

EL respositorio actual esta basado en: [https://github.com/paypal/PayPal-PHP-SDK](https://github.com/paypal/PayPal-PHP-SDK)

[PayPal-PHP-SDK Page](http://paypal.github.io/PayPal-PHP-SDK/) incluye toda la documentación relacionada con PHP SDK. Todo, desde SDK Wiki, hasta códigos de muestra, hasta lanzamientos. Aquí hay algunos enlaces rápidos para llegar más rápido.

* [PayPal-PHP-SDK Home Page](https://paypal.github.io/PayPal-PHP-SDK/)
* [Wiki](https://github.com/paypal/PayPal-PHP-SDK/wiki)
* [Ejemplos](https://paypal.github.io/PayPal-PHP-SDK/sample/)
* [Instalación](https://github.com/paypal/PayPal-PHP-SDK/wiki/Installation)
* [PayPal Developer Docs](https://developer.paypal.com/docs/)


