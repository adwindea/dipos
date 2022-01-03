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
use App\Models\Promotion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('admin');
    }

    public function index(){
        $orders = Order::with('promotion', 'user')
        ->where('status', '>', 0)
        ->orderByDesc('order_number')
        ->get()->map(function($order){
            return [
                'uuid' => $order->uuid,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'customer_name' => $order->customer_name,
                'price_total' => $order->price_total+0,
                'discount' => $order->discount+0,
                'discount_type' => $order->discount_type,
                'max_discount' => $order->promotion ? $order->promotion->max_discount+0 : '',
                'amount' => $order->promotion ? $order->promotion->amount+0 : '',
                'final_price' => $order->final_price+0,
                'note' => $order->note,
                'cashier' => $order->user->name,
            ];
        });
        $role = Auth::user()->menuroles;
        $admin = false;
        if(strpos($role, 'admin') !== false){
            $admin = true;
        }
        return response()->json( array(
            'orders'  => $orders,
            'role' => $admin
            ));
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
        $order->price_total = $order->price_total+0;
        $order->discount = $order->discount+0;
        $order->final_price = $order->final_price+0;
        $promo = Promotion::find($order->promotion_id);
        if(empty($promo)){
            $promo = new Promotion();
            $promo->code = '';
        }
        return response()->json( array(
            'order'  => $order,
            'promo' => $promo,
            'user' => Auth::user()->name
        ));
    }

    public function show(Request $request){
        $uuid = $request->input('uuid');
        $order = Order::where('uuid', '=', $uuid)->first();
        $order->price_total = $order->price_total+0;
        $order->discount = $order->discount+0;
        $order->final_price = $order->final_price+0;
        $promo = Promotion::find($order->promotion_id);
        if(empty($promo)){
            $promo = new Promotion();
            $promo->code = '';
        }
        return response()->json( array(
            'order'  => $order,
            'promo' => $promo,
            'user' => Auth::user()->name
        ));
    }

    public function edit(Request $request){
        $uuid = $request->input('uuid');
        $order = Order::where('uuid', '=', $uuid)->first();
        $order->price_total = $order->price_total+0;
        return response()->json( array(
            'order'  => $order
        ));
    }

    public function checkPromotion(Request $request){
        $code = $request->input('code');
        $price_total = $request->input('price_total');
        $now = date('Y-m-d');
        $promo = Promotion::where('code', '=', $code)->first();
        $message = '';
        $status = false;
        if(!empty($promo)){
            if($promo->status == 0){
                $message = 'Promo is not available';
            }else if($now > $promo->end_date){
                $message = 'Promo is expired';
            }else if($now < $promo->start_date){
                $message = 'Promo is available from '.date('d M Y', strtotime($promo->start_date));
            }else if($price_total < $promo->min_buy){
                $message = 'Minimum order is IDR '.number_format($promo->min_buy, 0, '', '.');
            }else if($promo->quantity == 0){
                $message = 'Promo is not available';
            }else{
                $message = 'Promo successfully used';
                $status = true;
            }
        }else{
            $message = 'Promo not found';
        }
        return response()->json( array(
            'promo'  => $promo,
            'mess'  => $message,
            'status' => $status
        ));
    }

    public function saveOrderDetail(Request $request){
        $uuid = $request->input('uuid');
        $customer_name = $request->input('customer_name');
        $customer_email = $request->input('customer_email');
        $note = $request->input('note');
        $payment_type = $request->input('payment_type');
        $promotion_id = $request->input('promotion_id');
        $discount_type = $request->input('discount_type');
        $order = Order::where('uuid', '=', $uuid)->first();
        $order->customer_name = $customer_name;
        $order->customer_email = $customer_email;
        $order->note = $note;
        $order->payment_type = $payment_type;
        $order->promotion_id = $promotion_id;
        $order->discount_type = $discount_type;
        $order->save();
        $order = $this->countOrderPriceProcessor($uuid);
        return response()->json( array('order'=>$order) );
    }

    public function saveOrder(Request $request){
        $uuid = $request->input('uuid');
        $stat = $request->input('stat');
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
        $order->user_id = Auth::user()->id;
        $order->save();

        if($stat == 2){
            if(!empty($order->promotion_id)){
                $promo = Promotion::where('id', '=', $order->promotion_id)->first();
                if($promo->discount_type == 1){
                    $orderlog = OrderLog::where('order_id', '=', $order->id)->get();
                    if(!empty($orderlog)){
                        foreach($orderlog as $ol){
                            $product = Product::where('id', '=', $ol->product_id)->first();
                            $ol->discount = ($product->price*$promo->amount/100)*$ol->quantity;
                            $ol->save();
                        }
                    }
                }
                $promo->quantity = $promo->quantity - 1;
                if($promo->quantity == 0){
                    $promo->status = false;
                }
                $promo->save();
            }
        }

        return response()->json( array('success'=>true, 'order'=>$order) );
    }

    public function getCategories(){
        $categories = Category::get()->map(function($category){
            return [
                'label' => $category->name,
                'value' => $category->uuid
            ];
        });
        $categories->prepend(['label'=>'All Categories', 'value'=>'']);
        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function listItems(Request $request){
        $searchkey = $request->input('searchkey');
        $categorykey = $request->input('searchcategory');
        $products = '';
        if($categorykey != ''){
            $category = Category::where('uuid', '=', $categorykey)->first();
            $products = Product::where('name', 'like', '%'.$searchkey.'%')->where('category_id', '=', $category->id)->paginate(12);
        }else{
            $products = Product::where('name', 'like', '%'.$searchkey.'%')->paginate(12);
        }
        $products = tap($products, function ($productInstance){
            return $productInstance->getCollection()->transform(function($product){
                return [
                    'name' => $product->name,
                    'description' => $product->description,
                    'img' => $product->img,
                    'price' => $product->price+0,
                    'description' => $product->description,
                    'uuid' => $product->uuid,
                ];
            });
        });
        return response()->json( $products );
    }

    public function orderItems(Request $request){
        $uuid = $request->input('uuid');
        $order = Order::where('uuid', '=', $uuid)->first();
        $order_items = OrderLog::with('product')
        ->where('order_id', '=', $order->id)
        ->get()->map(function($order_item){
            $order_item->saved = $order_item->saved ? true : false;
            return [
                'name' => $order_item->product->name,
                'quantity' => $order_item->quantity+0,
                'price' => $order_item->product->price,
                'note' => $order_item->note,
                'saved' => $order_item->saved,
                'total_price' => $order_item->quantity*$order_item->product->price,
                'uuid' => $order_item->uuid,
            ];
        });
        return response()->json( 
            [
                'order_items' => $order_items,
                'total_price' => $order_items->sum('total_price'),
            ]
        );
    }

    public function saveQuantity(Request $request){
        $uuid = $request->input('uuid');
        $order_uuid = $request->input('order_uuid');
        $quantity = $request->input('quantity');
        $note = $request->input('note');
        $orderlog = OrderLog::where('uuid', '=', $uuid)->first();
        $order = Order::where('uuid', '=', $order_uuid)->first();
        $product = Product::find($orderlog->product_id)->first();
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
            $orderlog->note = $note;
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
        $order = $this->countOrderPriceProcessor($order_uuid);
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
        $order = $this->countOrderPriceProcessor($order_uuid);
        $order->price_total = $order->price_total+0;
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
        $order = $this->countOrderPriceProcessor($order_uuid);
        $order->price_total = number_format($order->price_total, 0, '', '.');
        return response()->json( array('success'=>true, 'order'=>$order) );
    }

    public function countOrderPriceProcessor($uuid){
        $order = Order::where('uuid', '=', $uuid)->first();
        $discount = '';
        $final_price = '';
        if(!empty($order->promotion_id)){
            $promo = Promotion::find($order->promotion_id);
            if($promo->discount_type == 1){
                $discount = $promo->amount*$order->price_total/100;
                if($discount > $promo->max_discount){
                    $discount = $promo->max_discount;
                }
            }else{
                $discount = $promo->max_discount;
            }
            $final_price = $order->price_total - $discount;
            $order->discount = $discount;
            $order->final_price = $final_price;
            $order->save();
        }
        else{
            $order->discount = 0;
            $order->final_price = $order->price_total;
            $order->save();
        }

        $cogs = $order->final_price;
        if($order->payment_type == 1){
            $cogs = $order->final_price*(100-0.7)/100;
        }else if($order->payment_type == 2){
            $cogs = $order->final_price*(100-1.5)/100;
        }
        $order->cogs = $cogs;
        $order->save();
        $order->price_total = number_format($order->price_total, 0, '', '.');
        $order->discount = number_format($order->discount, 0, '', '.');
        $order->final_price = number_format($order->final_price, 0, '', '.');
        return $order;
    }

    public function printOrder(Request $request){
        $uuid = $request->input('uuid');
        $order = Order::where('uuid', '=', $uuid)->first();
        $order->price_total = number_format($order->price_total, 0, '', '.');
        $order->discount = number_format($order->discount, 0, '', '.');
        $order->final_price = number_format($order->final_price, 0, '', '.');
        $order_items = OrderLog::join('products', 'products.id', '=', 'order_logs.product_id')
        ->select('order_logs.*', 'products.name as name', 'products.price as price')
        ->where('order_id', '=', $order->id)
        ->get();
        if(!empty($order_items)){
            foreach($order_items as $key => $value){
                $order_items[$key]['quantity'] = number_format($value['quantity'], 0, '', '');
                $order_items[$key]['price_unformat'] = number_format($value['price'], 0, '', '');
                $order_items[$key]['price'] = number_format($value['price'], 0, '', '.');
            }
        }
        return response()->json( array(
            'order_items'=>$order_items,
            'order'=>$order,
            'user'=>Auth::user()->name
        ));
    }

    public function delete(Request $request){
        $uuid = $request->input('uuid');
        $order = Order::where('uuid', '=', $uuid)->first();
        $rawmatlog = RawmatLog::where('saved', true)
        ->where('order_id', '=', $order->id)
        ->selectRaw('sum(quantity) as quantity, rawmat_id')
        ->groupBy('rawmat_id')
        ->get();
        if(!empty($rawmatlog)){
            foreach($rawmatlog as $rawlog){
                $rawmat = Rawmat::where('id', '=', $rawlog->rawmat_id)->first();
                $rawmat->stock = $rawmat->stock + $rawlog->quantity;
                $rawmat->save();
            }
        }
        $rawmatlog = RawmatLog::where('saved', true)
        ->where('order_id', '=', $order->id)
        ->update(['saved' => false]);
        $rawmatlog = RawmatLog::where('order_id', '=', $order->id)->delete();
        $orderlog = OrderLog::where('saved', true)
        ->where('order_id', '=', $order->id)
        ->update(['saved' => false]);
        $orderlog = OrderLog::where('order_id', '=', $order->id)->delete();
        $order->status = 0;
        $order->save();
        $order->delete();
        return response()->json( array('success'=>true) );
    }
}
