<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Rawmat;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(){
        $products = Product::with('category', 'ingredients', 'ingredients.rawmat')
        ->where('tenant_id', Auth::user()->tenant_id)
        ->get()
        ->map(function($product, $key){
            $capital = decToCur($product->capital);
            if($product->use_rawmat){
                $capital = $product->ingredients->map(function($ingredient, $key){
                    return $ingredient->quantity*$ingredient->rawmat->price;
                });
                $capital = decToCur($capital->sum());
            }
            return [
                'name' => $product->name,
                'category' => $product->category->name,
                'description' => $product->description,
                'price' => decToCur($product->price),
                'capital' => $capital,
                'unit' => $product->unit,
                'img' => $product->img,
                'use_rawmat' => $product->use_rawmat,
                'uuid' => $product->uuid,
            ];
        });
        return response()->json( array( 'products'  => $products ) );
    }

    public function create(){
        $categories = Category::all()->map(function($category, $key){
            return [
                'label' => $category->name,
                'value' => $category->uuid,
            ];
        });
        $categories->prepend(['label' => 'Select category', 'value' => '']);
        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function store(Request $request){
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'category' => 'required',
        ];
        if(!$request->input('use_rawmat')){
            $rules['capital'] = 'required';
        }
        $validatedData = $request->validate($rules);
        $category_uuid = $request->input('category');
        $category = Category::where('uuid', '=', $category_uuid)->first();
        $img = $request->input('img');
        if(!empty($img)){
            $img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace('[removed]', '', $img);
			$img = str_replace(' ', '+', $img);
            $resource = base64_decode($img);
            $prefix = Str::random(8);
            $s3name = 'image/product/'.$prefix.time().'.png';
            Storage::disk('s3')->put($s3name, $resource);
            $filename = Storage::disk('s3')->url($s3name);
            // $filename = 'storage/image/rawmat/'.$prefix.time().'.png';
            // $path = storage_path().'/app/public/image/rawmat/'.$prefix.time().'.png';
            // file_put_contents($path, $resource);
        }else{
            $filename = Storage::disk('s3')->url('image/noimage.png');
        }
        $product = new Product();
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->category_id = $category->id;
        $product->price = curToDec($request->input('price'));
        $product->capital = curToDec($request->input('capital'));
        $product->img = $filename;
        $product->user_id = Auth::user()->id;
        $product->tenant_id = Auth::user()->tenant_id;
        $product->uuid = Str::uuid();
        $product->save();
        return response()->json( array('success' => true) );
    }

    public function show(Request $request){
        $product = Product::with('category')
        ->where('uuid', $request->input('uuid'))
        ->first();
        $product = [
            'category' => $product->category->uuid,
            'name' => $product->name,
            'description' => $product->description,
            'price' => decToCur($product->price),
            'use_rawmat' => $product->use_rawmat,
            'capital' => decToCur($product->capital),
        ];
        return response()->json( array(
            'product' => $product
        ));
    }

    public function update(Request $request){
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'category' => 'required',
        ];
        if(!$request->input('use_rawmat')){
            $rules['capital'] = 'required';
        }
        $validatedData = $request->validate($rules);
        $product = Product::where('uuid', '=', $request->input('uuid'))->first();
        $img = $request->input('img');
        if(!empty($img)){
            $img_path = parse_url($product->img, PHP_URL_PATH);
            if($img_path != '/image/noimage.png'){
                Storage::disk('s3')->delete($img_path);
            }
            $img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace('[removed]', '', $img);
			$img = str_replace(' ', '+', $img);
            $resource = base64_decode($img);
            $prefix = Str::random(8);
            $s3name = 'image/product/'.$prefix.time().'.png';
            Storage::disk('s3')->put($s3name, $resource);
            $filename = Storage::disk('s3')->url($s3name);
            $product->img = $filename;
        }
        if(!$request->input('use_rawmat')){
            $ingredients = Ingredient::where('product_id', $product->id)->delete();
        }
        $category_uuid = $request->input('category');
        $category = Category::where('uuid', '=', $category_uuid)->first();
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->category_id = $category->id;
        $product->price = curToDec($request->input('price'));
        $product->capital = curToDec($request->input('capital'));
        $product->use_rawmat = $request->input('use_rawmat');
        $product->user_id = Auth::user()->id;
        $product->save();
        return response()->json( array('success'=>true) );
    }

    public function delete(Request $request){
        $product = Product::where('uuid', '=', $request->input('uuid'))->first();
        $img_path = parse_url($product->img, PHP_URL_PATH);
        if($img_path != '/image/noimage.png'){
            Storage::disk('s3')->delete($img_path);
        }
        $ingredient = Ingredient::where('product_id', $product->id)->delete();
        $product->delete();
        return response()->json( array('success'=>true) );
    }

    public function rawmatData(Request $request){
        $searchKey = $request->input('searchKey');
        $rawmats = Rawmat::where('name', 'like', '%'.$searchKey.'%')->orderBy('name')->paginate(10);
        $rawmats = tap($rawmats, function ($rawmatInstance){
            return $rawmatInstance->getCollection()->transform(function($rawmat){
                return [
                    'name' => $rawmat->name,
                    'unit' => $rawmat->unit,
                    'uuid' => $rawmat->uuid,
                ];
            });
        });
        return response()->json($rawmats);
    }

    public function getIngredient(Request $request){
        $uuid = $request->input('uuid');
        $product = Product::where('uuid', '=', $uuid)->first();
        $ingredients = Ingredient::with('rawmat')
            ->where('product_id', '=', $product->id)
            ->paginate(10);
        $ingredients = tap($ingredients, function ($ingredientInstance){
            return $ingredientInstance->getCollection()->transform(function($ingredient){
                return [
                    'name' => $ingredient->rawmat->name,
                    'quantity' => decToCur($ingredient->quantity),
                    'uuid' => $ingredient->uuid
                ];
            });
        });
        return response()->json($ingredients);
    }

    public function insertIngredient(Request $request){
        $product_uuid = $request->input('product_uuid');
        $rawmat_uuid = $request->input('rawmat_uuid');
        $product = Product::where('uuid', '=', $product_uuid)->first();
        $rawmat = Rawmat::where('uuid', '=', $rawmat_uuid)->first();
        $ingredient = Ingredient::where('product_id', '=', $product->id)->where('rawmat_id', '=', $rawmat->id)->first();
        if($ingredient === null){
            $ingredient = new Ingredient(['product_id'=>$product->id]);
            $ingredient->rawmat_id = $rawmat->id;
            $ingredient->quantity = 1;
            $ingredient->user_id = Auth::user()->id;
            $ingredient->tenant_id = Auth::user()->tenant_id;
            $ingredient->uuid = Str::uuid();
        }else{
            $ingredient->quantity = $ingredient->quantity + 1;
        }
        $ingredient->save();
        return response()->json( array('success'=>true) );
    }

    public function updateIngredient(Request $request){
        $uuid = $request->input('uuid');
        $quantity = curToDec($request->input('quantity'));
        $ingredient = Ingredient::where('uuid', '=', $uuid);
        if($quantity == 0){
            $ingredient->delete();
        }else{
            $ingredient->update(['quantity'=>$quantity]);
        }
        return response()->json( array('success'=>true) );
    }
}
