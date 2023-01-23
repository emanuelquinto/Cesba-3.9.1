<?php

namespace App\Http\Controllers;

use App\User;
use Collator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use League\CommonMark\Block\Parser\ListParser;
use PhpParser\Builder\Function_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Return_;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class UsersApiController extends Controller
{


    /**
     *Moodle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response  $response
     */

    /*
***********************************************************************************************************************
*Autor:Emanuel Quinto Zagal                                                                                           *
*Descripcion:Servicio para crear usuarios en la plataforma moodle , consumo de api de moodle (core_user_create_users).*
*Contenido:Codigo documentado para fines de cambios en versiones de la plataforma de cesba virtual version 4.0.       *
*fecha:02/10/2022                                                                                                     *
************************************************************************************************************************
*/
    public function CrearAlumnos()
    {
        
        $body = json_decode(file_get_contents('php://input'), true); #Obtenemos el request en un areglo
        for ($i = 0; $i <=count($body); $i++) {  #Recoremos el areglo del request
            $resultado = array(); 
            try { #Control de fallos en la operacion 
            header('Content-Type: application/json');#Especificamos la cavecera
            /*pasamos la Url del dominio con argumentos necesarios para que se realice la consulta al endpoint , es necesario agregar estos datos:
              1:dominio = 'http://campusvirtual.cesba-queretaro.edu.mx/'
              2:ruta del webservice origen= 'webservice/rest/server.php?'
              3:Token que tiene que generarte moodle para acceso = 'wstoken=817c06ac6196681f2d8f7db8dc6401ab&'
              4:Formato = 'moodlewsrestformat=json&'
              5:Funcion generada en moodle: = 'wsfunction=core_user_get_users'
              6: consumos de parametros de la funcion de moodle de consulta = 'criteria[0][key]=&criteria[0][value]='
            */
            $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]=".$body[$i]['Matricula'];
            $ch = curl_init(); #se unicia la seccion
            curl_setopt($ch, CURLOPT_URL, $endpoint); #se añade al Curl el endpoint
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);# busca ubicacion
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            $result = curl_exec($ch);# guarda el resultado de la consulta
            if (preg_match('~Location: (.*)~i', $result, $match)) { #Si la busqueda falla la localizacion y vuelve a consultar hasta verificar 
                $location = trim($match[1]);
                header('Content-Type: application/json');
                $endpoint = $location;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                $result = curl_exec($ch); #obtenemos el resultado final de la consulta 
                curl_close($ch);
                $array = json_decode($result, true); #Descodificamos el areglo del request de la consulta
                $matricula=''; #inicializamos la variable la cual se usara para comprovar si existe un alumno con la matricula o correo 
                foreach ($array['users'] as $mPersona) { #Recoremos el array de la consulta de la matricula
                   $matricula = $mPersona['username']; #asignamos a matricula la el resultado de la busqueda de matricula en la plataforma
                }
               
                if ($matricula == $body[$i]['Matricula']) { #Validamos si existe 
                    $collection = collect([
                        ['Error 404' => $body[$i]['Matricula']], 
                    ]);
                    $obj = new \stdClass;
                    $obj->Matricula =$body[$i]['Matricula']; 
                    $obj->Existente= "404 Not Found";
                    $resultado[] = $obj;
                    return $resultado; # Muestra las los usuarios existetes
                }else {
                    /***Abrimos comunicacion con moodle para  crear los nuevos usuarios en plataforma***/
                    #En el controlador principal esta creado una clase que es la contenedora de methodos y conexiones de moodle
                    #Se crea un Objecto de la clase MoodleRest y se pasa la Url como parametro , con dominio , token
                    $MoodleRest = new MoodleRest('http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php', '817c06ac6196681f2d8f7db8dc6401ab');
                    $new_group = array('users' => array( #Se crea un areglo con los campos que moodle exige para su conexion.
                        array(
                            'username'       => $body[$i]['Matricula'], #se inserta el usuario para loguearse obtenido del request 
                            'createpassword' => 1, #1 = Si para que moodle cree aleatoriamente contraseñas y mande la informacion por correo
                            'firstname' => $body[$i]['nombre'], #Se inserta el nombre 
                            'lastname' => $body[$i]['apellidos'], #Se inserta apellidos
                            'department' => strtolower($body[$i]['carrera']), #opcional la carrera
                            'email' => $body[$i]['Correo'], #Correo
                            'city' => 'Santiago de Queretaro', #Opcional la ciudad
                            'country' => 'MX' #Opcional el estado
                        ),
                    ));
                    $return = $MoodleRest->request('core_user_create_users', $new_group, MoodleRest::METHOD_GET); #se concatenan los datos a enviar
                     $payload = $MoodleRest->getUrl(); #Se abre la comunicacion 
                           
                    if ($payload != null) { #Evalua si hubo comunicacion
                        $obj = new \stdClass;
                        $obj->Matricula = $body[$i]['Matricula']; // muestra 8;
                        $obj->Create = "Create 200 Ok";
                        $resultado[] = $obj;
                         return  $resultado;
                    }
                }
              
        }
       }catch (\Exception $e) {   
        echo   $e->getMessage(), "\n";  # valida si hubo error
    }
    }
}

    /*
***********************************************************
* Consulta Por Matricula   (METHODO "GET")
***********************************************************
*/
    public  function getAlumnos($matricula)
    {
        $resultado = array();
        header('Content-Type: application/json');
        $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]=".$matricula;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (preg_match('~Location: (.*)~i', $result, $match)) {
            $location = trim($match[1]);
            header('Content-Type: application/json');
            $endpoint = $location;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            $result = curl_exec($ch);
            curl_close($ch);
            $array = json_decode($result, true);
            foreach ($array['users'] as $mPersona) {
                $fullname = $mPersona['fullname'];
                $matricula = $mPersona['username'];
                $email = $mPersona['email'];
                $department = $mPersona['department'];
            }
            if ($array != null) {
                $obj = new \stdClass;
                $obj->User = $fullname;
                $obj->matricula = $matricula;
                $obj->Email = $email;
                $obj->Carrera = $department;
                $resultado[] = $obj;
                return $resultado;
            } else {
                $obj = new \stdClass;
                $obj->Users = "404 Not Found";
                $resultado[] = $obj;
                return $resultado;
            }
        }
    }






    /*
***********************************************************
* Actualiza Usuario   (METHODO "PUT")
***********************************************************
*/
    public function UpdateUsers(Request $request, $matricula)
    {
        $resultado = array();
        if (is_null($matricula)) {
            $obj = new \stdClass;
            $obj->matricula = $matricula;
            $obj->Error = "Matricula no Encontrada";
            $resultado[] = $obj;
            return $resultado;
        } else {
            $body = json_decode(file_get_contents('php://input'), true);     
            if ($body != null) {
                header('Content-Type: application/json');
                $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]=".$matricula;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                if (preg_match('~Location: (.*)~i', $result, $match)) {
                    $location = trim($match[1]);
                    header('Content-Type: application/json');
                    $endpoint = $location;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_URL, $endpoint);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    $array = json_decode($result, true);
                    foreach ($array['users'] as $mPersona) {
                        $id = $mPersona['id'];
                    }
                    if (isEmpty($result)) { //*si la consulta por id o email es true-->ejecuta el cambio
                        $MoodleRest = new MoodleRest('http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php', '817c06ac6196681f2d8f7db8dc6401ab');
                        $new_group = array('users' => array(
                            array(
                                'id'         => $id,
                                'username'       => $body['Matricula'],
                                'firstname' =>$body['nombre'],
                                'lastname' => $body['apellidos'],
                                'email' => $body['Correo'],
                                'department' => strtolower($body['carrera']),
                            ),
                        ));
                        $return = $MoodleRest->request('core_user_update_users', $new_group, MoodleRest::METHOD_GET);
                        $payload = $MoodleRest->getUrl();
                        if ($payload!=null) {
                            $obj = new \stdClass;
                            $obj->User =$body['Matricula'];
                            $obj->Update = "Modificado correctamente en Moodle";
                            $resultado[] = $obj;
                            return $resultado;
                        } else {
                            $obj = new \stdClass;
                            $obj->User = $body['Matricula'];
                            $obj->Error = "Error de parametros de cuerpo, vuelva a intentralo";
                            $resultado[] = $obj;
                            return $resultado;
                        }
                    }else {
                        $obj = new \stdClass;
                        $obj->User = $body['Matricula'];
                        $obj->Error = "La matricula no Existe";
                        $resultado[] = $obj;
                        return $resultado;
                    }
                }
            }
        }
    }
}
