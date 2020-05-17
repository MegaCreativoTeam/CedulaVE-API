<?php

namespace MegaCreativo\API;

require __DIR__ . '/../vendor/autoload.php';

use Curl\Curl;

/**
 * CEDULA VE
 * 
 * @package     CedulaVE
 * @author      Brayan Rincón <brayan262@gmail.com>
 * @version     1.1.0
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
	 * @since   1.1.0
	 * @access  private
	 * @var     string    $version    The current version.
	 */
    private static $version = '1.1.0';

    /**
	 * The Author.
	 *
	 * @since   1.1.0
	 * @access  private
	 * @var     string    $author     The current author.
	 */
    private static $author = 'brayan2rincon';

    /**
	 * The Website.
	 *
	 * @since   1.1.0
	 * @access  private
	 * @var     string    $author     The current author.
	 */
    private static $website = 'http://megacreativo.com';

    /**
     * Obtiene los datos del ciudadano.
     * 
     * @since   1.1.0
     *
     * @uses    queryCNE()
     * @uses    existData()
     * @uses    processAndCleanData()
     * @uses    formatterName()
     * 
     * @param   string      $nac        Tipo de Nacionalidad [V|E]
     * @param   string      $cedula     Número de Cédula de Identidad a consultar
     * @param   boolean     $json       Si es true devolver JSON como respuesta, en caso contrario devuelve un array 
     * @param   boolean     $pretty     Se devuelve un JSON, este parametro establece si se aplica JSON_PRETTY_PRINT
     * 
     * @return  void
     */
    public static function info(string $nac, string $cedula, bool $json = true, bool $pretty = false)
    {
        $content   = self::queryCNE($nac, $cedula);

        if ( self::existData($content) ) { // Se encontraron los datos

            $content = self::processAndCleanData($content);

            $fullname   = self::formatterName($content[2]);

            $response = [
                'status' => 200,
                'version' => '1.0.1',
                'website' => self::$website,
            	'data' => [
	            	'nac'            => $nac,
	            	'dni'            => $cedula,
	            	'name'           => $fullname['name'],
                    'lastname'       => $fullname['lastname'],
                    'fullname'       => $content[2],
	            	'isadult'        => true,
	            	'state'          => $content[3],
	            	'municipality'   => $content[4],
	            	'parish'         => $content[5],
                    'voting'         => $content[6],
                    'address_voting' => $content[7],                    
                ]
            ];

        } 
        else { // No se encontraron los datos

            $response = [
                'status' => 404,
                "version" => "1.0.1",
                "autor" => "brayan2rincon",
            	'data' => [
	                'nac' => $nac,
	            	'cedula' => $cedula,
                    'inscrito' => FALSE,
	            ]
            ];

        }

        if( $json === true ) {
            header('Content-Type: application/json; charset=utf8');
            return json_encode($response, JSON_PRETTY_PRINT);
        }
        else {
            return $response;
        }

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
    private static function queryCNE(string $nac, string $dni) : string
    {
        $url = sprintf(self::URL, $nac, $dni);
        $curl = new Curl();
        $curl->get($url);

        if ($curl->error) {
            $response = $curl->errorCode . ': ' . $curl->errorMessage;
        } else {
            $response = strip_tags($curl->response);
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

}