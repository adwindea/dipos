<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


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
        $categories = Category::where('tenant_id', Auth::user()->tenant_id)
        ->get()
        ->map(function($category, $key){
            return [
                'name' => $category->name,
                'uuid' => $category->uuid,
                'img' => $category->img,
            ];
        });
        return response()->json( array( 'categories'  => $categories, 'path' => storage_path()) );
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
            $s3name = 'public/image/category/'.$prefix.time().'.png';
            Storage::disk('local')->put($s3name, $resource);
            $filename = Storage::disk('local')->url($s3name);
        }else{
            $filename = Storage::disk('local')->url('public/image/noimage.png');
        }
        $category = new Category();
        $category->name = $name;
        $category->img = $filename;
        $category->user_id = Auth::user()->id; 
        $category->tenant_id = Auth::user()->tenant_id;
        $category->uuid = Str::uuid();
        $category->save();
        return response()->json( array('success' => true) );
    }

    public function show(Request $request){
        $category = Category::where('uuid', '=', $request->input('uuid'))->first();
        $category = [
            'name' => $category->name,
            'uuid' => $category->uuid,
            'img' => $category->img,
        ];
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
        if(!empty($img)){
            $img_path = storage_path().'/app/public'.(str_replace('storage/', '', $category->img));
            if(file_exists($img_path) && $img_path != storage_path().'/app/public/image/noimage.png'){
                unlink($img_path);
            }
            $img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace('[removed]', '', $img);
			$img = str_replace(' ', '+', $img);
            $resource = base64_decode($img);
            $prefix = Str::random(8);
            $s3name = 'public/image/category/'.$prefix.time().'.png';
            Storage::disk('local')->put($s3name, $resource);
            $filename = Storage::disk('local')->url($s3name);
            $category->img = $filename;
        }
        $category->name = $name;
        $category->user_id = Auth::user()->id;
        $category->save();
        return response()->json( array('success'=>true) );
    }

    public function delete(Request $request){
        $category = Category::where('uuid', '=', $request->input('uuid'))->first();
        $img_path = storage_path().'/app/public'.(str_replace('storage/', '', $category->img));
        if(file_exists($img_path) && $img_path != storage_path().'/app/public/image/noimage.png'){
            unlink($img_path);
        }
        $category->delete();
        return response()->json( array('success'=>true) );
    }


}
