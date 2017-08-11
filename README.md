# CedulaVE API

*CedulaVE API* es un Script escrito en *PHP* para consultar datos personales de habitantes de Venezuela inscritos en el *CNE* mediante su Cédula de Identidad.

## Contribución

¡Gracias por considerar contribuir al proyecto de Creative. Envíe un correo electrónico a Brayan Rincon a *brincon@megacreativo.com* para considerar su contribución.

## Instalación
La forma más fácil de instalar CedulaVE API es a través de [Composer](https://github.com/composer/composer)

```php
composer require megacreativo/cedulave-api
```

## Ejemplo PHP

### Consultar la API

```php
CedulaVE::get('V', '12345678');
```

### Respuesta en formato JSON

```javascript
{
    'status' : 200,
    'response' :
    {
        'nacionalidad' : 'V'
        'cedula' : '12345678',
        'nombres' : 'NOMBRE1 NOMBRE2',
        'apellidos' : 'APELLIDO1 APELLIDO2',
        'completo' : 'NOMBRE1 NOMBRE2 APELLIDO1 APELLIDO2',
        'mayor' : true,
        'estado' : 'ESTADO',
        'municipio' : 'MUNICIPIO',
        'parroquia' : 'PARROQUIA',
        'direccion' : 'VENEZUELA'
    }
}
```

## Licencia

*CedulaVE API* es un software de código abierto con licencia bajo [MIT license](http://opensource.org/licenses/MIT).
