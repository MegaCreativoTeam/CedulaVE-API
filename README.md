# 👋 CedulaVE API

*CedulaVE API* es un script escrito en *PHP* para consultar datos personales de habitantes de Venezuela inscritos en el *CNE* mediante su Cédula de Identidad.

## 🤝 Contibución

[![ko-fi][ico-kofi]][link-kofi]

Contribuciones, problemas y solicitudes de funciones son bienvenidos. Siéntase libre de consultar los [issues](https://github.com/megacreativo/CedulaVE-API/issues) si quieres contribuir.

## 🔧 Instalación

La forma más fácil de instalar CedulaVE API es a través de [Composer](https://github.com/composer/composer)

```php
composer require megacreativo/cedulave-api
```

## Ejemplo PHP

El método info está disponible para ejecutar la consula, las respuesta se obtiene por defecto en formato JSON

### Consultar la API

```php
CedulaVE::info('V', '12345678');
```

### Respuesta en formato JSON

```json
{
    "status" : 200,
    "version": "1.0.1",
    "website": "http:\/\/megacreativo.com",
    "response" :
    {
        "nac" : "V",
        "dni" : "12345678",
        "name" : "Jhon Alfred",
        "lastname" : "Doe Law",
        "fullname" : "Jhon Alfred Doe Law",
        "isadult" : true,
        "state" : "Estado",
        "municipality" : "Municipio",
        "address" : "Parroquia",
        "direccion" : "Venezuela",
        "voting" : "Voting Center Address"
    }
}
```

## 👤 Author

**Brayan Rincón**
- Github: [@brayan2rincon][link-brayan2rincon]

## 📌 Versiones 

We use [SemVer](http://semver.org/) for versioninWg. For all available versions, look at the [tags in this repository](https://github.com/tu/proyecto/tags).

## 📝 Licencia

The software is distributed under the [MIT license](https://github.com/megacreativo/CedulaVE-API/master/LICENSE).
Copyright © 2018-2020. Hechi con ❤️ por Brayan Rincon y Mega Creativo [https://megacreativo.com][link-megacreativo]


<<<<<<< HEAD
[ico-kofi]: https://www.ko-fi.com/img/githubbutton_sm.svg
[link-kofi]: https://ko-fi.com/N4N21DSFZ
[link-megacreativo]: https://megacreativo.com
[link-brayan2rincon]: https://github.com/brayan2rincon
=======
*CedulaVE API* es un software de código abierto con licencia bajo [MIT license](http://opensource.org/licenses/MIT).
>>>>>>> 3027f98184226355b5bfe1a45d7a0e792d823600
