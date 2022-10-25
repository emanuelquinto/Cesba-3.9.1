<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class PlanEstudiosApiController extends Controller

{
     /*
***********************************************************
* Crea Una Carrera    (METHODO "POTS")
***********************************************************
*/

public function CreaCarrera(){
    $resultado =array();
    $categy = json_decode(file_get_contents('php://input'), true);
    $rvoe = $categy['rvoe'];
    $carrera = $categy['carrera'];
    $clave_carrera = $categy['clave_carrera'];
    $plan_estudios = $categy['plan_estudios'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_categories&criteria[0][key]=idnumber&criteria[0][value]=' . $clave_carrera . '-' . $rvoe);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    $ciclo = json_decode($data, true);
    $id = '';
    foreach ($ciclo  as $mPersona) {
        $id = $mPersona['idnumber'];
    }
    if ($id == $clave_carrera . '-' . $rvoe) {
        //return response()->json( "la carrera : " . $carrera . " ' Ya existe en Moodle",404);
        $obj=new \stdClass;
        $obj->materia=$carrera ."--> 404 Error Ya Existe";        
         $resultado[]=$obj;
         return $resultado; 
    } else
    {
        $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
        $new_group = array('categories' => array(
            array(
                'name'         => $carrera,
                'parent'       => 0,
                'idnumber' =>  $clave_carrera . '-' . $rvoe,
                'description' => $plan_estudios,
            ),
        ));
        $return = $MoodleRest->request('core_course_create_categories', $new_group, MoodleRest::METHOD_POST);

        $payload = $MoodleRest->getUrl();

        if ($payload) {
            $obj=new \stdClass;
                $obj->materia=$carrera ."--> 200 OK Creado Correctamente";        
                 $resultado[]=$obj;
                 return $resultado;      
        }
    }
    curl_close($ch);
}

/*
***********************************************************
* Crea Una Cuatrimestres    (METHODO "POT")
***********************************************************
*/

public function CreaCuatrimestres(){
    $resultado =array();
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
        if ($error == $ciclo. '-' . $clave_carrera) {
       $obj=new \stdClass;
        $obj->materia=$modulo ."--> 404 Error Ya Existe";        
         $resultado[]=$obj;
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
                $obj=new \stdClass;
                $obj->materia=$modulo ."--> 200 OK Creado Correctamente";        
                 $resultado[]=$obj;
                 return $resultado; 
                //echo json_encode("El cuatrimestre : " . $modulo . " ' - A sido creado correctamente en Moodle");
            }else {
                $obj=new \stdClass;
                $obj->materia=$modulo ."--> 404 Fallo al Crearse";        
                 $resultado[]=$obj;
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

public function CreaMaterias(){
   
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
        $resultado =array();
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
                $resultado =array();
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
                        $obj=new \stdClass;
                $obj->materia=$clave . '-' . $clavemateria = $categy[$i]['clave'] ."--> 200 OK Creado Correctamente";        
                 $resultado[]=$obj;
                
                    }
                }
            }
           
        }
         return $resultado;
    }

    $response = curl_close($ch);
}
//*definir la consulta de URL , si GET o POST
public function ConsultaMateria($ConsultaMateria){
    $resultado =array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_courses_by_field&field=shortname&value='.$ConsultaMateria);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    $area = '';
    $nombreCorto = '';
    $cuatrimestre = '';
    $docente = '';
    $name = '';
    $array = json_decode($data, true);
    foreach ($array['courses'] as $curso) {
        $name = $curso['fullname'];
        $nombreCorto = $curso['shortname'];
        $cuatrimestre = $curso['categoryname'];
    }
    $obj=new \stdClass;
    $obj->materia=$name;
    $obj->clave=$nombreCorto;
    $obj->cuatrimestre=$cuatrimestre;
     $resultado[]=$obj;
     return $resultado;

}

/*
***********************************************************
* Calificacion de la Materia    (METHODO "GET")
***********************************************************
*/
public function CalificacionMateria($calificacionMateria){
    $resultado =array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_courses_by_field&field=shortname&value='.$calificacionMateria);
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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=gradereport_user_get_grade_items&courseid=' . $idcurse);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
           
          
       
        $arraycurse = json_decode($data, true);
        foreach ($arraycurse['usergrades'] as $curso) {
             $id=$curso['userid'];
             $shortna = $curso['userfullname'];
             $ch = curl_init();
             curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=id&criteria[0][value]='.$id);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_HEADER, 0);
             $data = curl_exec($ch);
             curl_close($ch); 
             $array = json_decode($data, true);    
             $username = '';
             foreach ($array['users']  as $mPersona) {
                 $username = $mPersona['username'];
             }
            foreach ($curso['gradeitems'] as $v2) {
                $micalificacion = $v2['percentageformatted'];
                $gradeformatted = $v2['gradeformatted'];
            }
            $obj=new \stdClass;
            $obj->matricula=$username;
            $obj->nombre=$shortna;
            $obj->calificacion_final=$micalificacion;
             $resultado[]=$obj;
          
            //echo "\n Alumno :" . $shortname = $curso['userfullname'] . " calificacion : [" . $micalificacion . "] Con un Total de Actividades del curso " . $fulname . " :[" . $gradeformatted . "]";
            //$resultado = array(array("Alumno :". $shortname = $curso['userfullname']. " calificacion : [".$micalificacion."] Con un Total del curso ".$fulname. " :[".$gradeformatted."]" => "HTTP 200 OK "));
            // header("Content-Type: application/json");
            //echo json_encode($resultado);
        }
    } 
    else {
       $resultado = array(array("La clave de la materia Obtenida no existe " => " HTTP 404 Not Found "));
        header("Content-Type: application/json");
        return $resultado;
    }
    return $resultado;

}
}

