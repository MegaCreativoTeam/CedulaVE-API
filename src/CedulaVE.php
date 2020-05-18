<?php

namespace MegaCreativo\API;

require __DIR__ . '/../vendor/autoload.php';

use Curl\Curl;

/**
 * CEDULA VE
 * 
 * @package     CedulaVE
 * @author      Brayan Rincón <brayan262@gmail.com>
 * @version     1.1.1
 * @copyright   Copyright (c) 2017-2020, Brayan Rincon - MEGA CREATIVO
 * @link        https://github.com/brayan2rincon
 * @link        https://megacreativo.com
 * @license     MIT
 */

/**
 * Esta Clase que permite consultar datos personales de habitantes de Venezuela
 * como nombre completo, estado y municipio de su centro de votación.  
 * estos datos son suministrados por la base de datos del CNE.
 *
 * @package    CedulaVE
 * @author     Brayan Rincon <brayan262@gmail.com>
 */
abstract class CedulaVE 
{
    const URL = "http://www.cne.gov.ve/web/registro_electoral/ce.php?nacionalidad=%s&cedula=%s";

    /**
	 * The version.
	 *
	 * @since   1.1.1
	 * @access  private
	 * @var     string    $version    The current version.
	 */
    private static $version = '1.1.1';

    /**
	 * The Author.
	 *
	 * @since   1.1.1
	 * @access  private
	 * @var     string    $author     The current author.
	 */
    private static $author = 'brayan2rincon';

    /**
	 * The Website.
	 *
	 * @since   1.1.1
	 * @access  private
	 * @var     string    $author     The current author.
	 */
    private static $api = 'https://api.megacreativo.com/public/cedula-ve/v1';

    /**
     * Obtiene los datos del ciudadano.
     * 
     * @since   1.1.1
     *
     * @uses    queryCNE()
     * @uses    existData()
     * @uses    processAndCleanData()
     * @uses    formatterName()
     * @uses    response()
     * 
     * @param   string      $nac        Tipo de Nacionalidad [V|E]. Cualquier otro valor producirá un Error 301
     * @param   string      $cedula     Número de Cédula de Identidad a consultar
     * @param   boolean     $json       (Opcional) Si es true devolver JSON como respuesta, en caso contrario devuelve un ARRAY. Valor por defecto TRUE
     * @param   boolean     $pretty     (Opcional) Se devuelve un JSON, este parametro establece si se aplica JSON_PRETTY_PRINT. Valor por defecto FALSE
     * 
     * @return  void
     */
    public static function info(string $nac, string $cedula, bool $json = true, bool $pretty = false)
    {
        // Validaciones

        if($nac != 'V' and $nac != 'E'){
            return self::errors(1, $json, $pretty);
        } // endif

        if(empty($cedula)){
            return self::errors(2, $json, $pretty);
        } // endif

        if(!is_numeric($cedula)){
            return self::errors(3, $json, $pretty);
        } // endif

        // end Validaciones


        $content   = self::queryCNE($nac, $cedula);

        if($content['error'] == true){
            return self::errors($content['code'], $json, $pretty);
        } // endif

        if (self::existData($content['message'])) { // Se encontraron los datos

            $content = self::processAndCleanData($content['message']);

            $fullname   = self::formatterName($content[2]);

            $response = [
                'status' => 200,
                'version' => self::$version,
                'api' => self::$api,
            	'data' => [
	            	'nac'           => $nac,
	            	'dni'           => $cedula,
	            	'name'          => $fullname['name'],
                    'lastname'      => $fullname['lastname'],
                    'fullname'      => $content[2],
	            	'state'         => $content[3],
	            	'municipality'  => $content[4],
	            	'parish'        => $content[5],
                    'voting'        => $content[6],
                    'address'       => $content[7],                    
                ]
            ]; // end response

        } 
        else { // No se encontraron los datos

            return self::errors(4, $json, $pretty);

        } // endif
        
        return self::response($response, $json, $pretty);

    } // end function

    /**
     * 
     * @since   1.1.0
     * @access  private
     * 
     * @param   string      $nac        Nacionalidad de la persona
     * @param   string      $dni        Cédula de identidad     
     * @return  string
     */
    private static function queryCNE(string $nac, string $dni) : array
    {
        $url = sprintf(self::URL, $nac, $dni);
        $curl = new Curl();
        $curl->get($url);

        if ($curl->error) {
            $response = [
                'error' => true,
                'code' => $curl->errorCode,
                'message' => $curl->errorMessage
            ];
        } else {
            $response = [
                'error' => false,
                'message' => strip_tags($curl->response)
            ];
        }

        return $response;
        
    } // end function

    /**
     * Procesa y limpia los datos
     *
     * @param   string      $nac        Nacionalidad de la persona
     * @param   string      $dni        Cédula de identidad
     * @return  string
     */
    private static function processAndCleanData(string $content) : array
    {
        $patterns   = ['Cédula:', 'Nombre:', 'Estado:', 'Municipio:', 'Parroquia:', 'Centro:', 'Dirección:', 'SERVICIO ELECTORAL', 'Registro ElectoralCorte'];           
        $patterns   = str_ireplace($patterns, '|', self::clean($content));
        $patterns   = trim($patterns);
        
        $response   = array_map('self::clean', explode("|", $patterns));

        return $response;

    } // end function
    
    /**
     * Verifica si existen datos de la persona
     *
     * @param   string      $content
     * @return  boolean
     */
    private static function existData(string $content) : bool
    {
        $pattern_1  = 'REGISTRO ELECTORAL';
        $position_1 = stripos($content, $pattern_1);

        $pattern_2  = 'ADVERTENCIA';
        $position_2 = stripos($content, $pattern_2);

        if ( $position_1 AND $position_2 == FALSE ) {
            return true;
        }

        return false;

    } // end function

    /**
     * Formatea el nombre de la persona según la cantidad de palabras que lo componen.
	 * 
     * @since   1.1.0
     * @access  private
     * @uses    clean()
     * 
     * @param   string      $text
     * 
     * @return  array
     */
    private static function formatterName( $text ) : array
    {
        $text = self::clean($text);
        $text = explode(' ', $text);
        switch ( count($text) ) {

            // Un nombre y un apellido
            case 2:
                $name = $text[0] ;
                $lastname = $text[1];
            break;

            // Dos nombre y un apellido
            case 3:
                $name = $text[0] .' '. $text[1];
                $lastname = $text[2];
            break;
            
            // Dos nombre y dos apellidos
            case 4:
               $name = $text[0] .' '. $text[1];
               $lastname = $text[2] .' '. $text[3];
            break;

            default:
                $count = count($text);
                $mitad = round($count/2);
                $name = $lastname = '';
                for ($i=0; $i < $mitad; $i++) { 
                    $name .= $text[$i] .' ';
                }
                for ($i=$mitad; $i < $count; $i++) { 
                    $lastname .= $text[$i].' ';
                }
            break;
        }

        return [
            'name' => trim($name),
            'lastname' =>trim($lastname)
        ];

    } // end function

    /**
     * Elimina saltos de líneas y tabulaciones.
     * 
	 * @since   1.1.0
     * @access  private
     * 
     * @param   string      $value
     * 
     * @return  string
     */
    private static function clean( $value ) : string
    {
        $patterns = array('\n', '\t');
        $r = trim(str_ireplace($patterns, ' ', $value));
        return str_ireplace("\r", "", str_replace("\n", "", str_replace("\t", "", $r)));
        
    } // end function


    /**
     * Tratamiento de la respuesta en formato JSON.
     * 
	 * @since   1.1.1
     * @access  private
     * 
     * @param   array       $content
     * @param   array       $json
     * @param   bool        $pretty
     * 
     * @return  string
     */
    private static function response(array $content, bool $json = true, bool $pretty = false)
    {
        if( $json === true ) {

            header('Content-Type: application/json; charset=utf8');

            if($pretty == true) {
                return json_encode($content, JSON_PRETTY_PRINT);
            }
    
            return json_encode($content);
        }
        else {
            return $content;
        }

    }// end function


    /**
     * Tratamiento de errores.
     * 
	 * @since   1.1.1
     * @access  private
     * @uses    response()
     * 
     * @param   array       $content
     * @param   array       $json
     * @param   bool        $pretty
     * 
     * @return  string
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

        $response = [
            'status' => $code,
            'version' => self::$version,
            'api' => self::$api,
            'data' => $message
        ];// end response

        return self::response($response, $json, $pretty);

    }// end function

}