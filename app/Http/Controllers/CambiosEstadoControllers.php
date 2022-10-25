<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
/*
***********************************************************
*  ENROLAR , DESENROLAR , SUSPENDER , ACTIVAR
***********************************************************
*/
class CambiosEstadoControllers extends Controller
{

    /*
***********************************************************
* Enrolar Users a Curse    (METHODO "POST")
***********************************************************
*/

   public function EnrolarUsersCurse(){
    $resultado =array();
    $body = json_decode(file_get_contents('php://input'), true);
    $ciclo = $body[0]['ciclo'];
    $Rol = $body[0]['Rol'];
    $clave = $body[0]['clave'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_courses_by_field&field=shortname&value=' . $ciclo . '-' . $clave);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    $idcurse = '';
    $fulname = '';
    $shortname = '';
    $arraycurse = json_decode($data, true);
    foreach ($arraycurse['courses'] as $curso) {
        $idcurse = $curso['id'];
        $fulname = $curso['fullname'];
        $shortname = $curso['shortname'];
    }
    if ($data) {
        for ($i = 1; $i < count($body); $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]=' . $name = $body[$i]['matriculas_alumnos']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $buscar = curl_exec($ch);
            curl_close($ch);
            $error = '';
            $valida = '';
            $array = json_decode($buscar, true);
            foreach ($array['users'] as $user) {
                $valida = $user['id'];
                $firstname = $user['firstname'];
                $username = $user['username'];
            }
            if ($buscar) {


                if ($username = null) {
                    $obj=new \stdClass;
                    $obj->matriculas= $body[$i]['matriculas_alumnos'];
                    $obj->Error="Usuarios no Encontrados en Moodle";
                     $resultado[]=$obj;
                     return $resultado;          
                } else {
                    if ($Rol == 'Estudiante') {
                        $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                        $new_group = array('enrolments' => array(
                            array(

                                'roleid'         => 5,
                                'userid'       => $valida,
                                'courseid'      => $idcurse
                            ),
                        ));

                        $return = $MoodleRest->request('enrol_manual_enrol_users', $new_group, MoodleRest::METHOD_POST);
                        $payload = $MoodleRest->getUrl();
                        if ($payload) {
                           
                            $obj=new \stdClass;
                            $obj->UserRolado=$firstname;
                            $obj->Materia=$fulname;
                            $obj->ClaveMateria=$shortname;
                             $resultado[]=$obj;
                             return $resultado;
                        } else {
                          
                            $obj=new \stdClass;
                            $obj->UserRolado=$firstname;
                            $obj->Error="No se pudo Enrolar";
                             $resultado[]=$obj;
                             return $resultado;
                        }
                    } else {
                        if ($Rol == 'Profesor') {
                            $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                            $new_group = array('enrolments' => array(
                                array(

                                    'roleid'         => 3,
                                    'userid'       => $valida,
                                    'courseid'      => $idcurse


                                ),
                            ));

                            $return = $MoodleRest->request('enrol_manual_enrol_users', $new_group, MoodleRest::METHOD_POST);
                            $payload = $MoodleRest->getUrl();
                            if ($payload) {
                               
                                $obj=new \stdClass;
                                $obj->UserRolado=$firstname;
                                $obj->Materia=$fulname;
                                $obj->ClaveMateria=$shortname;
                                $obj->Rol=$Rol;
                                $obj->Enrolado="Enrolado Satisfatoriamente";
                                 $resultado[]=$obj;
                                 return $resultado;
                            } else {
                             $obj=new \stdClass;
                            $obj->UserRolado=$firstname;
                            $obj->Error="No se pudo Enrolar";
                             $resultado[]=$obj;
                             return $resultado;
                            }
                        } else {
                            $obj=new \stdClass;
                            $obj->UserRolado=$firstname;
                            $obj->Error="No se pudo Enrolar";
                             $resultado[]=$obj;
                             return $resultado;
                        }
                    }
                }
            } else {
                $obj=new \stdClass;
                $obj->UserRolado=$firstname;
                $obj->Error="No Existe";
                 $resultado[]=$obj;
                 return $resultado;
            }
        }
    } else {
        $obj=new \stdClass;
       
        $obj->Error="La materia no Existe";
         $resultado[]=$obj;
         return $resultado;
    }

   }


 /*
***********************************************************
* Desenrolar Users de Curse    (METHODO "POST")
***********************************************************
*/
   public function DesenrolarUsersCurse(){
    $resultado =array();
    $body = json_decode(file_get_contents('php://input'), true);
    $ciclo = $body[0]['ciclo'];
    $clave = $body[0]['clave'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_courses_by_field&field=shortname&value=' . $ciclo . '-' . $clave);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    $idcurse = '';
    $fulname = '';
    $shortname = '';
    $arraycurse = json_decode($data, true);
    foreach ($arraycurse['courses'] as $curso) {
        $idcurse = $curso['id'];
        $fulname = $curso['fullname'];
        $shortname = $curso['shortname'];
    }
    if ($data) {

        for ($i = 1; $i < count($body); $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]=' . $name = $body[$i]['matriculas_alumnos']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $buscar = curl_exec($ch);

            $firstname = '';
            $lastname = '';
            $valida = '';
            $array = json_decode($buscar, true);
            foreach ($array['users'] as $user) {
                $valida = $user['id'];
                $firstname = $user['firstname'];
                $lastname = $user['lastname'];
            }
            if ($buscar) {

                if ($lastname = null) {
                    $obj=new \stdClass;
                    $obj->Matriculados= $body[$i]['matriculas_alumnos'];
                    $obj->Error="No se Encuentra Enrolado";
                     $resultado[]=$obj;
                     return $resultado;
                    
                } else {
                    $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                    $new_group = array('enrolments' => array(
                        array(
                            'userid'       => $valida,
                            'courseid'      => $idcurse
                        ),
                    ));
                    $return = $MoodleRest->request('enrol_manual_unenrol_users', $new_group, MoodleRest::METHOD_POST);
                    $payload = $MoodleRest->getUrl();
                    if ($payload) {
                      

                        $obj=new \stdClass;
                        $obj->User=$firstname;
                        $obj->Materia=$shortname;
                        $obj->Desenrolado="Desenrolado Satisfatoriamente";
                         $resultado[]=$obj;
                         return $resultado;
                    } else {
                       
                        $obj=new \stdClass;
                        $obj->User=$firstname;
                        $obj->Error="No se Desenrolo de la materia";
                         $resultado[]=$obj;
                         return $resultado;
                    }
                }
            }
        }
    } else {
    $obj=new \stdClass;
     
    $obj->Error="Materia No encontrada";
    $resultado[]=$obj;
    return $resultado;
    }

   }

    /*
***********************************************************
* Suspender Users   (METHODO "POST")
***********************************************************
*/

 public function SuspenderUsers(){
    $resultado =array();
    $body = json_decode(file_get_contents('php://input'), true);
            $ciclo = $body[0]['ciclo'];
            $clave = $body[0]['clave'];
            for ($i = 1; $i < count($body); $i++) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]=' . $name = $body[$i]['matriculas_alumnos']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $buscar = curl_exec($ch);

                $firstname = '';
                $lastname = '';
                $valida = '';
                $array = json_decode($buscar, true);
                foreach ($array['users'] as $user) {
                    $valida = $user['id'];
                    $firstname = $user['firstname'];
                    $lastname = $user['lastname'];
                }

                if ($buscar) {

                    if ($lastname = null) {
                      
                                $obj=new \stdClass;
                                $obj->Error="User no Encontrado";
                                $resultado[]=$obj;
                                return $resultado;
                    } else {
                        $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                        $new_group = array('users' => array(
                            array(

                                'id'         => $valida,
                                'suspended'       => 1,
                            ),
                        ));

                        $return = $MoodleRest->request('core_user_update_users', $new_group, MoodleRest::METHOD_POST);
                        $payload = $MoodleRest->getUrl();
                        if ($payload) {
                          
                            $obj=new \stdClass;
                                $obj->User=$firstname;
                                $obj->Suspendido="Suspendido de forma Temporal";
                                $resultado[]=$obj;
                                return $resultado;
                        } else {
                           

                            $obj=new \stdClass;
                            $obj->User=$firstname;
                            $obj->Suspendido="No! fue Suspendido";
                            $resultado[]=$obj;
                            return $resultado;
                        }
                    }
                }
            }
 }

 public function AbilitarUser(){
    $resultado =array();
    $body = json_decode(file_get_contents('php://input'), true);
    $ciclo = $body[0]['ciclo'];
    $clave = $body[0]['clave'];


    for ($i = 1; $i < count($body); $i++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]=' . $name = $body[$i]['matriculas_alumnos']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $buscar = curl_exec($ch);

        $firstname = '';
        $lastname = '';
        $valida = '';
        $array = json_decode($buscar, true);
        foreach ($array['users'] as $user) {
            $valida = $user['id'];
            $firstname = $user['firstname'];
            $lastname = $user['lastname'];
        }

        if ($buscar) {

            if ($lastname = null) {
                echo json_encode("El Usuario : " . $body[$i]['matriculas_alumnos'] . "'- Nose encuentra En Moodle'");
                $obj=new \stdClass;
                            $obj->User=$body[$i]['matriculas_alumnos'];
                            $obj->Error="Nose encuentra En Moodle";
                            $resultado[]=$obj;
                            return $resultado;
            } else {
                $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                $new_group = array('users' => array(
                    array(

                        'id'         => $valida,
                        'suspended'       => 0,
                    ),
                ));

                $return = $MoodleRest->request('core_user_update_users', $new_group, MoodleRest::METHOD_POST);
                $payload = $MoodleRest->getUrl();
                if ($payload) {
                   
                    $obj=new \stdClass;
                    $obj->User=$firstname;
                    $obj->Activado="Activado En la Plataforma";
                    $resultado[]=$obj;
                    return $resultado;
                } else {
                    

                    $obj=new \stdClass;
                    $obj->User=$firstname;
                    $obj->Activado="Sigue Suspendido";
                    $resultado[]=$obj;
                    return $resultado;
                }
            }
        }
    }
 }
}
