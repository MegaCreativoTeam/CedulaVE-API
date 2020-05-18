# 👋 CedulaVE API

**CedulaVE API** es un script escrito en *PHP* para consultar datos personales de habitantes de Venezuela inscritos en el *CNE* mediante su Cédula de Identidad.

## 🤝 Contibución

[![ko-fi][ico-kofi]][link-kofi]

Contribuciones, problemas y solicitudes de funciones son bienvenidos. Siéntase libre de consultar los [issues](https://github.com/megacreativo/CedulaVE-API/issues) si quieres contribuir.

## :minidisc: Instalación

La forma más fácil de instalar CedulaVE API es a través de [Composer](https://github.com/composer/composer)

```php
composer require megacreativo/cedulave-api
```

## :scroll: Ejemplo PHP

El método info está disponible para ejecutar la consula, las respuesta se obtiene por defecto en formato JSON

### Consultar la API

Para ver más ejemplos valla a [examples]()

```php
/**
 * Estructura de parametros
 * 
 * @param string   $nac     Tipo de Nacionalidad. Valores permitidos [V|E]. Cualquier otro valor producirá un Error 301
 * @param string   $cedula  Número de Cédula de Identidad a consultar
 * @param boolean  $json    (Opcional) Si es true devolver JSON como respuesta, en caso contrario devuelve un ARRAY. Valor por defecto TRUE
 * @param boolean  $pretty  (Opcional) Se devuelve un JSON, este parametro establece si se aplica JSON_PRETTY_PRINT. Valor por defecto FALSE
 */
function info(string $nac, string $cedula, bool $json = true, bool $pretty = false)


/**
 * Ejemplos de uso
 **/
// Retorna un JSON
CedulaVE::info('V', '12345678');

// Retorna un Array
CedulaVE::info('V', '12345678', false);

// Retorna un JSON formateado 
vCedulaVE::info('V', '12345678', true, true);
```

### Respuesta exitosa

```json
{
    "status": 200,
    "version": "1.1.1",
    "website": "https://api.megacreativo.com/public/cedula-ve/v1",
    "response":
    {
        "nac": "V", // Nacionalidad. [V|E]
        "dni": "12345678", // Cédula de identidad
        "name": "Jhon Alfred", // Primer y segundo nombre
        "lastname": "Doe Law", // Primer y segundo apellido
        "fullname": "Jhon Alfred Doe Law", // Nombre completo
        "state": "Estado", // Estado donde se encuentra el Centro de votación
        "municipality": "Municipio", // Municipio del Centro de votación
        "parish": "Parroquia", // Parroquia del Centro de votación
        "voting": "Centro de votación", // Nombre del Centro de votación        
        "address": "Direccion" // Dirección del Centro de votación
    }
}
```

```php
Array
(
    [status] => 200
    [version] => 1.1.1
    [api] => https://api.megacreativo.com/public/cedula-ve/v1
    [data] => Array
        (
            [nac] => V
            [dni] => 12345678
            [name] => JHON ALFRED
            [lastname] => DOE LAW
            [fullname] => JHON ALFRED DOE LAW
            [state] => ESTADO
            [municipality] => MUNICIPIO
            [parish] => PARROQUIA
            [voting] => CENTRO DE VOTACION
            [address] => DIRECCION DEL CENTRO
        )

)
```

### Respuestas de error

**Error 404** La cédula consultada no está inscrita en el CNE

```json
{
    "status": 404,
    "version": "1.1.1",
    "api": "https://api.megacreativo.com/public/cedula-ve/v1",
    "data": {
        "code": 404,
        "message": "No se encontró la cédula de identidad"
    }
}
```

**Error 301** Los datos recibidos no son correctos, Error en la nacionalidad. Valores permitidos [V|E]

```json
{
    "status": 301,
    "version": "1.1.1",
    "api": "https://api.megacreativo.com/public/cedula-ve/v1",
    "data": {
        "code": 301,
        "message": "Los datos recibidos no son correctos, Error en la nacionalidad. Valores permitidos [V|E]"
    }
}
```

**Error 302** Los datos recibidos no son correctos. Se introdujo un caracter no numerico

```json
{
    "status": 302,
    "version": "1.1.1",
    "api": "https://api.megacreativo.com/public/cedula-ve/v1",
    "data": {
        "code": 302,
        "message": "Los datos recibidos no son correctos. Se introdujo un caracter no numerico"
    }
}
```

**Error 303** Los datos recibidos no son correctos. Se introdujo un caracter no numerico

```json
{
    "status": 303,
    "version": "1.1.1",
    "api": "https://api.megacreativo.com/public/cedula-ve/v1",
    "data": {
        "code": 303,
        "message": "Debe ingresar una cedula de indetidad válida. Sólo se permiten caracteres numéricos"
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
Copyright © 2018-2020. Hecho con ❤️ por Brayan Rincon y Mega Creativo [https://megacreativo.com](https://megacreativo.com)

[ico-kofi]: https://www.ko-fi.com/img/githubbutton_sm.svg
[link-kofi]: https://ko-fi.com/N4N21DSFZ
