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

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('admin');
    }

    public function dashboardWidget(Request $request){
        $date = $request->input('date');
        $order = Order::where('status', '=', 2)
        ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
        ->orderBy('id')
        ->selectRaw('count(id) order_count, sum(cogs) as cogs, min(id) as minid, max(id) as maxid')
        ->groupByRaw('substring(order_number, 1, 6)')
        ->first();
        $order->cogs = number_format($order->cogs, 0, '', '.');

        $orderlog = OrderLog::where('saved', '=', 1)
        ->whereBetween('order_id', [$order->minid, $order->maxid])
        ->selectRaw('sum(quantity) as cups')
        ->first();
        $orderlog->cups = number_format($orderlog->cups, 0, '', '');

        $rawmatlog = RawmatLog::join('rawmats', 'rawmats.id', '=', 'rawmat_logs.rawmat_id')
        ->where('saved', '=', 1)
        ->whereBetween('order_id', [$order->minid, $order->maxid])
        ->selectRaw('sum(rawmats.price*rawmat_logs.quantity) as spend')
        ->first();
        $rawmatlog->spend = number_format($rawmatlog->spend, 0, '', '.');

        return response()->json( array(
            'order'  => $order,
            'product' => $orderlog,
            'rawmat' => $rawmatlog
        ));
    }

    public function dashboardTransactionTable(Request $request){
        $date = $request->input('date');
        $order = Order::where('status', '=', 2)
        ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
        ->orderByDesc('id')
        ->get();
        if(!empty($order)){
            foreach($order as $o){
                $o->charge = number_format($o->final_price-$o->cogs, 0, '', '.');
                $o->price_total = number_format($o->price_total, 0, '', '.');
                $o->discount = number_format($o->discount, 0, '', '.');
                $o->final_price = number_format($o->discount, 0, '', '.');
                $o->COGS = number_format($o->cogs, 0, '', '.');
            }
        }
        return response()->json( array(
            'trans'  => $order
        ));
    }

    public function dashboardProductTable(Request $request){
        $date = $request->input('date');
        $product = OrderLog::join('orders', 'orders.id', '=', 'order_logs.order_id')
        ->join('products', 'products.id', '=', 'order_logs.product_id')
        ->join('categories', 'categories.id', '=', 'products.category_id')
        ->where('order_logs.saved', true)
        ->whereBetween('orders.created_at', [$date['start_date'], $date['end_date']])
        ->orderBy('products.name')
        ->selectRaw('products.name as product_name, categories.name as category, sum(order_logs.quantity) as quantity, products.price as price, sum(order_logs.discount) as discount')
        ->groupBy('products.id')
        ->get();
        if(!empty($product)){
            foreach($product as $p){
                $sell_price = $p->price*$p->quantity;
                $p->sell_price = number_format($sell_price, 0, '', '.');
                $p->final_price = number_format($sell_price-$p->discount, 0, '', '.');
                $p->discount = number_format($p->discount, 0, '', '.');
                $p->quantity = number_format($p->quantity, 0, '', '.');
            }
        }
        return response()->json( array(
            'products'  => $product
        ));
    }

    public function dashboardSalesChart(Request $request){
        $date = $request->input('date');

        $product = OrderLog::join('orders', 'orders.id', '=', 'order_logs.order_id')
            ->join('products', 'products.id', '=', 'order_logs.product_id')
            // ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('order_logs.saved', true)
            ->whereBetween('orders.created_at', [$date['start_date'], $date['end_date']]);

        $product_sum = $product->sum('quantity');

        $product = $product->orderBy('products.name')
            ->groupBy('products.id')
            ->selectRaw('products.name as product_name, sum(order_logs.quantity) as quantity')
            ->get();

        $label = [];
        $pie = [];
        $bar = [];
        $col = [];
        $rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
        if(!empty($product)){
            foreach($product as $p){
                $color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
                array_push($col, $color);
                $percent = $p->quantity/$product_sum*100;
                array_push($label, $p->product_name);
                array_push($pie, number_format($percent, 2, '.', ''));
                array_push($bar, number_format($p->quantity, 0, '.', ''));
            }
        }
        return response()->json( array(
            'label'  => $label,
            'pie'  => $pie,
            'bar'  => $bar,
            'col' => $col
        ));
    }

}
