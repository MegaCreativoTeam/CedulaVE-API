<?php

namespace MegaCreativo\API;

require __DIR__ . '/../vendor/autoload.php';

use Curl\Curl;

/**
 * CEDULA VE.
 * 
 * This Class that allows to consult personal data of inhabitants of Venezuela
 * as full name, state and municipality of your polling place.
 * These data are provided by the CNE database.
 * 
 * @author      Brayan Rincón <brayan.er.rincon@gmail.com>
 * @version     1.1.2
 * @copyright   Copyright (c) 2017-2020, Brayan Rincon - MEGA CREATIVO
 * @link        https://github.com/bracodev
 * @link        https://megacreativo.com
 * @license     MIT
 */
abstract class CedulaVE
{
    const URL = 'http://www.cne.gov.ve/web/registro_electoral/ce.php?nacionalidad=%s&cedula=%s';

    /**
     * The version.
     * @var string The current version.
     */
    private static $version = '1.1.2';

    /**
     * The Author.
     * @var string The current author.
     */
    private static $author = 'bracodev';

    /**
     * The Website.
     * @var string The current author.
     */
    private static $api = 'https://api.megacreativo.com/public/cedula-ve/v1';

    /**
     * Gets the person's data
     *
     * @since   1.1.1
     *
     * @uses    queryCNE()
     * @uses    existData()
     * @uses    processAndCleanData()
     * @uses    formatterName()
     * @uses    response()
     *
     * @param string  $nac      Type of Nationality [V|E]. Any other value will produce an Error 301
     * @param string  $cedula   Identity Card number to consult
     * @param bool    $json     (Optional) Return JSON as the response if true, otherwise return an ARRAY. Default value TRUE
     * @param bool    $pretty   (Optional) A JSON is returned, this parameter sets whether JSON_PRETTY_PRINT is applied. Default value FALSE
     *
     * @return void
     */
    public static function get(string $nac, string $cedula)
    {
        return self::info($nac, $cedula, false, false);
    }

    /**
     * Gets the person data and returns it as a JSON.
     *
     * @uses    queryCNE()
     * @uses    existData()
     * @uses    processAndCleanData()
     * @uses    formatterName()
     * @uses    response()
     *
     * @param string  $nac      Type of Nationality [V|E]. Any other value will produce an Error 301
     * @param string  $cedula   Identity Card number to consult
     * @param bool    $pretty   (Optional) A JSON is returned, this parameter sets whether JSON_PRETTY_PRINT is applied. Default value FALSE
     *
     * @return void
     */
    public static function json(string $nac, string $cedula, bool $pretty = false)
    {
        return self::info($nac, $cedula, true, $pretty);
    }


    /**
     * Gets the person's data.
     *
     * @uses    queryCNE()
     * @uses    existData()
     * @uses    processAndCleanData()
     * @uses    formatterName()
     * @uses    response()
     *
     * @param string  $nac      Type of Nationality [V|E]. Any other value will produce an Error 301
     * @param string  $cedula   Identity Card number to consult
     * @param bool    $json     (Optional) Return JSON as the response if true, otherwise return an ARRAY. Default value TRUE
     * @param bool    $pretty   (Optional) A JSON is returned, this parameter sets whether JSON_PRETTY_PRINT is applied. Default value FALSE
     * @return void
     */
    public static function info(string $nac, string $cedula, bool $json = true, bool $pretty = false)
    {
        // begin valdiations

        if ($nac !== 'V' and $nac !== 'E') {
            return self::errors(1, $json, $pretty);
        }

        if (empty($cedula)) {
            return self::errors(2, $json, $pretty);
        }

        if (!is_numeric($cedula)) {
            return self::errors(3, $json, $pretty);
        }

        // end valdiations

        $content = self::queryCNE($nac, $cedula);

        if ($content['error'] === true) {
            return self::errors($content['code'], $json, $pretty);
        }

        if (self::existData($content['message'])) {// Data not found

            $content = self::processAndCleanData($content['message']);

            $fullname = self::formatterName($content[2]);

            $response = [
                'status' => 200,
                'version' => self::$version,
                'api' => self::$api,
                'data' => [
                    'nac' => $nac,
                    'dni' => $cedula,
                    'name' => $fullname['name'],
                    'lastname' => $fullname['lastname'],
                    'fullname' => $content[2],
                    'state' => $content[3],
                    'municipality' => $content[4],
                    'parish' => $content[5],
                    'voting' => $content[6],
                    'address' => $content[7],
                ],
            ]; // end response
            
        } else { // Data not found

            return self::errors(4, $json, $pretty);
        } // endif

        return self::response($response, $json, $pretty);
    }

    /**
     *
     * @param string $nac Nationality of the person
     * @param string $dni Identity card
     *
     * @return string
     */
    private static function queryCNE(string $nac, string $dni): array
    {
        $url = sprintf(self::URL, $nac, $dni);
        $curl = new Curl();
        $curl->get($url);

        if ($curl->error) {
            $response = array(
                'error' => true,
                'code' => $curl->errorCode,
                'message' => $curl->errorMessage,
            );
        } else {
            $response = array(
                'error' => false,
                'message' => strip_tags($curl->response),
            );
        }

        return $response;
    }

    /**
     * Process and clean the data
     *
     * @param string $content 
     *
     * @return array
     */
    private static function processAndCleanData(string $content): array
    {
        $patterns = array('Cédula:', 'Nombre:', 'Estado:', 'Municipio:', 'Parroquia:', 'Centro:', 'Dirección:', 'SERVICIO ELECTORAL', 'Registro ElectoralCorte');
        $patterns = str_ireplace($patterns, '|', self::clean($content));
        $patterns = trim($patterns);

        $response = array_map('self::clean', explode('|', $patterns));

        return $response;
    }

    /**
     * Check if the person's data exists.
     *
     * @param string $content
     *
     * @return bool
     */
    private static function existData(string $content): bool
    {
        $pattern_1 = 'REGISTRO ELECTORAL';
        $position_1 = stripos($content, $pattern_1);

        $pattern_2 = 'ADVERTENCIA';
        $position_2 = stripos($content, $pattern_2);

        if ($position_1 and $position_2 === false) {
            return true;
        }

        return false;
    }

    /**
     * Format the person's name according to the number of words in it.
     * 
     * @uses    clean()
     *
     * @param string $text
     *
     * @return array
     */
    private static function formatterName($text): array
    {
        $text = self::clean($text);
        $text = explode(' ', $text);
        switch (count($text)) {

                // Un nombre y un apellido
            case 2:
                $name = $text[0];
                $lastname = $text[1];
                break;

                // Dos nombre y un apellido
            case 3:
                $name = $text[0] . ' ' . $text[1];
                $lastname = $text[2];
                break;

                // Dos nombre y dos apellidos
            case 4:
                $name = $text[0] . ' ' . $text[1];
                $lastname = $text[2] . ' ' . $text[3];
                break;

            default:
                $count = count($text);
                $mitad = round($count / 2);
                $name = $lastname = '';
                for ($i = 0; $i < $mitad; $i++) {
                    $name .= $text[$i] . ' ';
                }
                for ($i = $mitad; $i < $count; $i++) {
                    $lastname .= $text[$i] . ' ';
                }
                break;
        }

        return array(
            'name' => trim($name),
            'lastname' => trim($lastname),
        );
    }

    /**
     * Remove line breaks and tabs.
     * 
     * @param string $value
     *
     * @return string
     */
    private static function clean($value): string
    {
        $patterns = array('\n', '\t');
        $r = trim(str_ireplace($patterns, ' ', $value));

        return str_ireplace("\r", '', str_replace("\n", '', str_replace("\t", '', $r)));
    }

    /**
     * Treatment of the response in JSON format.
     *
     * @param array $content
     * @param array $json
     * @param bool  $pretty
     *
     * @return string
     */
    private static function response(array $content, bool $json = true, bool $pretty = false)
    {
        if ($json === true) {
            header('Content-Type: application/json; charset=utf8');

            if ($pretty === true) {
                return json_encode($content, JSON_PRETTY_PRINT);
            }

            return json_encode($content);
        } else {
            return $content;
        }
    }

    /**
     * Error handling
     *
     * @uses    response()
     *
     * @param array $content
     * @param array $json
     * @param bool  $pretty
     *
     * @return string
     */
    private static function errors(int $code, bool $json = true, bool $pretty = false)
    {
        switch ($code) {
            case 1:
                //
                $code = '301';
                $message = 'Los datos recibidos no son correctos, Error en la nacionalidad. Valores permitidos [V|E]';
                break;

            case 2:
                //
                $code = '302';
                $message = 'Los datos recibidos no son correctos. Se introdujo un caracter no numerico';
                break;

            case 3:
                // Couldn't resolve host name: Could not resolve host: www.cn9e.gov.ve
                $code = '303';
                $message = 'Debe ingresar una cedula de indetidad válida. Sólo se permiten caracteres numéricos';
                break;

            case 4:
                $code = '404';
                $message = 'No se encontró la cédula de identidad';
                break;

            case 6:
                // Couldn't resolve host name: Could not resolve host: www.cn9e.gov.ve
                $code = '306';
                $message = 'El Host del CNE esta fuera de linea';
                break;

            default:
                $code = '500';
                $message = 'No se ha podido procesar la solicitud';
                break;
        }

        $response = array(
            'status' => $code,
            'version' => self::$version,
            'api' => self::$api,
            'data' => $message,
        );

        return self::response($response, $json, $pretty);
    }
}
