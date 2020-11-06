<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\RawmatLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // $this->middleware('admin');
    }

    public function index(){
        $prefix = date('yWN');
        $order = Order::where('order_number', 'like', $prefix.'%')
        ->where('status', '=', 0)
        ->orderByDesc('id')
        ->first();
        if(empty($order)){
            $order = new Order();
            $order->order_number = $prefix.'0001';
            $order->user_id = Auth::user()->id;
            $order->uuid = Str::uuid();
            $order->save();
        }
        return response()->json( array(
            'order'  => $order
        ));
    }

    public function orderItems(Request $request){
        $uuid = $request->input('uuid');
        $order = Order::where('uuid', '=', $uuid)->first();
        $order_items = OrderLog::join('products', 'products.id', '=', 'order_logs.product_id')
        ->select('order_logs.*', 'products.name as name', 'products.price as price')
        ->where('order_id', '=', $order->id)
        ->paginate(10);
        if(!empty($order_items)){
            foreach($order_items as $key => $value){
                $order_items[$key]['quantity'] = number_format($value['quantity'], 0, '', '');
            }
        }
        return response()->json( $order_items );
    }

    public function saveQuantity(Request $request){
        $uuid = $request->input('uuid');
        $quantity = $request->input('quantity');
        $orderlog = OrderLog::where('uuid', '=', $uuid)->first();
        $old_qty = $orderlog->quantity;
        $orderlog->quantity = $quantity;
        $orderlog->save();
        //update rawmatlog
        return response()->json( array('success'=>true) );
    }
}
