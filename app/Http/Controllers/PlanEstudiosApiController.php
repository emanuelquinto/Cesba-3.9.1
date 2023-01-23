<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;

class PlanEstudiosApiController extends Controller

{
    /*
***********************************************************
* Crea Una Carrera    (METHODO "POTS")
***********************************************************
*/

    public function CreaCarrera()
    {
        $resultado = array();
        $body = json_decode(file_get_contents('php://input'), true);
        $path = $body['clave_carrera'] . '-' . $body['rvoe'];
        try {
            header('Content-Type: application/json');
            $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_course_get_categories&criteria[0][key]=idnumber&criteria[0][value]=".$path;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            if (preg_match('~Location: (.*)~i', $result, $match)) {
                $location = trim($match[1]);
                header('Content-Type: application/json');
                $urlCompuesta = $location;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $urlCompuesta);
                $rest = curl_exec($ch);
                curl_close($ch);
                $array = json_decode($rest, true);
                $id = "";
                foreach ($array  as $category) {
                    $id = $category['idnumber'];
                }
                if ($id == $path || $id = null) {
                    $obj = new \stdClass;
                    $obj->Carrera = $body['carrera'];
                    $obj->Existente = "404 Not Found";
                    $resultado[] = $obj;
                    return $resultado;
                } else {
                    $MoodleRest = new MoodleRest('http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php', '817c06ac6196681f2d8f7db8dc6401ab');
                    $new_group = array('categories' => array(
                        array(
                            'name'         => $body['carrera'],
                            'parent'       => 0,
                            'idnumber' => $path,
                            'description' => $body['plan_estudios'],
                        ),
                    ));
                    $return = $MoodleRest->request('core_course_create_categories', $new_group, MoodleRest::METHOD_GET);
                    $payload = $MoodleRest->getUrl();
                    if ($payload != null) {
                        /*  Ahora si vueleve a buscar ala Categoria "carrera" para obtener el Id*/
                        $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_course_get_categories&criteria[0][key]=idnumber&criteria[0][value]=".$path;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $endpoint);
                        curl_setopt($ch, CURLOPT_HEADER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        if (preg_match('~Location: (.*)~i', $result, $match)) {
                            $location = trim($match[1]);
                            header('Content-Type: application/json');
                            $urlCompuesta = $location;
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_URL, $urlCompuesta);
                            $rest = curl_exec($ch);
                            curl_close($ch);
                            $array = json_decode($rest, true);
                            $idCarreraCreada = "";
                            foreach ($array  as $category) {
                                $idCarreraCreada = $category['id'];
                            }
                        }
                        /* aqui termina la busqueda del Id de la carrera */
                        $path_Cuatrimetre = $body['ciclo'] .'-'. $body['clave_carrera'];
                        header('Content-Type: application/json');
                        $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_course_get_categories&criteria[0][key]=idnumber&criteria[0][value]=" . $path_Cuatrimetre;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $endpoint);
                        curl_setopt($ch, CURLOPT_HEADER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        if (preg_match('~Location: (.*)~i', $result, $match)) {
                            $location = trim($match[1]);
                            header('Content-Type: application/json');
                            $urlCompuesta = $location;
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_URL, $urlCompuesta);
                            $rest = curl_exec($ch);
                            curl_close($ch);
                            $array = json_decode($rest, true);
                            $error = '';
                            foreach ($array  as $modulo_) {
                                $error = $modulo_['idnumber'];
                            }
                            if ($error == $path_Cuatrimetre) {
                                $obj = new \stdClass;
                                $obj->materia = $body['cuatrimestre'] . "404 Not Found";
                                $resultado[] = $obj;
                                return $resultado;
                            } else {
                                //$NumeroCuatrimestre=9;
                                //$NumeroCuatriCreados=9;
                                // for ($i=0; $i<=$NumeroCuatrimestre; $i++) {                  
                                $MoodleRest = new MoodleRest('http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php', '817c06ac6196681f2d8f7db8dc6401ab');
                                $new_group = array('categories' => array(
                                    array(
                                        'name'         => $body['cuatrimestre'],
                                        'parent'       => $idCarreraCreada,
                                        'idnumber'      => $path_Cuatrimetre
                                    ),
                                ));
                                $return = $MoodleRest->request('core_course_create_categories', $new_group, MoodleRest::METHOD_GET);
                                $payload = $MoodleRest->getUrl();
                                if ($payload != null) {
                                    $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_course_get_categories&criteria[0][key]=idnumber&criteria[0][value]=".$path_Cuatrimetre;
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
                                        $id = '';
                                        $idnumber = '';
                                        foreach ($array  as $mPersona) {
                                            $id = $mPersona['id'];
                                            $idnumber = $mPersona['idnumber'];
                                        }
                                        $pathMateria=$body['ciclo'].'-'.$body['clave'];
                                        $MoodleRest = new MoodleRest('http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php', '817c06ac6196681f2d8f7db8dc6401ab');
                                        $new_group = array('courses' => array(
                                            array(
                                                'fullname'         => $body['asignatura'],
                                                'shortname'       => $pathMateria,
                                                'categoryid'      => $id             
                                            ),
                                        ));
                                        $return = $MoodleRest->request('core_course_create_courses', $new_group, MoodleRest::METHOD_GET);
                                        $payload = $MoodleRest->getUrl();
                                        if ($payload!=null) {                                           
                                        $obj=new \stdClass;
                                        $obj->Carrera=$body['carrera']; 
                                        $obj->Cuatrimestres=$body['cuatrimestre'];  
                                        $obj->Asignaturas=$body['asignatura']; 
                                        $obj->Create="Create 200 Ok";              
                                        $resultado[]=$obj;
                                        echo json_encode($resultado);  
                                       }        
                                       
                                    }
                                }
                            }
                        }
                    }
                }
            }
          } catch (\Exception $e) {
            echo ' ',  $e->getMessage(), "\n";
        }
    }
    /*
***********************************************************
* Crea Una Cuatrimestres    (METHODO "POST")
***********************************************************
*/

    public function CreaCuatrimestres()
    {
        $resultado = array();
        $categy = json_decode(file_get_contents('php://input'), true);
        $rvoe = $categy['rvoe'];
        $ciclo = $categy['ciclo'];
        $clave_carrera = $categy['clave_carrera'];
        $modulo = $categy['modulo'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_categories&criteria[0][key]=idnumber&criteria[0][value]=' . $clave_carrera . '-' . $rvoe);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        $ciclo6 = json_decode($data, true);
        $id = '';
        $idnumber = '';
        foreach ($ciclo6  as $mPersona) {
            $id = $mPersona['id'];
            $idnumber = $mPersona['idnumber'];
        }

        if ($data) {
            $categy_ = json_decode(file_get_contents('php://input'), true);
            $path_ = $categy_['ciclo'] . '-' . $categy_['clave_carrera'];
            $clave_carrera = $categy_['clave_carrera'];
            $modulo = $categy_['modulo'];
            $ch_ = curl_init();
            curl_setopt($ch_, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_categories&criteria[0][key]=idnumber&criteria[0][value]=' . $path_);
            curl_setopt($ch_, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_, CURLOPT_HEADER, 0);
            $data2 = curl_exec($ch_);
            $cuerpo = json_decode($data2, true);
            $error = '';
            foreach ($cuerpo  as $modulo_) {
                $error = $modulo_['idnumber'];
            }
            if ($error == $ciclo . '-' . $clave_carrera) {
                $obj = new \stdClass;
                $obj->materia = $modulo . "--> 404 Error Ya Existe";
                $resultado[] = $obj;
                return $resultado;
            } else {
                $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                $new_group = array('categories' => array(
                    array(
                        'name'         => $modulo,
                        'parent'       => $id,
                        'idnumber'      =>  $path_
                    ),
                ));

                $return = $MoodleRest->request('core_course_create_categories', $new_group, MoodleRest::METHOD_POST);

                $payload = $MoodleRest->getUrl();

                if ($payload) {
                    $obj = new \stdClass;
                    $obj->materia = $modulo . "--> 200 OK Creado Correctamente";
                    $resultado[] = $obj;
                    return $resultado;
                    //echo json_encode("El cuatrimestre : " . $modulo . " ' - A sido creado correctamente en Moodle");
                } else {
                    $obj = new \stdClass;
                    $obj->materia = $modulo . "--> 404 Fallo al Crearse";
                    $resultado[] = $obj;
                    return $resultado;
                }
                curl_close($ch);
                curl_close($ch_);
            }
        }
    }

    /*
***********************************************************
* Crea Una Materias    (METHODO "POST")
***********************************************************
*/

    public function CreaMaterias()
    {

        $categy = json_decode(file_get_contents('php://input'), true);
        $clave = $categy[0]['ciclo'];
        $modulo = $categy[0]['modulo'];
        $clave_carrera = $categy[0]['clave_carrera'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_categories&criteria[0][key]=idnumber&criteria[0][value]=' . $clave . '-' . $clave_carrera);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        $ciclo = json_decode($data, true);
        $id = '';
        $idnumber = '';
        foreach ($ciclo  as $mPersona) {
            $id = $mPersona['id'];
            $idnumber = $mPersona['idnumber'];
        }
        if ($data) {
            $resultado = array();
            $condicion = count($categy);
            for ($i = 1; $i < count($categy); $i++) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_courses_by_field&field=shortname&value=' . $clave . '-' . $clavemateria = $categy[$i]['clave']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $query = curl_exec($ch);
                $materia_ = json_decode($query, true);
                $name = '';
                foreach ($materia_['courses']  as $curso) {
                    $name = $curso['shortname'];
                }


                if ($name == $clave . '-' . $clavemateria = $categy[$i]['clave']) {
                    $resultado = array();
                    $data = array(array("Esta Materia : [" . $clave . '-' . $clavemateria = $categy[$i]['clave'] . "] ya existe en moodle " => " HTTP 404 Not Found "));

                    echo json_encode($data);
                } else {

                    if ($condicion >= $i) {
                        $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                        $new_group = array('courses' => array(
                            array(
                                //'username' =>strtolower(preg_replace(['/\s+/','/^\s|\s$/'],['',''], $matricula)),
                                'fullname'         => $asignatura = $categy[$i]['asignatura'],
                                'shortname'       => $clave . '-' . $clavemateria = $categy[$i]['clave'],
                                'categoryid'      => $id
                                //'path'          => $path,               
                            ),
                        ));

                        $return = $MoodleRest->request('core_course_create_courses', $new_group, MoodleRest::METHOD_POST);
                        $payload = $MoodleRest->getUrl();



                        if ($payload) {
                            $obj = new \stdClass;
                            $obj->materia = $clave . '-' . $clavemateria = $categy[$i]['clave'] . "--> 200 OK Creado Correctamente";
                            $resultado[] = $obj;
                        }
                    }
                }
            }
            return $resultado;
        }

        $response = curl_close($ch);
    }
    //*definir la consulta de URL , si GET 
    public function ConsultaMateria($ConsultaMaterias)
    {
        $resultado = array();
        $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_course_get_courses_by_field&field=shortname&value=".$ConsultaMaterias;
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
        $nombreCorto = '';
        $cuatrimestre = '';
        $name = '';
        $array = json_decode( $rest, true);
        foreach ($array['courses'] as $curso) {
            $name = $curso['fullname'];
            $nombreCorto = $curso['shortname'];
            $cuatrimestre = $curso['categoryname'];
        }
        $obj = new \stdClass;
        $obj->materia = $name;
        $obj->clave = $nombreCorto;
        $obj->cuatrimestre = $cuatrimestre;
        $resultado[] = $obj;
        return $resultado;
    }

    /*
***********************************************************
* Calificacion de la Materia    (METHODO "GET")
***********************************************************
*/
    public function CalificacionMateria($calificacionMateria)
    {
        $resultado = array();
        $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_course_get_courses_by_field&field=shortname&value=".$calificacionMateria;
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
           
        }
        $idcurse = '';
        $fulname = '';
        $shortname = '';
        $arraycurse = json_decode($rest, true);
        foreach ($arraycurse['courses'] as $curso) {
            $idcurse = $curso['id'];
            $fulname = $curso['fullname'];
            $shortname = $curso['shortname'];
        }
        if ($rest!=null) {
            $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=gradereport_user_get_grade_items&courseid=".$idcurse;
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
            foreach ($array['usergrades'] as $curso) {
               $id = $curso['userid'];
                $shortname = $curso['userfullname'];
                       
                $endpoint = "http://campusvirtual.cesba-queretaro.edu.mx/webservice/rest/server.php?wstoken=817c06ac6196681f2d8f7db8dc6401ab&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=id&criteria[0][value]=" .$id;
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

            }
                $array = json_decode($result, true);
                $username = '';      
                foreach ($array['users']  as $mPersona) {
                  $username = $mPersona['username'];
                }
                foreach ($curso['gradeitems'] as $v2) {
                    $micalificacion = $v2['percentageformatted'];
                    $gradeformatted = $v2['gradeformatted'];
                }
            
                $obj = new \stdClass;
                $obj->matricula =$username;
                $obj->nombre = $shortname;
                $obj->Materia =$fulname;
                $obj->calificacion_final = $micalificacion;
                $resultado[] = $obj;
                echo json_encode($obj);
            }
        } else {
            $obj = new \stdClass;
            $obj->Materia =$calificacionMateria;
            $resultado[] = $obj;
            return $resultado;
        }
        
    }
}
