<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\User;
use App\Category;

class PruebasController extends Controller
{
    public function index(){
        $titulo = 'animales';
        $animales = ['perro', 'gato', 'conejo'];
        return view('pruebas.index', array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }
    
    public function testORM(){
        /*$posts = Post::all();
        foreach($posts as $post){
            echo "<h1>{$post->user->name} - {$post->category->name}</h1>";
        }*/
        
        $categories = Category::all();
        foreach($categories as $category){
            echo "<h1>{$category}</h1>";
            foreach($category->posts as $post)
            echo "<h2>{$post}</h2>";
        }
        die();
    }
}
