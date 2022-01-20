<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rawmat;
use App\Models\RawmatLog;
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
        $rawmats = Rawmat::where('tenant_id', Auth::user()->tenant_id)
        ->get()
        ->map(function($rawmat, $key){
            return [
                'name' => $rawmat->name,
                'stock' => decToCur($rawmat->stock),
                'limit' => decToCur($rawmat->limit),
                'unit' => $rawmat->unit,
                'price' => decToCur($rawmat->price),
                'restock_notif' => $rawmat->restock_notif,
                'img' => $rawmat->img,
                'uuid' => $rawmat->uuid,
            ];
        });
        return response()->json( array( 'rawmat'  => $rawmats ) );
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'stock' => 'required',
            'price' => 'required',
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
            $s3name = 'public/image/rawmat/'.$prefix.time().'.png';
            Storage::disk('local')->put($s3name, $resource);
            $filename = Storage::disk('local')->url($s3name);
        }else{
            $filename = Storage::disk('local')->url('public/image/noimage.png');
        }
        $rawmaterial = new Rawmat();
        $rawmaterial->name = $name;
        $rawmaterial->stock = curToDec($request->input('stock'));
        $rawmaterial->price = curToDec($request->input('price'));
        $rawmaterial->limit = curToDec($request->input('limit'));
        $rawmaterial->unit = $request->input('unit');
        $rawmaterial->restock_notif = $request->input('restock_notif');
        $rawmaterial->img = $filename;
        $rawmaterial->user_id = Auth::user()->id;
        $rawmaterial->tenant_id = Auth::user()->tenant_id;
        $rawmaterial->uuid = Str::uuid();
        $rawmaterial->save();
        return response()->json( array('success' => true) );
    }

    public function show(Request $request){
        $rawmat = Rawmat::where('uuid', '=', $request->input('uuid'))->first();
        $rawmat = [
            'name' => $rawmat->name,
            'stock' => decToCur($rawmat->stock),
            'limit' => decToCur($rawmat->limit),
            'unit' => $rawmat->unit,
            'price' => decToCur($rawmat->price),
            'restock_notif' => $rawmat->restock_notif,
            'img' => $rawmat->img,
            'uuid' => $rawmat->uuid,
        ];
        return response()->json( array(
            'rawmat' => $rawmat
        ));
    }

    public function update(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'stock' => 'required',
            'price' => 'required',
            'unit' => 'required',
            'restock_notif' => 'required|numeric'
        ]);
        $rawmat = Rawmat::where('uuid', '=', $request->input('uuid'))->first();
        $name = $request->input('name');
        $img = $request->input('img');
        if(!empty($img)){
            $img_path = storage_path().'/app/public'.(str_replace('storage/', '', $rawmat->img));
            if(file_exists($img_path) && $img_path != storage_path().'/app/public/image/noimage.png'){
                unlink($img_path);
            }
            $img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace('[removed]', '', $img);
			$img = str_replace(' ', '+', $img);
            $resource = base64_decode($img);
            $prefix = Str::random(8);
            $s3name = 'public/image/rawmat/'.$prefix.time().'.png';
            Storage::disk('local')->put($s3name, $resource);
            $filename = Storage::disk('local')->url($s3name);
            $rawmat->img = $filename;
        }
        $rawmat->name = $name;
        $rawmat->stock = curToDec($request->input('stock'));
        $rawmat->price = curToDec($request->input('price'));
        $rawmat->limit = curToDec($request->input('limit'));
        $rawmat->unit = $request->input('unit');
        $rawmat->restock_notif = $request->input('restock_notif');
        $rawmat->user_id = Auth::user()->id;
        $rawmat->save();
        return response()->json( array('success'=>true) );
    }

    public function delete(Request $request){
        $rawmat = Rawmat::where('uuid', '=', $request->input('uuid'))->first();
        $img_path = storage_path().'/app/public'.(str_replace('storage/', '', $rawmat->img));
        if(file_exists($img_path) && $img_path != storage_path().'/app/public/image/noimage.png'){
            unlink($img_path);
        }
        $rawmat->delete();
        return response()->json( array('success'=>true) );
    }

    public function restock(Request $request){
        $rawmat_uuid = $request->input('uuid');
        $restock_quantity = curToDec($request->input('quantity'));
        $price_total = curToDec($request->input('price_total'));
        $note = $request->input('note');

        $rawmat = Rawmat::where('uuid', '=', $rawmat_uuid)->first();

        $restock = new RawmatLog;
        $restock->rawmat_id = $rawmat->id;
        $restock->status = 2;
        $restock->quantity = $restock_quantity;
        $restock->price_total = $price_total;
        $restock->note = $note;
        $restock->user_id = Auth::user()->id;
        $restock->uuid = Str::uuid();
        $restock->save();

        $current_price = $rawmat->stock*$rawmat->price;
        $end_stock = $rawmat->stock + $restock_quantity;
        $end_price = ($current_price+$price_total)/$end_stock;
        $rawmat->stock = $end_stock;
        $rawmat->price = $end_price;
        $rawmat->save();

        return response()->json( array('success'=>true) );
    }
}
