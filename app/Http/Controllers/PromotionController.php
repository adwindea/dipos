<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('admin');
    }

    public function index(){
        $promotions = Promotion::where('tenant_id', Auth::user()->tenant_id)->get()->map(function($promotion){
            return [
                'uuid' => $promotion->uuid,
                'code' => $promotion->code,
                'status' => $promotion->status,
                'discount_type' => $promotion->discount_type,
                'note' => $promotion->note,
                'quantity' => decToCur($promotion->quantity),
                'amount' => decToCur($promotion->amount),
                'min_buy' => decToCur($promotion->min_buy),
                'max_discount' => decToCur($promotion->max_discount),
                'start_date' => date('d M Y', strtotime($promotion->start_date)),
                'end_date' => date('d M Y', strtotime($promotion->end_date)),
            ];
        });
        return response()->json( array( 'promotions'  => $promotions ) );
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'code' => 'required',
            'quantity' => 'required',
            'min_buy' => 'required',
            'max_discount' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        $promotion = new Promotion();
        $promotion->code = $request->input('code');
        $promotion->note = $request->input('note');
        $promotion->discount_type = $request->input('discount_type');
        $promotion->quantity = curToDec($request->input('quantity'));
        $promotion->amount = curToDec($request->input('amount'));
        $promotion->min_buy = curToDec($request->input('min_buy'));
        $promotion->max_discount = curToDec($request->input('max_discount'));
        $promotion->start_date = $request->input('start_date');
        $promotion->end_date = $request->input('end_date');
        $promotion->user_id = Auth::user()->id;
        $promotion->tenant_id = Auth::user()->tenant_id;
        $promotion->uuid = Str::uuid();
        $promotion->save();
        return response()->json( array('success' => true) );
    }

    public function show(Request $request){
        $promotion = Promotion::select('*')->where('uuid', '=', $request->input('uuid'))->first();
        $promotion = [
            'uuid' => $promotion->uuid,
            'code' => $promotion->code,
            'status' => $promotion->status,
            'discount_type' => $promotion->discount_type,
            'note' => $promotion->note,
            'quantity' => decToCur($promotion->quantity),
            'amount' => decToCur($promotion->amount),
            'min_buy' => decToCur($promotion->min_buy),
            'max_discount' => decToCur($promotion->max_discount),
            'start_date' => date('Y-m-d', strtotime($promotion->start_date)),
            'end_date' => date('Y-m-d', strtotime($promotion->end_date)),            
        ];
        return response()->json( array(
            'promotion' => $promotion
        ));
    }

    public function update(Request $request){
        $validatedData = $request->validate([
            'code' => 'required',
            'quantity' => 'required',
            'min_buy' => 'required',
            'max_discount' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        $uuid = $request->input('uuid');
        $promotion = Promotion::where('uuid', '=', $uuid)->first();
        $promotion->code = $request->input('code');
        $promotion->note = $request->input('note');
        $promotion->discount_type = $request->input('discount_type');
        $promotion->quantity = curToDec($request->input('quantity'));
        $promotion->amount = curToDec($request->input('amount'));
        $promotion->min_buy = curToDec($request->input('min_buy'));
        $promotion->max_discount = curToDec($request->input('max_discount'));
        $promotion->start_date = $request->input('start_date');
        $promotion->end_date = $request->input('end_date');
        $promotion->save();
        return response()->json( array('success' => true) );
    }

    public function delete(Request $request){
        $promotion = Promotion::where('uuid', '=', $request->input('uuid'))->first();
        $promotion->status = false;
        $promotion->save();
        $promotion->delete();
        return response()->json( array('success'=>true) );
    }

}
