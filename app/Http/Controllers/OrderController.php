<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Rawmat;
use App\Models\OrderLog;
use App\Models\RawmatLog;
use App\Models\Product;
use App\Models\Category;
use App\Models\Ingredient;
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
        $orders = Order::where('status', '>', 0)->get();
        if(!empty($orders)){
            foreach($orders as $key => $value){
                $orders[$key]['price_total'] = number_format($value['price_total'], 0, '', '.');
                $orders[$key]['discount'] = number_format($value['discount'], 0, '', '.');
                $orders[$key]['final_price'] = number_format($value['final_price'], 0, '', '.');
            }
        }
        return response()->json( array( 'orders'  => $orders ) );
    }

    public function create(){
        $prefix = date('ymd');
        $order = Order::where('order_number', 'like', $prefix.'%')
        // ->where('status', '>', 0)
        ->orderByDesc('id')
        ->first();
        if(empty($order)){
            $num = $prefix.'001';
            $order = new Order();
            $order->order_number = $num;
            $order->user_id = Auth::user()->id;
            $order->uuid = Str::uuid();
            $order->save();
        }else{
            if($order->status > 0){
                $num = $order->order_number + 1;
                $order = new Order();
                $order->order_number = $num;
                $order->user_id = Auth::user()->id;
                $order->uuid = Str::uuid();
                $order->save();
            }
        }
        $order->price_total = number_format($order->price_total, 0, '', '.');
        return response()->json( array(
            'order'  => $order
        ));
    }

    public function edit(Request $request){
        $uuid = $request->input('uuid');
        $order = Order::where('uuid', '=', $uuid)->first();
        $order->price_total = number_format($order->price_total, 0, '', '.');
        return response()->json( array(
            'order'  => $order
        ));
    }

    public function saveOrderDetail(Request $request){
        $uuid = $request->input('uuid');
        $customer_name = $request->input('customer_name');
        $customer_email = $request->input('customer_email');
        $note = $request->input('note');
        $order = Order::where('uuid', '=', $uuid)->first();
        $order->customer_name = $customer_name;
        $order->customer_email = $customer_email;
        $order->note = $note;
        $order->save();
        return response()->json( array('success'=>true) );
    }

    public function saveOrder(Request $request){
        $uuid = $request->input('uuid');
        $stat = $request->input('stat');
        $redir = false;
        if($stat == 2){
            $redir = true;
        }
        $order = Order::where('uuid', '=', $uuid)->first();
        $orderlog = OrderLog::where('order_id', '=', $order->id)->update(['saved'=>true]);
        $rawmatlog = RawmatLog::where('order_id', '=', $order->id)->where('saved', false)->get();
        if(!empty($rawmatlog)){
            foreach($rawmatlog as $rawlog){
                $rawmat = Rawmat::where('id', '=', $rawlog->rawmat_id)->first();
                $rawmat->stock = $rawmat->stock - $rawlog->quantity;
                $rawmat->save();
                $rawlog->saved = true;
                $rawlog->save();
            }
        }
        $order->status = $stat;
        $order->save();
        return response()->json( array('success'=>true, 'redir'=>$redir) );
    }

    public function getCategories(){
        $categories = Category::select('categories.name as label', 'categories.uuid as value')->get();
        $categories->push(['label'=>'All Categories', 'value'=>'']);
        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function listItems(Request $request){
        $searchkey = $request->input('searchkey');
        $categorykey = $request->input('searchcategory');
        $product = '';
        if($categorykey != ''){
            $category = Category::where('uuid', '=', $categorykey)->first();
            $product = Product::where('name', 'like', '%'.$searchkey.'%')->where('category_id', '=', $category->id)->paginate(12);
        }else{
            $product = Product::where('name', 'like', '%'.$searchkey.'%')->paginate(12);
        }
        // $product;
        // echo var_dump($product);
        if(!empty($product)){
            foreach($product as $key => $value){
                $product[$key]['price'] = number_format($value['price'], 0, '', '.');
            }
        }
        return response()->json( $product );
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
                if($order_items[$key]['saved'] == 0){
                    $order_items[$key]['saved'] = false;
                }else{
                    $order_items[$key]['saved'] = true;
                }
            }
        }
        return response()->json( $order_items );
    }

    public function saveQuantity(Request $request){
        $uuid = $request->input('uuid');
        $order_uuid = $request->input('order_uuid');
        $quantity = $request->input('quantity');
        $orderlog = OrderLog::where('uuid', '=', $uuid)->first();
        $order = Order::where('uuid', '=', $order_uuid)->first();
        $product = Product::where('id', '=', $orderlog->product_id)->first();
        $reload = false;
        if($quantity == 0){
            $order->price_total = $order->price_total - ($product->price*$orderlog->quantity);
            $order->save();
            $orderlog->delete();
            $rawmatlog = RawmatLog::where('order_log_id', '=', $orderlog->id)->delete();
            $reload = true;
        }else{
            $old_qty = $orderlog->quantity;
            $div = $quantity - $old_qty;
            $order->price_total = $order->price_total + ($product->price*$div);
            $order->save();
            $orderlog->quantity = $quantity;
            $orderlog->save();
            $ingredient = Ingredient::where('product_id', '=', $orderlog->product_id)->get();
            if(!empty($ingredient)){
                foreach($ingredient as $i){
                    $rawmatlog = RawmatLog::where('order_log_id', '=', $orderlog->id)
                    ->where('rawmat_id', '=', $i->rawmat_id)
                    ->where('order_id', '=', $orderlog->order_id)
                    ->where('saved', false)
                    ->first();
                    if(!empty($rawmatlog)){
                        $rawmatlog->quantity = $i->quantity * $quantity;
                        $rawmatlog->save();
                    }
                }
            }
        }
        $order->price_total = number_format($order->price_total, 0, '', '.');
        return response()->json( array('success'=>true, 'reload'=>$reload, 'order'=>$order) );
    }

    public function addOrderItem(Request $request){
        $uuid = $request->input('uuid');
        $order_uuid = $request->input('order_uuid');
        $product = Product::where('uuid', '=', $uuid)->first();
        $order = Order::where('uuid', '=', $order_uuid)->first();
        $orderlog = OrderLog::where('product_id', '=', $product->id)
        ->where('order_id', '=', $order->id)
        ->where('saved', false)
        ->first();
        if(empty($orderlog)){
            $orderlog = new OrderLog();
            $orderlog->product_id = $product->id;
            $orderlog->order_id = $order->id;
            $orderlog->quantity = 1;
            $orderlog->user_id = Auth::user()->id;
            $orderlog->uuid = Str::uuid();
            $orderlog->save();
            $order->price_total = $order->price_total + $product->price;
            $order->save();
        }
        $ingredient = Ingredient::where('product_id', '=', $product->id)->get();
        if(!empty($ingredient)){
            foreach($ingredient as $i){
                $rawmatlog = RawmatLog::where('order_log_id', '=', $orderlog->id)
                ->where('rawmat_id', '=', $i->rawmat_id)
                ->where('order_id', '=', $order->id)
                ->where('saved', false)
                ->first();
                if(empty($rawmatlog)){
                    $rawmatlog = new RawmatLog();
                    $rawmatlog->status = 1;
                    $rawmatlog->quantity = $i->quantity;
                    $rawmatlog->rawmat_id = $i->rawmat_id;
                    $rawmatlog->order_id = $order->id;
                    $rawmatlog->order_log_id = $orderlog->id;
                    $rawmatlog->user_id = Auth::user()->id;
                    $rawmatlog->uuid = Str::uuid();
                    $rawmatlog->save();
                }
            }
        }
        $order->price_total = number_format($order->price_total, 0, '', '.');
        return response()->json( array('success'=>true, 'order'=>$order) );
    }
    public function removeOrderItem(Request $request){
        $uuid = $request->input('uuid');
        $order_uuid = $request->input('order_uuid');
        $product = Product::where('uuid', '=', $uuid)->first();
        $order = Order::where('uuid', '=', $order_uuid)->first();
        $orderlog = OrderLog::where('product_id', '=', $product->id)
        ->where('order_id', '=', $order->id)
        ->where('saved', false)
        ->first();
        if(!empty($orderlog)){
            $order->price_total = $order->price_total - ($product->price*$orderlog->quantity);
            $order->save();
            $orderlog->delete();
        }
        $ingredient = Ingredient::where('product_id', '=', $product->id)->get();
        if(!empty($ingredient)){
            foreach($ingredient as $i){
                $rawmatlog = RawmatLog::where('order_log_id', '=', $orderlog->id)
                ->where('rawmat_id', '=', $i->rawmat_id)
                ->where('order_id', '=', $order->id)
                ->where('saved', false)
                ->first();
                if(!empty($rawmatlog)){
                    $rawmatlog->delete();
                }
            }
        }
        $order->price_total = number_format($order->price_total, 0, '', '.');
        return response()->json( array('success'=>true, 'order'=>$order) );
    }
}
