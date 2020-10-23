<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rawmat;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class RawmatController extends Controller
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
        return response()->json( array( 'rawmat'  => Rawmat::all() ) );
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'stock' => 'required|numeric',
            'price' => 'required|numeric',
            // 'limit' => 'required|numeric',
            'unit' => 'required',
            'restock_notif' => 'required|numeric'
        ]);
        $name = $request->input('name');
        $img = $request->input('img');
        if(!empty($img)){
            $img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace('[removed]', '', $img);
			$img = str_replace(' ', '+', $img);
            $resource = base64_decode($img);
            $prefix = Str::random(8);
            $s3name = 'image/rawmat/'.$prefix.time().'.png';
            Storage::disk('s3')->put($s3name, $resource);
            $filename = Storage::disk('s3')->url($s3name);
            // $filename = 'storage/image/rawmat/'.$prefix.time().'.png';
            // $path = storage_path().'/app/public/image/rawmat/'.$prefix.time().'.png';
            // file_put_contents($path, $resource);
        }else{
            $filename = 'noimage.png';
        }
        $rawmaterial = new Rawmat();
        $rawmaterial->name = $name;
        $rawmaterial->stock = $request->input('stock');
        $rawmaterial->price = $request->input('price');
        $rawmaterial->limit = $request->input('limit');
        $rawmaterial->unit = $request->input('unit');
        $rawmaterial->restock_notif = $request->input('restock_notif');
        $rawmaterial->img = $filename;
        $rawmaterial->user_id = Auth::user()->id;
        $rawmaterial->uuid = Str::uuid();
        $rawmaterial->save();
        return response()->json( array('success' => true) );
    }

    public function show(Request $request){
        $rawmat = Rawmat::select('*')->where('uuid', '=', $request->input('uuid'))->first();
        return response()->json( array(
            'rawmat' => $rawmat
        ));
    }

    public function update(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'stock' => 'required|numeric',
            'price' => 'required|numeric',
            // 'limit' => 'required|numeric',
            'unit' => 'required',
            'restock_notif' => 'required|numeric'
        ]);
        $rawmat = Rawmat::where('uuid', '=', $request->input('uuid'))->first();
        $name = $request->input('name');
        $img = $request->input('img');
        // $oldimg = str_replace('storage/', '', $rawmat->img);
        if(!empty($img)){
            // if(file_exists(storage_path().'/app/public/'.$oldimg)){
            //     unlink(storage_path().'/app/public/'.$oldimg);
            // }
            $img_path = parse_url($rawmat->img, PHP_URL_PATH);
            $img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace('[removed]', '', $img);
			$img = str_replace(' ', '+', $img);
            $resource = base64_decode($img);
            $prefix = Str::random(8);
            $s3name = 'image/rawmat/'.$prefix.time().'.png';
            Storage::disk('s3')->put($s3name, $resource);
            $filename = Storage::disk('s3')->url($s3name);
            // $filename = 'storage/image/rawmat/'.$prefix.time().'.png';
            // $path = storage_path().'/app/public/image/rawmat/'.$prefix.time().'.png';
            // file_put_contents($path, $resource);
            $rawmat->img = $filename;
        }
        $rawmat->name = $name;
        $rawmat->stock = $request->input('stock');
        $rawmat->price = $request->input('price');
        $rawmat->limit = $request->input('limit');
        $rawmat->unit = $request->input('unit');
        $rawmat->restock_notif = $request->input('restock_notif');
        $rawmat->user_id = Auth::user()->id;
        $rawmat->save();
        return response()->json( array('success'=>true) );
    }

    public function delete(Request $request){
        $rawmat = Rawmat::where('uuid', '=', $request->input('uuid'))->first();
        $img_path = parse_url($rawmat->img, PHP_URL_PATH);
        Storage::disk('s3')->delete($img_path);
        $rawmat->delete();
        return response()->json( array('success'=>true) );
    }

}
