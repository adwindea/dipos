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
        $products = Product::select('products.*', 'categories.name as category')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->get();
        return response()->json( array( 'products'  => $products ) );
    }

    public function create(){
        return response()->json([
            'categories' => Category::select('categories.name as label', 'categories.uuid as value')->get(),
        ]);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category' => 'required'
        ]);
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
        $product->category_id = $category->id;
        $product->price = $request->input('price');
        $product->img = $filename;
        $product->user_id = Auth::user()->id;
        $product->uuid = Str::uuid();
        $product->save();
        return response()->json( array('success' => true) );
    }

    public function show(Request $request){
        $product = Product::join('categories', 'categories.id', '=', 'products.category_id')
        ->select('products.*', 'categories.uuid as category')
        ->where('products.uuid', '=', $request->input('uuid'))->first();
        return response()->json( array(
            'product' => $product
        ));
    }

    public function update(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category' => 'required'
        ]);
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
        $category_uuid = $request->input('category');
        $category = Category::where('uuid', '=', $category_uuid)->first();
        $product->name = $request->input('name');
        $product->category_id = $category->id;
        $product->price = $request->input('price');
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
        $product->delete();
        return response()->json( array('success'=>true) );
    }

    public function rawmatData(Request $request){
        $searchKey = $request->input('searchKey');
        $rawmat = Rawmat::where('name', 'like', '%'.$searchKey.'%')->orderBy('name')->paginate(10);
        return response()->json($rawmat);
    }

    public function getIngredient(Request $request){
        $uuid = $request->input('uuid');
        $product = Product::where('uuid', '=', $uuid)->first();
        $ingredients = Ingredient::where('ingredients.product_id', '=', $product->id)
            ->select('rawmats.name as name', 'ingredients.quantity as quantity', 'ingredients.uuid as uuid')
            ->leftJoin('rawmats', 'rawmats.id', '=', 'ingredients.rawmat_id')
            ->orderBy('rawmats.name')
            ->paginate(10);
        return response()->json($ingredients);
    }

    public function insertIngredient(Request $request){
        $product_uuid = $request->input('product_uuid');
        $rawmat_uuid = $request->input('rawmat_uuid');
        $product = Product::select('*')->where('uuid', '=', $product_uuid)->first();
        $rawmat = Rawmat::select('*')->where('uuid', '=', $rawmat_uuid)->first();
        $ingredient = Ingredient::where('product_id', '=', $product->id)->where('rawmat_id', '=', $rawmat->id)->first();
        if($ingredient === null){
            $ingredient = new Ingredient(['product_id'=>$product->id]);
            $ingredient->rawmat_id = $rawmat->id;
            $ingredient->quantity = 1;
            $ingredient->user_id = Auth::user()->id;
            $ingredient->uuid = Str::uuid();
        }else{
            $ingredient->quantity = $ingredient->quantity + 1;
        }
        $ingredient->save();
        return response()->json( array('success'=>true) );
    }

    public function updateIngredient(Request $request){
        $uuid = $request->input('uuid');
        $quantity = $request->input('quantity');
        $ingredient = Ingredient::where('uuid', '=', $uuid);
        if($quantity == 0){
            $ingredient->delete();
        }else{
            $ingredient->update(['quantity'=>$quantity]);
        }
        return response()->json( array('success'=>true) );
    }
}
