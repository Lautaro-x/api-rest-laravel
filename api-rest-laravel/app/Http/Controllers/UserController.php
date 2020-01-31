<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller {

    function Pruebas(Request $request) {
        return 'Prueba de USER CONTROLLER';
    }

    function Register(Request $request) {


        //RECOJER DATOS DE USUARIO

        $json = $request->input('json', null);
        $params = json_decode($json, true);
        $params = array_map('trim', $params);

        //VERIFICAR DATOS

        $validate = \Validator::make($params, [
                    'name' => "required|alpha",
                    'surname' => "required|alpha",
                    'email' => "required|email|unique:users", //unique asegura que el email sea unico en la tabla :tabla (users) ... el nombre del campo (email) debe ser el mismo nombre que el del array.
                    'password' => "required"
        ]);

        if ($validate->fails()) {
            /* la validacion fallo */
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha creado',
                'errors' => $validate->errors()
            );
        } else {
            /* La validación es correcta */
            $pwd = password_hash($params['password'], PASSWORD_BCRYPT, ['cost' => 4]);

            $user = new User();
            $user->name = $params['name'];
            $user->surname = $params['surname'];
            $user->email = $params['email'];
            $user->password = $pwd;
            $user->role = 'ROLE_USER';

            //GUARDAR USUARIO
            if ($user->save()) {
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado',
                    'user' => $user
                );
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado por no haberse guardado, intentelo otra vez'
                );
            }
        }


        //RESPUESTA DEL SERVIDOR 
        return response()->json($data, $data['code']);
    }

    function Login(Request $request) {
        $jwtAuth = new \JwtAuth();
        // RECIVIR DATOS POR POST

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        // VALIDAR DATOS

        $validate = \Validator::make($params_array, [
                    'email' => "required|email",
                    'password' => "required"
        ]);


        //

        if ($validate->fails()) {
            /* la validacion fallo */
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            /* La validacion es correcta */
            if (empty($params->gettoken)) {
                $response = $jwtAuth->signUp($params->email, $params->password);
            } else {
                $response = $jwtAuth->signUp($params->email, $params->password, true);
            }

            if ($response) {
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => $response  //tocken
                );
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El email y la contraseña no corresponden a ningún usuario.'
                );
            }
        }

        return response()->json($data, $data['code']);
    }

    public function Update(Request $request) {
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $modifier = $jwtAuth->checkToken($token, true);

        $json = $request->input('json', null);
        // La persona que está logueada quiere hacer un update de informacion sobre un usuario. 
        // Verifico antes si esta persona ,desde ahora mofifier, es administrador o el mismo usuario.
        if ($modifier) {
            if (empty($json)) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'no hay datos para modificar'
                );
            } else {
                $params = json_decode($json);

                if (empty($params->id)) {
                    $params->id = $modifier->sub;
                }

                $params_array = json_decode($json, true);

                $modifier_role = User::where('id', $modifier->sub)->first()->role;
                if ($modifier_role === "ROLE_ADMIN" || $modifier->sub == $params->id) {
                    //VALIDAR DATOS                
                    $validate = \Validator::make($params_array, [
                                'name' => "alpha",
                                'surname' => "alpha",
                                'email' => "email|unique:users,email," . $params->id
                    ]);
                    if ($validate->fails()) {
                        $data = array(
                            'status' => 'error',
                            'code' => 404,
                            'message' => 'El usuario no se ha podido modificar',
                            'errors' => $validate->errors()
                        );
                    } else {
                        //COMIENZO LA ACTUALIZACION

                        if (isset($params_array['name']))
                            $params_ok['name'] = $params_array['name'];
                        if (isset($params_array['surname']))
                            $params_ok['surname'] = $params_array['surname'];
                        if (isset($params_array['email']))
                            $params_ok['email'] = $params_array['email'];


                        $user_update = User::where('id', $params->id)->update($params_ok);
                        //FIN DE ACTUALIZACION

                        if ($modifier->sub == $params->id) {
                            $data = array(
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'Usuario actualizado',
                                'changes' => $params_ok,
                                'newAuth' => $jwtAuth->signUp($params->email, $params->password, true)
                            );
                        } else {
                            $data = array(
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'Usuario actualizado',
                                'changes' => $params_ok
                            );
                        }
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'Acceso denegado.'
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario que intenta hacer la modificación identificado correctamente'
            );
        }
        return response()->json($data, $data['code']);
    }

    //Verifica si el tocken es correcto y de quien si se pide identidad.
    public function CheckUserTocken(Request $request) {

        $token = $request->header("Authorization");
        $params = json_decode($request->input('json', null));

        $jwtAuth = new \JwtAuth();

        if (empty($params->getIdentity)) {
            $checkTocken = $jwtAuth->checkToken($token);
        } else {
            $checkTocken = $jwtAuth->checkToken($token, $params->getIdentity);
        }

        return response()->json($checkTocken);
    }

    public function UploadImage(Request $request) {


        //Guardar imagen de usuario
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Error al subir imagen',
                'errors' => $validate->errors()
            );
        } else {
            $image = $request->file('file0');
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('user')->put($image_name, \File::get($image));
            $data = array(
                'status' => 'correcto',
                'code' => 200,
                'message' => 'Imagen subida correctamente'
            );
        }

        return response()->json($data, $data['code']);
    }
    
    public function getImage($filename){
        $isset = \Storage::disk('user')->exists($filename);
        if($isset){
            $file = \Storage::disk('user')->get($filename);
            return new Response($file, 200);
        }
        else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'imagen no encontrada'
            );
            return response()->json($data, $data['code']);
        }        
    }
    
    public function detail($id){
        $user = User::find($id);
        if(is_object($user)){
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => $user
            );
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'imagen no encontrada'
            );
        }
        return response()->json($data, $data['code']);
    }
}
