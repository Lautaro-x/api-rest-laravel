<?php
/**/
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller {

    function __construct() {
        $this->middleware('ApiAuthMiddleware', ['except' => ['index', 'show']]);
    }

    function Index(Request $request) {
        $categories = Category::all();
        return response()->json([
                    'status' => 'succsess',
                    'code' => 200,
                    'message' => $categories
        ]);
    }

    function Show($id) {
        $category = Category::find($id);
        if (is_object($category)) {
            $data = array(
                'status' => 'succsess',
                'code' => 200,
                'message' => $category
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se han encontrado categorias'
            );
        }
        return Response()->json($data, $data['code']);
    }

    function Store(Request $request) {
        //RECOGER LOS DATOS POR POST

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //VALIDAR LOS DATOS

            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);

            //GUARDAR LA CATEGORIA

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha guardado la categoria',
                    'errors' => $validate->errors()
                );
            } else {

                $category = new Category();
                $category->name = $params->name;
                $category->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => $category
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se recivió información'
            );
        }


        //DEVOLVER LOS DATOS

        return Response()->json($data, $data['code']);
    }

    public function Update($id, Request $request) {

        //RECOGER LOS DATOS POR POST

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            //VALIDAR LOS DATOS
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Errores en los datos', 
                    'errors' => $validate->errors()
                );
            } else {
                //SELECCIONO SOLO LOS DATOS QUE QUIERO/SE PUEDEN ACTUALIZAR
                $params_corrected = array(
                    'name' => $params->name
                );

                //ACTUALIZAR EL REGISTRO
                $category = Category::where('id', $id)->update($params_corrected);

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => $params_corrected
                );
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

}
