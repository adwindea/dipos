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
        $promotions = Promotion::all();
        if(!empty($promotions)){
            foreach($promotions as $key => $value){
                $promotions[$key]['amount'] = number_format($value['amount'], 2, ',', '.');
                $promotions[$key]['quantity'] = number_format($value['quantity'], 0, ',', '.');
                $promotions[$key]['min_buy'] = number_format($value['min_buy'], 0, ',', '.');
                $promotions[$key]['max_discount'] = number_format($value['max_discount'], 0, ',', '.');
                $promotions[$key]['start_date'] = date('d M Y', strtotime($value['start_date']));
                $promotions[$key]['end_date'] = date('d M Y', strtotime($value['end_date']));
            }
            return response()->json( array( 'promotions'  => $promotions ) );
        }
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'code' => 'required',
            'quantity' => 'required|numeric',
            'min_buy' => 'required|numeric',
            'max_discount' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        $promotion = new Promotion();
        $promotion->code = $request->input('code');
        $promotion->note = $request->input('note');
        $promotion->discount_type = $request->input('discount_type');
        $promotion->quantity = $request->input('quantity');
        $promotion->amount = $request->input('amount');
        $promotion->min_buy = $request->input('min_buy');
        $promotion->max_discount = $request->input('max_discount');
        $promotion->start_date = $request->input('start_date');
        $promotion->end_date = $request->input('end_date');
        $promotion->user_id = Auth::user()->id;
        $promotion->uuid = Str::uuid();
        $promotion->save();
        return response()->json( array('success' => true) );
    }

    public function show(Request $request){
        $promotion = Promotion::select('*')->where('uuid', '=', $request->input('uuid'))->first();
        $promotion->amount = number_format($promotion->amount, 2, '.', '');
        $promotion->quantity = number_format($promotion->quantity, 0, '', '');
        $promotion->min_buy = number_format($promotion->min_buy, 0, '', '');
        $promotion->max_discount = number_format($promotion->max_discount, 0, '', '');
        return response()->json( array(
            'promotion' => $promotion
        ));
    }

    public function update(Request $request){
        $validatedData = $request->validate([
            'code' => 'required',
            'quantity' => 'required|numeric',
            'min_buy' => 'required|numeric',
            'max_discount' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        $uuid = $request->input('uuid');
        $promotion = Promotion::where('uuid', '=', $uuid)->first();
        $promotion->code = $request->input('code');
        $promotion->note = $request->input('note');
        $promotion->discount_type = $request->input('discount_type');
        $promotion->quantity = $request->input('quantity');
        $promotion->amount = $request->input('amount');
        $promotion->min_buy = $request->input('min_buy');
        $promotion->max_discount = $request->input('max_discount');
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
