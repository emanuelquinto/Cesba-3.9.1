<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPUnit\Util\Json;

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
    public function EnrolarUsersCurse()
    {
        $resultado = array();
        $body = json_decode(file_get_contents('php://input'), true);
        try{
        for ($i = 1; $i <count($body); $i++) {
            $conteo =count($body[$i]);
         
            $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_course_get_courses_by_field&field=shortname&value=".$body[0]['ciclo'].'-'.$body[0]['clave'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            if (preg_match('~Location: (.*)~i', $result, $match)) {
                $location = trim($match[1]);
                $urlCompuesta = $location;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $urlCompuesta);
                $rest = curl_exec($ch);
                curl_close($ch);
                $array = json_decode($rest, true);
            }
            foreach ($array['courses'] as $curso) {
                $idcurse = $curso['id'];
                $fulname = $curso['fullname'];
                $shortname = $curso['shortname'];
            }
            if ($rest!= null) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]='.$body[$i]['matriculas_alumnos']);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result1 = curl_exec($ch);
                if (preg_match('~Location: (.*)~i', $result1, $match)) {
                    $location = trim($match[1]);
                    header('Content-Type: application/json');
                    $endpoint = $location;
                    $cl = curl_init();
                    curl_setopt($cl, CURLOPT_NOBODY, false);
                    curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($cl, CURLOPT_URL, $endpoint);
                    $result2 = curl_exec($cl);
                    curl_close($cl);
                }         
                $userId = '';
                $array = json_decode($result2, true);
                foreach ($array['users'] as $user) {
                    $userId = $user['id'];
                    $firstname = $user['firstname'];
                   $username = $user['username'];
                }
                if ($result2!=null) { 
                        if ($username ==$body[$i]['matriculas_alumnos']) {        
                            $obj = new \stdClass;
                            $obj->Users =$body[$i]['matriculas_alumnos'];
                            $obj->Error = "404 Not Funt ->  Users Existentes";
                            $resultado[] = $obj;
                            echo json_encode(array($obj));
                        } else  {                           
                            if ($body[0]['Rol'] == 'Estudiante') {
                                $MoodleRest = new MoodleRest('http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php', '817c06ac6196681f2d8f7db8dc6401ab');
                                $new_group = array('enrolments' => array(
                                    array(
                                        'roleid'         =>5,
                                        'userid'       => $userId,
                                        'courseid'      => $idcurse
                                    ),
                                ));
                                $return = $MoodleRest->request('enrol_manual_enrol_users', $new_group, MoodleRest::METHOD_GET);
                                $payload = $MoodleRest->getUrl();
                                if ($payload!=null) {
                                    $obj = new \stdClass;
                                    $obj->UserRolado =$body[$i]['matriculas_alumnos'];
                                    $obj->Materia = $fulname;
                                    $obj->ClaveMateria = $shortname;
                                    $resultado[] = $obj;
                                    echo json_encode($resultado);
                                } else {
                                    $obj = new \stdClass;
                                    $obj->UserRolado = $firstname;
                                    $obj->Error = "No se pudo Enrolar";
                                    $resultado[] = $obj;
                                    echo json_encode($resultado);
                                }
                             }else {     
                                $MoodleRest = new MoodleRest('http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php', '817c06ac6196681f2d8f7db8dc6401ab');
                                $new_group = array('enrolments' => array(
                                    array(
                                        'roleid'         => 3,
                                        'userid'       => $userId,
                                        'courseid'      => $idcurse
                                    ),
                                ));
                                $return = $MoodleRest->request('enrol_manual_enrol_users', $new_group, MoodleRest::METHOD_GET);
                                $payload = $MoodleRest->getUrl();
                                if ($payload) {
                                    $obj = new \stdClass;
                                    $obj->UserRolado = $body[$i]['matriculas_alumnos'];
                                    $obj->Materia = $fulname;
                                    $obj->ClaveMateria = $shortname;
                                    $obj->Rol = $body[0]['Rol'];
                                    $obj->Enrolado = "Enrolado Satisfatoriamente";
                                    $resultado[] = $obj;
                                    echo json_encode($resultado);
                                } else {
                                    $obj = new \stdClass;
                                    $obj->UserRolado = $firstname;
                                    $obj->Error = "No se pudo Enrolar";
                                    $resultado[] = $obj;
                                    echo json_encode($resultado);
                                }
                            }
                        
                        }
                    }
                    else {
                        $obj = new \stdClass;
                        $obj->UserRolado =$body[$i]['matriculas_alumnos'];
                        $obj->Error = "No Existe";
                        $resultado[] = $obj;
                        return $resultado;
              
                } 
                
                }
                else {
                    $obj = new \stdClass;
                    $obj->Materia =$body[$i]['ciclo'].'-'.$body[$i]['clave'];
                    $obj->Error = "No Existe";
                    $resultado[] = $obj;
                    return $resultado;
          
            } 
            } 
        
    }
    
    catch (\Exception $e) {
        echo '',  $e->getMessage(), "\n";
    }
 }
    /*
***********************************************************
* Desenrolar Users de Curse    (METHODO "POST")
***********************************************************
*/
    public function DesenrolarUsersCurse()
    {
        $resultado = array();
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
                        $obj = new \stdClass;
                        $obj->Matriculados = $body[$i]['matriculas_alumnos'];
                        $obj->Error = "No se Encuentra Enrolado";
                        $resultado[] = $obj;
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


                            $obj = new \stdClass;
                            $obj->User = $firstname;
                            $obj->Materia = $shortname;
                            $obj->Desenrolado = "Desenrolado Satisfatoriamente";
                            $resultado[] = $obj;
                            return $resultado;
                        } else {

                            $obj = new \stdClass;
                            $obj->User = $firstname;
                            $obj->Error = "No se Desenrolo de la materia";
                            $resultado[] = $obj;
                            return $resultado;
                        }
                    }
                }
            }
        } else {
            $obj = new \stdClass;

            $obj->Error = "Materia No encontrada";
            $resultado[] = $obj;
            return $resultado;
        }
    }

    /*
***********************************************************
* Suspender Users   (METHODO "POST")
***********************************************************
*/

    public function SuspenderUsers()
    {
        $resultado = array();
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

                    $obj = new \stdClass;
                    $obj->Error = "User no Encontrado";
                    $resultado[] = $obj;
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

                        $obj = new \stdClass;
                        $obj->User = $firstname;
                        $obj->Suspendido = "Suspendido de forma Temporal";
                        $resultado[] = $obj;
                        return $resultado;
                    } else {


                        $obj = new \stdClass;
                        $obj->User = $firstname;
                        $obj->Suspendido = "No! fue Suspendido";
                        $resultado[] = $obj;
                        return $resultado;
                    }
                }
            }
        }
    }

    public function AbilitarUser()
    {
        $resultado = array();
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
                    $obj = new \stdClass;
                    $obj->User = $body[$i]['matriculas_alumnos'];
                    $obj->Error = "Nose encuentra En Moodle";
                    $resultado[] = $obj;
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

                        $obj = new \stdClass;
                        $obj->User = $firstname;
                        $obj->Activado = "Activado En la Plataforma";
                        $resultado[] = $obj;
                        return $resultado;
                    } else {


                        $obj = new \stdClass;
                        $obj->User = $firstname;
                        $obj->Activado = "Sigue Suspendido";
                        $resultado[] = $obj;
                        return $resultado;
                    }
                }
            }
        }
    }
}
