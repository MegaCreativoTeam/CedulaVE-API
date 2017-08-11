<?php

/**
 * CedulaVE
 * 
 * Clase que permite consultar datos personales de habitantes de Venezuela
 * como nombre completo, estado y municipio, estos datos son suministrados 
 * por la base de datos del CNE.
 * 
 * Class that allows to consult the personal data of the inhabitants of 
 * Venezuela like complete state, state and municipality, these data are 
 * supplied by the database of the CNE.
 * 
 * @package API
 * @version 1.0.0
 * @author Brayan Rincon <brayan262@gmail.com>
 * @copyright 2017 Mega Creativo
 * @license MIT
 */
abstract class CedulaVE 
{
    const
        URL = "http://www.cne.gov.ve/web/registro_electoral/ce.php?nacionalidad=:nacionalidad&cedula=:cedula";

    /**
     * Obtiene los datos del ciudadano
     * 
     * Get the citizen's data
     * 
     * @param string $nacionalidad Nacionalidad [V|E]
     * @param string $cedula Número de Cédula de Identidad
     * @return void
     */
    public static function get($nacionalidad, $cedula)
    {
        $url = str_replace([':nacionalidad',':cedula'], [$nacionalidad, $cedula], self::URL);
        
        $resource = self::getContent($url);
        $text = strip_tags($resource);

        $find1 = 'REGISTRO ELECTORAL';
        $pos1 = stripos($text, $find1);

        $find2 = 'ADVERTENCIA';
        $pos2 = stripos($text, $find2);

        $find3 = 'FALLECIDO ';
        $pos3 = stripos($text, $find2);

        if ( $pos1 AND $pos2 == FALSE AND $pos3 != FALSE ) {
            
            $patterns   = ['Cédula:', 'Nombre:', 'Estado:', 'Municipio:', 'Parroquia:', 'Centro:', 'Dirección:', 'SERVICIO ELECTORAL'];           
            $patterns   = trim(str_ireplace($patterns, '|', self::clean($text)));
            $resource   = array_map('self::clean', explode("|", $patterns));
            $nombre     = self::formatearNombre($resource[2]);

            $response = [
            	'status' => 200,
            	'response' => [
	            	  'nacionalidad' => $nacionalidad
	            	, 'cedula' => $cedula
	            	, 'nombres' => $nombre[0]
                    , 'apellidos' => $nombre[1]
                    , 'completo' => $resource[2]
	            	, 'mayor' => TRUE
	            	, 'estado' => $resource[3]
	            	, 'municipio' => $resource[4]
	            	, 'parroquia' => $resource[5]
	            	, 'direccion' => $resource[7]
                ]
            ];        
        } 
        else
        {
            $response = [
            	'status' => 404,
            	'response' => [
	            	  'nacionalidad' => $nacionalidad
	            	, 'cedula' => $cedula
                    , 'inscrito' => FALSE
	            ]
            ];
        }
        return $response;
    }


    /**
     *
     * @param string $url
     * @return string
     */
    private static function getContent($url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if (curl_exec($curl) === false)
        {
            echo 'Curl error: ' . curl_error($curl);
        }
        else
        {
            $return = curl_exec($curl);
        }
        curl_close($curl);

        return $return;
    }


    /**
     * Formatea el nombre de la persona según la cantidad de palabras que lo componen.
     * 
     * Format the name of the person according to the number of words that compose it.
     *
     * @param string $text
     * @return string
     */
    private static function formatearNombre( $text )
    {
        $text = self::clean($text);
        $text = explode(' ', $text);
        switch ( count($text) ) {

            //Un nombre y un apellido
            case 2:
                $nombres = $text[0] ;
                $apellidos = $text[1];
            break;

            //Dos nombre y un apellido
            case 3:
                $nombres = $text[0] .' '. $text[1];
                $apellidos = $text[2];
            break;
            
            //dos nombre y dos apellidos
            case 4:
               $nombres = $text[0] .' '. $text[1];
               $apellidos = $text[2] .' '. $text[3];
            break;

            default:
                $count = count($text);
                $mitad = round($count/2);
                $nombres = $apellidos = '';
                for ($i=0; $i < $mitad; $i++) { 
                    $nombres .= $text[$i] .' ';
                }
                for ($i=$mitad; $i < $count; $i++) { 
                    $apellidos .= $text[$i].' ';
                }
            break;
        }

        return [trim($nombres), trim($apellidos)];

    }


    /**
     * Elimina saltos de líneas y tabulaciones.
     * 
     * Eliminates line breaks and tab stops.
     *
     * @param string $value
     * @return string
     */
    private static function clean($value)
    {
        $patterns = array('\n', '\t');
        $r = trim(str_ireplace($patterns, ' ', $value));
        return str_ireplace("\r", "", str_replace("\n", "", str_replace("\t", "", $r)));
    }

}
