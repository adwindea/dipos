<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(){
        return response()->json( array( 'categories'  => Category::all() ) );
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'name' => 'required'
        ]);
        $name = $request->input('name');
        $img = $request->input('img');
        if(!empty($img)){
            $img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace('[removed]', '', $img);
			$img = str_replace(' ', '+', $img);
            $resource = base64_decode($img);
            $prefix = Str::random(8);
            $filename = '/storage/image/category/'.$prefix.time().'.png';
            $path = storage_path().'/app/public/image/category/'.$prefix.time().'.png';
            file_put_contents($path, $resource);
        }else{
            $filename = '/image/noimage.png';
        }
        $category = new Category();
        $category->name = $name;
        $category->img = $filename;
        $category->user_id = Auth::user()->id;
        $category->uuid = Str::uuid();
        $category->save();
        return response()->json( array('success' => true) );
    }

    public function show(Request $request){
        $category = Category::select('*')->where('uuid', '=', $request->input('uuid'))->first();
        return response()->json( array(
            'category' => $category
        ));
    }

    public function update(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
        ]);
        $category = Category::where('uuid', '=', $request->input('uuid'))->first();
        $name = $request->input('name');
        $img = $request->input('img');
        $oldimg = str_replace('storage/', '', $category->img);
        if(!empty($img)){
            if(file_exists(storage_path().'/app/public/'.$oldimg)){
                unlink(storage_path().'/app/public/'.$oldimg);
            }
            $img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace('[removed]', '', $img);
			$img = str_replace(' ', '+', $img);
            $resource = base64_decode($img);
            $prefix = Str::random(8);
            $filename = '/storage/image/category/'.$prefix.time().'.png';
            $path = storage_path().'/app/public/image/category/'.$prefix.time().'.png';
            file_put_contents($path, $resource);
            $category->img = $filename;
        }
        $category->name = $name;
        $category->user_id = Auth::user()->id;
        $category->save();
        return response()->json( array('success'=>true) );
    }

    public function delete(Request $request){
        $category = Category::where('uuid', '=', $request->input('uuid'))->first();
        $category->delete();
        return response()->json( array('success'=>true) );
    }


}
