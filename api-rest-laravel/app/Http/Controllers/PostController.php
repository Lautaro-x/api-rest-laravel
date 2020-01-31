<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller {

    function __construct() {
        $this->middleware('ApiAuthMiddleware', ['except' => ['index', 'show', 'getImage', 'getPostsByUser', 'getPostsByCategory']]);
    }

    public function Index() {
        $posts = Post::all()->load('category');

        return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'message' => $posts
        ]);
    }

    public function Show($id) {
        $post = Post::find($id)->load(['category', 'user']);
        if (is_object($post)) {
            $data = array(
                'status' => 'succsess',
                'code' => 200,
                'message' => $post
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se han encontrado post'
            );
        }
        return Response()->json($data, $data['code']);
    }

    public function Store(Request $request) {
        //RECOGER DATOS POR POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //CONSEGUIR USUARIO IDENTIFICADO
            $jwtAuth = new JwtAuth;
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);

            //VALIDAR DATOS
            $validate = \Validator::make($params_array, [
                        'title' => 'required|alpha',
                        'content' => 'required|alpha',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'error al guardar el post',
                    'errores' => $validate->errors()
                );
            } else {
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;

                $post->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => $post
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'no se recivió información'
            );
        }
        //DEVOLVER RESPUESTA
        return response()->json($data, $data['code']);
    }

    public function Update($id, Request $request) {
        //RECOGER LOS DATOS POR POST

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //return Response()->json($params_array);
            //VALIDAR LOS DATOS
            $validate = \Validator::make($params_array, []);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Errores en los datos',
                    'errors' => $validate->errors()
                );
            } else {
                //SELECCIONO SOLO LOS DATOS QUE QUIERO/SE PUEDEN ACTUALIZAR
                $params_corrected = array();
                if (isset($params_array['title']))
                    $params_corrected['title'] = $params_array['title'];
                if (isset($params_array['content']))
                    $params_corrected['content'] = $params_array['content'];
                if (isset($params_array['category_id']))
                    $params_corrected['category_id'] = $params_array['category_id'];

                //ACTUALIZAR EL REGISTRO
                $jwtAuth = new JwtAuth;
                $token = $request->header('Authorization', null);
                $user = $jwtAuth->checkToken($token, true);

                $post = Post::where([
                            'id' => $id,
                            'user_id' => $user->sub
                ]);
                if (!empty($post)) {
                    $post->update($params_corrected);

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => $params_corrected
                    );
                } else {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'No tienes permitido modificar el post'
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'no se recivió información'
            );
        }

        //DEVOLVER DATOS
        return Response()->json($data, $data['code']);
    }

    public function Destroy($id, Request $request) {
        //OBTENER EL USUARIO

        $jwtAuth = new JwtAuth;
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        //OBTENER EL REGISTRO
        $post = Post::where([
                    'id' => $id,
                    'user_id' => $user->sub
                ])->first();
        if (!empty($post)) {
            //BORRAR
            $post->delete();

            //RESPUESTA
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => $post
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No existe el post'
            );
        }
        return Response()->json($data, $data['code']);
    }

    public function Upload(Request $request) {
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
            \Storage::disk('post')->put($image_name, \File::get($image));
            $data = array(
                'status' => 'correcto',
                'code' => 200,
                'message' => 'Imagen subida correctamente'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        //COMPROVAR SI EXISTE EL FICHERO
        $isset = \Storage::disk('post')->exists($filename);

        if ($isset) {
            //CONSEGUIR LA IMAGEN
            $file = \Storage::disk('post')->get($filename);
            //DEVOLVER LA IMAGEN
            return new Response($file, 200);
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'no se ha encontrado imagen'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getPostsByCategory($id) {
        $posts = Post::where('category_id', $id)->get();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'message' => $posts
        );
        
        return response()->json($data, $data['code']);
    }
    
    public function getPostsByUser($id) {
        $posts = Post::where('user_id', $id)->get();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'message' => $posts
        );
        
        return response()->json($data, $data['code']);
    }

}
