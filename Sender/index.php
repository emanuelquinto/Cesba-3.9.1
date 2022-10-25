<?php
/*
*********************************************
*AUTOR: ING EMANUEL QUINTO ZAGAL (Programador e-learning)
*********************************************





*                    Documentacion 
*Es Importante incluir autoload.php en este archivo para cargar 
*/
require_once dirname(__FILE__) . '/autoload.php';
/*
********************************************************************************************************************
*-Para Ejecutar Cada Api ,Todas las peticiones del cliente seran escuchadas por el methodo POST y GET.
*-Cada ruta, es validada con la informacion que es pasada en los json por el cliente.
*-Moodle Ofrece rna variedad de Apis que el usuario Autenticado con El ROL de Administrador puede Usar
*-Moodle Solo Escucha Peticiones GET y POST (Crea y Obtiene).
*-Para usar una Api de MOODlE, Debes Documentarte para Conocer lo parametros Aceptados para cual realizar una Accion 
*-Es Importante Crear Un Token de USER para Obtener el acceso a la imformacio.
*-http:{TU DOMINIO}/webservice/rest/server.php?wstoken={TU TOKEN }&moodlewsrestformat=json&wsfunction={TU FUNCION}&
*-MoodleRest.php esta creada con el objetivo de insertar parametros en la URL para los methodos HTTP para Moodle.
*********************************************************************************************************************
*/
/*
* Escuchar el methodo del Cliente
*$_SERVER['REQUEST_METHOD'] == 'POST'
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    switch ($_GET["moodle"]) { // Alta de un alumno--y Alta de un Profesor---methodo POST

        case 'crear_alumno':

            $body = json_decode(file_get_contents('php://input'), true);
            $matricula = $body['matricula'];
            $nombre = $body['nombre'];
            $apellidos = $body['apellidos'];
            $carrera = $body['carrera'];
            $rol = $body['rol'];
            // no hay mivel
            $email = $body['email'];
            $id = '';
            $emailmooble = '';
            $perfil = '';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=email&criteria[0][value]=' . $email);
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

            if ($email == $emailmooble) {
                echo json_encode("El Usuario : [" . $nombre . "]  Esta Registrado en Moodle con Matricula : [" . $matricula . "] Con un Perfil de : " . $perfil);
            } else

            {

                $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                $new_group = array('users' => array(
                    array(
                        //'username' =>strtolower(preg_replace(['/\s+/','/^\s|\s$/'],['',''], $matricula)),
                        'username'       => $matricula,
                        'createpassword' => '1',
                        'firstname' =>  $nombre,
                        'lastname' => $apellidos,
                        //NAME
                        // 'middlename '=> 'profesor',
                        //'institution ' =>strtolower($carrera), //institution = carrera
                        'department' => strtolower($carrera), //department = nivel
                        'email' => $email,
                        'city' => 'Santiago de Queretaro',
                        'country' => 'MX',
                        'aim' => $rol
                        // 'customfields'=>array(array('value'=>'Estudiante', 'type'=>'rol'))  
                    ),
                ));

                $return = $MoodleRest->request('core_user_create_users', $new_group, MoodleRest::METHOD_POST);

                $payload = $MoodleRest->getUrl();
                if ($payload) {

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

                                'roleid'         => 5,
                                'userid'       => $id
                            ),
                        ));

                        $return = $MoodleRest->request('core_role_assign_roles', $new_group, MoodleRest::METHOD_POST);

                        $payload = $MoodleRest->getUrl();

                        if ($payload) {
                            $resultado = array(array("El Usuario : [" . $name . "] Se ha Creado con el Rol de : [" . $rol . " ]: " => " HTTP 200 OK "));
                            header("Content-Type: application/json");
                            echo json_encode($resultado);
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

                                'roleid'         => 4,
                                'userid'       => $id
                            ),
                        ));

                        $return = $MoodleRest->request('core_role_assign_roles', $new_group, MoodleRest::METHOD_POST);

                        $payload = $MoodleRest->getUrl();

                        if ($payload) {
                            $resultado = array(array("El Usuario : [" . $name . "] Se ha creado con el Rol de : [" . $rol . " ]: " => " HTTP 200 OK "));
                            header("Content-Type: application/json");
                            echo json_encode($resultado);
                        }
                    }
                }
            }
            break;

        case 'Editar_alumno':
            //obtendremos los datos de piel
            $body = json_decode(file_get_contents('php://input'), true);
            $matricula = $body['matricula'];
            $nombre = $body['nombre'];
            $apellidos = $body['apellidos'];
            $carrera = $body['carrera'];

            // no hay mivel
            $email = $body['email'];
            //2 buscamos por su correo o matricula unica para buscarlo

            if ($body) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=email&criteria[0][value]=' . $email);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $data = curl_exec($ch);
                curl_close($ch);
                $array = json_decode($data, true);
                foreach ($array['users'] as $mPersona) {
                    $id = $mPersona['id'];
                    $email = $mPersona['email'];
                }


                if ($data) { //si la consulta por id o email es true-->ejecuta el cambio
                    $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                    $new_group = array('users' => array(
                        array(
                            //'username' =>strtolower(preg_replace(['/\s+/','/^\s|\s$/'],['',''], $matricula)),
                            'id'         => $id,
                            'username'       => $matricula,
                            //suspended alumno o profesor
                            'firstname' =>  $nombre,
                            'lastname' => $apellidos,
                            // 'middlename '=> 'profesor',
                            //'institution ' =>strtolower($carrera), //institution = carrera
                            // 'department ' => $carrera, //department = nivel
                            'email' => $email

                        ),
                    ));

                    $return = $MoodleRest->request('core_user_update_users', $new_group, MoodleRest::METHOD_POST);

                    $payload = $MoodleRest->getUrl();

                    if ($payload) {
                        echo json_encode("El usuario : " . $nombre . " ' - A sido Modificado correctamente en Moodle");
                    } else {
                        echo json_encode("error de parametros de cuerpo, vuelva a intentralo");
                    }
                }
            }


            break;

        case 'buscar_alumno': // busca por matricula

            $body = json_decode(file_get_contents('php://input'), true);
            $name = $body['matricula'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_user_get_users&criteria[0][key]=username&criteria[0][value]=' . $name);
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
            $resultado = array(array("El Usuario : [" .  $fullname . "] con matricula :[" . $matricula . "] Cuenta Email [" . $email . "] De la carrera de [" . $department . "]" => " HTTP 200 OK "));
            header("Content-Type: application/json");
            echo json_encode($resultado);

            break;

        case 'create_carrera':
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
            //print_r( $data);
            $id = '';
            foreach ($ciclo  as $mPersona) {
                $id = $mPersona['idnumber'];
            }
            if ($id == $clave_carrera . '-' . $rvoe) {
                echo json_encode("la carrera : " . $carrera . "'-existe en moodle'");
            } else
            # code...

            {
                $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                $new_group = array('categories' => array(
                    array(
                        //'username' =>strtolower(preg_replace(['/\s+/','/^\s|\s$/'],['',''], $matricula)),
                        'name'         => $carrera,
                        'parent'       => 0,
                        'idnumber' =>  $clave_carrera . '-' . $rvoe,
                        'description' => $plan_estudios,
                    ),
                ));

                $return = $MoodleRest->request('core_course_create_categories', $new_group, MoodleRest::METHOD_POST);

                $payload = $MoodleRest->getUrl();

                if ($payload) {
                    echo json_encode("la carrera : " . $carrera . " ' - A sido creada correctamente en Moodle");
                }
            }



            curl_close($ch);
            break;
        case 'create_cuatrimestre':
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
            $ciclo = json_decode($data, true);
            //print_r( $data);
            $id = '';
            $idnumber = '';
            foreach ($ciclo  as $mPersona) {
                $id = $mPersona['id'];
                $idnumber = $mPersona['idnumber'];
            }

            if ($data) {


                $categy_ = json_decode(file_get_contents('php://input'), true);
                $path_ = $categy_['ciclo'] . '-' . $categy_['clave_carrera'];
                $ciclo = $categy_['ciclo'];
                $clave_carrera = $categy_['clave_carrera'];
                $modulo = $categy_['modulo'];
                $ch_ = curl_init();
                curl_setopt($ch_, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_categories&criteria[0][key]=idnumber&criteria[0][value]=' . $path_);
                curl_setopt($ch_, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_, CURLOPT_HEADER, 0);
                $data2 = curl_exec($ch_);
                $cuerpo = json_decode($data2, true);
                //print_r( $data);
                $error = '';
                foreach ($cuerpo  as $modulo_) {
                    $error = $modulo_['idnumber'];
                }
                if ($error == $ciclo . '-' . $clave_carrera) {
                    echo json_encode("la carrera : " . $modulo . "'-existe en moodle'");
                } else {
                    $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                    $new_group = array('categories' => array(
                        array(
                            //'username' =>strtolower(preg_replace(['/\s+/','/^\s|\s$/'],['',''], $matricula)),
                            'name'         => $modulo,
                            'parent'       => $id,
                            'idnumber'      =>  $path_
                            //'path'          => $path,               
                        ),
                    ));

                    $return = $MoodleRest->request('core_course_create_categories', $new_group, MoodleRest::METHOD_POST);

                    $payload = $MoodleRest->getUrl();

                    if ($payload) {
                        echo json_encode("El cuatrimestre : " . $modulo . " ' - A sido creado correctamente en Moodle");
                    }
                    curl_close($ch);
                    curl_close($ch_);
                }
            }
            break;

        case 'create_materia': ///materia

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
                        $data = array(array("Esta Materia : [" . $clave . '-' . $clavemateria = $categy[$i]['clave'] . "] ya existe en moodle " => " HTTP 404 Not Found "));
                        header("Content-Type: application/json");
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

                            $resultado = array(array("Las Materia : [" . $clave . '-' . $clavemateria = $categy[$i]['clave'] . "] han sido creada con satisfacion : " => " HTTP 200 OK "));
                            header("Content-Type: application/json");
                            echo json_encode($resultado);
                        }
                    }
                }
            }

            $response = curl_close($ch);

            break;
        case 'busqueda_curso':

            $body = json_decode(file_get_contents('php://input'), true);
            $clave = $body['clave'];
            $ciclo = $body['ciclo'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=core_course_get_courses_by_field&field=shortname&value=' . $ciclo . '-' . $clave);
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
            $resultado = array(array("Las Materia : [" . $name . "] con clave : [" . $nombreCorto . "] Del cuatrimestre : [" . $cuatrimestre . "]" => " HTTP 200 OK "));
            header("Content-Type: application/json");
            echo json_encode($resultado);
            break;

        case 'Enrolar_Alumnos':
            $body = json_decode(file_get_contents('php://input'), true);
            //$modulo = $body[0]['modulo'];
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
                            echo json_encode("El Usuario : " . $body[$i]['matriculas_alumnos'] . "' no exusten en Moodle'");
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
                                    $resultado = array(array("El Usuario : [" . $firstname . "] han sido Enrolado ala Materia: [" . $fulname . "] con clave [" . $shortname . "]" => " HTTP 200 OK "));
                                    header("Content-Type: application/json");
                                    echo json_encode($resultado);
                                } else {
                                    $resultado = array(array("El Usuario : [" . $firstname . "] no se Enrolo ala Materia : [" . $fulname . "] con clave [" . $shortname . "]" => " HTTP 404 Not Found "));
                                    header("Content-Type: application/json");
                                    echo json_encode($resultado);
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
                                        $resultado = array(array("El Usuario : [" . $firstname . "] han sido Enrolado ala Materia: [" . $fulname . "] con clave [" . $shortname . "]" => " HTTP 200 OK "));
                                        header("Content-Type: application/json");
                                        echo json_encode($resultado);
                                    } else {
                                        $resultado = array(array("El Usuario : [" . $firstname . "] no se Enrolo ala Materia : [" . $fulname . "] con clave [" . $shortname . "]" => " HTTP 404 Not Found "));
                                        header("Content-Type: application/json");
                                        echo json_encode($resultado);
                                    }
                                } else {
                                    $resultado = array(array("El Usuario : [" . $firstname . "] no se Enrolo ala Materia : [" . $fulname . "] con clave [" . $shortname . "]" => " HTTP 404 Not Found "));
                                    header("Content-Type: application/json");
                                    echo json_encode($resultado);
                                }
                            }
                        }
                    } else {
                        $resultado = array(array("El Usuario No fue Encontrado" => " HTTP 404 Not Found "));
                        header("Content-Type: application/json");
                        echo json_encode($resultado);
                    }
                }
            } else {
                $resultado = array(array("La Materia  No fue Encontrado" => " HTTP 404 Not Found "));
                header("Content-Type: application/json");
                echo json_encode($resultado);
            }




            break;
        case 'Desenrolar_Usuarios':
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
                            echo json_encode("El Usuario : " . $body[$i]['matriculas_alumnos'] . "'- Nose encuentra Enrolados'");
                        } else {
                            $MoodleRest = new MoodleRest('http://34.133.150.120/webservice/rest/server.php', '70ac31662a4bc9e66d66bb2526cafa2b');
                            $new_group = array('enrolments' => array(
                                array(

                                    //'roleid'         =>5,
                                    'userid'       => $valida,
                                    'courseid'      => $idcurse

                                ),
                            ));

                            $return = $MoodleRest->request('enrol_manual_unenrol_users', $new_group, MoodleRest::METHOD_POST);
                            $payload = $MoodleRest->getUrl();
                            if ($payload) {
                                $resultado = array(array("El Usuario : [" . $firstname . "] han sido Desenrolado de la Materia: [" . $fulname . "] con clave [" . $shortname . "]" => " HTTP 200 OK "));
                                header("Content-Type: application/json");
                                echo json_encode($resultado);
                            } else {
                                $resultado = array(array("El Usuario : [" . $firstname . "] no se Desenrolo de la  Materia : [" . $fulname . "] con clave [" . $shortname . "]" => " HTTP 404 Not Found "));
                                header("Content-Type: application/json");
                                echo json_encode($resultado);
                            }
                        }
                    }
                }
            } else {
                $resultado = array(array("La Materia  No fue Encontrado" => " HTTP 404 Not Found "));
                header("Content-Type: application/json");
                echo json_encode($resultado);
                curl_close($ch);
            }
            break;

        case 'Desabitar_Usuario': // desabilitar Usuario
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
                        echo json_encode("El Usuario : " . $body[$i]['
                                '] . "'- Nose encuentra En Moodle'");
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
                            $resultado = array(array("El Usuario : [" . $firstname . "] han sido suapendido Temporal de la Plataforma" => " HTTP 200 OK "));
                            header("Content-Type: application/json");
                            echo json_encode($resultado);
                        } else {
                            $resultado = array(array("El Usuario : [" . $firstname . "] No fue suspendido " => " HTTP 404 Not Found "));
                            header("Content-Type: application/json");
                            echo json_encode($resultado);
                        }
                    }
                }
            }
            break;
        case 'Abilitar_Usuario': // desabilitar Usuario
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
                            $resultado = array(array("El Usuario : [" . $firstname . "] han Vuelto a Activarse En la Plataforma" => " HTTP 200 OK "));
                            header("Content-Type: application/json");
                            echo json_encode($resultado);
                        } else {
                            $resultado = array(array("El Usuario : [" . $firstname . "] Sigue Suspendido " => " HTTP 404 Not Found "));
                            header("Content-Type: application/json");
                            echo json_encode($resultado);
                        }
                    }
                }
            }
        case 'Calificacion_Materia':
            $body = json_decode(file_get_contents('php://input'), true);
            $ciclo = $body['ciclo'];
            $clave = $body['clave'];
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
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://34.133.150.120/webservice/rest/server.php?wstoken=70ac31662a4bc9e66d66bb2526cafa2b&moodlewsrestformat=json&wsfunction=gradereport_user_get_grade_items&courseid=' . $idcurse);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $data = curl_exec($ch);
                curl_close($ch);

                $userfullname = '';
                $arraycurse = json_decode($data, true);
                foreach ($arraycurse['usergrades'] as $curso) {
                    foreach ($curso['gradeitems'] as $v2) {


                        $micalificacion = $v2['percentageformatted'];
                        $gradeformatted = $v2['gradeformatted'];
                    }

                    echo "\n Alumno :" . $shortname = $curso['userfullname'] . " calificacion : [" . $micalificacion . "] Con un Total de Actividades del curso " . $fulname . " :[" . $gradeformatted . "]";
                    //$resultado = array(array("Alumno :". $shortname = $curso['userfullname']. " calificacion : [".$micalificacion."] Con un Total del curso ".$fulname. " :[".$gradeformatted."]" => "HTTP 200 OK "));
                    // header("Content-Type: application/json");
                    //echo json_encode($resultado);
                }
            } else {
                $resultado = array(array("La clave de la materia Obtenida no existe " => " HTTP 404 Not Found "));
                header("Content-Type: application/json");
                echo json_encode($resultado);
            }


            break;

        default:
            $resultado = array(array("Ruta No Encontrada En Moodle para realizar Una Accion " => " HTTP 404 Not Found "));
            header("Content-Type: application/json");
            echo json_encode($resultado);
            break;
    }
}
