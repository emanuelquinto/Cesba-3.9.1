<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function PHPUnit\Framework\isNull;

class UsersApiController extends Controller
{


 /**
     * Escucha de peticiones Moodle.
     *Tipo POST o Get , Put
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response  $response
     */
  
/*
********************************************************************
* Crear Alumnos y Maestros con Sus Respectivos Roles (METHODO "POST") 
*********************************************************************
*/
    public function CrearAlumnos(){
        //*consumimos el servicio foraneo
       $resultado =array();
       $body = json_decode(file_get_contents('php://input'), true);
            $matricula = $body['matricula'];
            $nombre = $body['nombre'];
            $apellidos = $body['apellidos'];
            $carrera = $body['carrera'];
            $rol = $body['rol'];
            $email = $body['email'];
            $id = '';
            $emailmooble = '';
            $perfil = '';
        //*Consultamos si el Gmail Consumida ya se encuentra en Moodle
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=email&criteria[0][value]='.$email);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $data = curl_exec($ch);
            curl_close($ch);
            $array = json_decode($data, true);
            foreach ($array['users'] as $mPersona) {
                $id = $mPersona['id'];
                $emailmooble = $mPersona['email'];
                $perfil = $mPersona['aim'];
            }
        //*Si la matricula Existe, entra a ese
            if ($email == $emailmooble) {
                $obj=new \stdClass;
                $obj->User=$nombre;
                $obj->Matricula=$matricula;
                $obj->ERROR="USUARIO EXISTENTE";
                 $resultado[]=$obj;
                 return $resultado;
            } else
            { 
         //*Esta Codificacion Hereda la clase  MoodleRest , en cual se encuentra los metodos HTTP para las conexion a moodle. 
                $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                $new_group = array('users' => array(
                    array(
                        'username'       => $matricula,
                        'createpassword' => '1',
                        'firstname' =>  $nombre,
                        'lastname' => $apellidos,
                        'department' => strtolower($carrera),
                        'email' => $email,
                        'city' => 'Santiago de Queretaro',
                        'country' => 'MX',
                        'aim' => $rol  
                    ),
                ));

                $return = $MoodleRest->request('core_user_create_users', $new_group, MoodleRest::METHOD_POST);
                $payload = $MoodleRest->getUrl();
         //*Aqui termina La conexion y consumo del Servicio Rest, que moodle Acepta por el Methodo POST 
                if ($payload) {
         //* Si la conexion es True, se Evalua si es Rol , Estudiante o Profesor y se Crea con Ese rol
                    if ($rol == 'Estudiante') {
                        $id = '';
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=email&criteria[0][value]=' . $email);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        $data = curl_exec($ch);
                        curl_close($ch);
                        $array = json_decode($data, true);
                        foreach ($array['users'] as $mPersona) {
                            $id = $mPersona['id'];
                            $name = $mPersona['firstname'];
                        }

                        $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                        $new_group = array('assignments' => array(
                            array(

                                'roleid'         => 5,//*Numero de Rol de Estudiante
                                'userid'       => $id
                            ),
                        ));

                        $return = $MoodleRest->request('core_role_assign_roles', $new_group, MoodleRest::METHOD_POST);

                        $payload = $MoodleRest->getUrl();

                        if ($payload) {
                           
                            $obj=new \stdClass;
                            $obj->User=$nombre." -> Asido Crado con un Rol de : ".$rol;
                            
                             $resultado[]=$obj;
                             return $resultado;
                        }
                    } else {
                        $id = '';
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=email&criteria[0][value]=' . $email);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        $data = curl_exec($ch);
                        curl_close($ch);
                        $array = json_decode($data, true);
                        foreach ($array['users'] as $mPersona) {
                            $id = $mPersona['id'];
                            $name = $mPersona['firstname'];
                        }

                        $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                        $new_group = array('assignments' => array(
                            array(
                                'roleid'       => 4, // numero de Rol profesor sin edicion
                                'userid'       => $id
                            ),
                        ));
                        $return = $MoodleRest->request('core_role_assign_roles', $new_group, MoodleRest::METHOD_POST);

                        $payload = $MoodleRest->getUrl();

                        if ($payload) {
                          

                            $obj=new \stdClass;
                            $obj->User=$nombre." -> Asido Crado con un Rol de : ".$rol;
                            
                             $resultado[]=$obj;
                             return $resultado;
                        }
                    }
                }
            }


    }

/*
***********************************************************
* Consulta Por Matricula   (METHODO "GET")
***********************************************************
*/
public  function getAlumnos($matricula , Request $request ){
    $resultado =array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]='.$matricula);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch); 
    $array = json_decode($data, true);
    $fullname = '';
    $matricula = '';
    $email = '';
    $department = '';
    foreach ($array['users']  as $mPersona) {
        $fullname = $mPersona['fullname'];
        $matricula = $mPersona['username'];
        $email = $mPersona['email'];
        $department = $mPersona['department'];
    }
    
   if ($data!=null) {
   $obj=new \stdClass;
   $obj->User=$fullname;
   $obj->matricula=$matricula;
   $obj->Email=$email;
   $obj->Carrera=$department;
   $resultado[]=$obj;
    return $resultado;
   }else {
    $obj=new \stdClass;  
    $obj->Users="404 Not Found";
    $resultado[]=$obj;
     return $resultado;
   }
 
   
     
  
}


/*
***********************************************************
* Actualiza Usuario   (METHODO "PUT")
***********************************************************
*/


public function UpdateUsers(Request $request,$matricula){
    $resultado =array();
    if (is_null($matricula)) {
        $obj=new \stdClass;
        $obj->matricula=$matricula;
        $obj->Error="Matricula no Encontrada";
         $resultado[]=$obj;
         return $resultado;
      
     }else{ 
    $body = json_decode(file_get_contents('php://input'), true);
    $nombre = $body['nombre'];
    $apellidos = $body['apellidos'];
    $carrera = $body['carrera'];
    $email = $body['email'];
    if ($body) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]=' . $matricula);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $array = json_decode($data, true);
        foreach ($array['users'] as $mPersona) {
            $id = $mPersona['id'];
            $emailMoodle = $mPersona['email'];
        }
        if ($data) { //*si la consulta por id o email es true-->ejecuta el cambio
            $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
            $new_group = array('users' => array(
                array(
                    'id'         => $id,
                    'username'       => $matricula,
                    'firstname' =>  $nombre,
                    'lastname' => $apellidos,
                    'email' => $email,
                    'department' => strtolower($carrera),

                ),
            ));

            $return = $MoodleRest->request('core_user_update_users', $new_group, MoodleRest::METHOD_POST);

            $payload = $MoodleRest->getUrl();

            if ($payload) {
                $obj=new \stdClass;
                $obj->User=$nombre;
                $obj->Update="Modificado correctamente en Moodle";
                 $resultado[]=$obj;
                 return $resultado;
               
            } else {
              
                $obj=new \stdClass;
                $obj->User=$nombre;
                $obj->Error="Error de parametros de cuerpo, vuelva a intentralo";
                 $resultado[]=$obj;
                 return $resultado;
            }
        }
    }
}
}


}
