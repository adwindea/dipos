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

        $pie = [];
        $bar = [];
        $productlabel = [];
        if(!empty($product)){
            foreach($product as $p){
                $percent = $p->quantity/$product_sum*100;
                $pieobj = new \stdClass();
                $pieobj->name = $p->product_name;
                $pieobj->y = $percent;
                $quantity = floatval($p->quantity);
                array_push($pie, $pieobj);
                array_push($productlabel, $p->product_name);
                array_push($bar, $quantity);
            }
        }

        $category = OrderLog::join('orders', 'orders.id', '=', 'order_logs.order_id')
            ->join('products', 'products.id', '=', 'order_logs.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('order_logs.saved', true)
            ->whereBetween('orders.created_at', [$date['start_date'], $date['end_date']])
            ->orderBy('categories.name')
            ->groupBy('categories.id')
            ->selectRaw('categories.name as category_name, sum(order_logs.quantity) as quantity')
            ->get();
        $catpie = [];
        $catbar = [];
        $catlabel = [];
        if(!empty($category)){
            foreach($category as $c){
                $percent = $c->quantity/$product_sum*100;
                $catpieobj = new \stdClass();
                $catpieobj->name = $c->category_name;
                $catpieobj->y = $percent;
                $quantity = floatval($c->quantity);
                array_push($catpie, $catpieobj);
                array_push($catlabel, $c->category_name);
                array_push($catbar, $quantity);
            }
        }

        return response()->json( array(
            // 'label'  => $label,
            'pie'  => $pie,
            'bar'  => $bar,
            'productlabel' => $productlabel,
            'catpie'  => $catpie,
            'catbar'  => $catbar,
            'catlabel' => $catlabel
        ));
    }

    public function salesReportChart(Request $request){
        $date = $request->input('date');
        $cat = [];
        $order = [];
        $cogs = [];
        $cogsorder = [];
        for($i = $date['start_date']; $i <= $date['end_date']; $i = date('Y-m-d', strtotime($i.'+1 day'))){
            array_push($cat, $i);
            $ornum = date('ymd', strtotime($i));
            $data = Order::where('status', '=', 2)
            ->where('order_number', 'like', $ornum.'%')
            ->selectRaw('count(id) as order_count, sum(cogs) as cogs')
            ->first();
            $cogsdata = floatval($data->cogs);
            $cogsorderdata = null;
            if($data->order_count > 0){
                $cogsorderdata = $cogsdata/$data->order_count*1;
            }
            array_push($order, $data->order_count);
            array_push($cogs, $cogsdata);
            array_push($cogsorder, $cogsorderdata);
        }
        return response()->json( array(
            'cat'  => $cat,
            'order' => $order,
            'cogs' => $cogs,
            'cogsorder' => $cogsorder,
        ));
    }
    public function salesReportData(Request $request){
        $date = $request->input('date');
        $order = Order::where('status', '=', 2)
        ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
        ->selectRaw('count(id) as total_order, sum(cogs) as COGS, date(created_at) sales_date')
        ->orderBy('created_at')
        ->groupByRaw('DATE(created_at)')
        ->get();
        if(!empty($order)){
            foreach ($order as $o){
                $avg = 0;
                if($o->total_order > 0){
                    $avg = $o->COGS/$o->total_order;
                }
                $o->average = number_format($avg, 0, '', '.');
                $o->COGS = number_format($o->COGS, 0, '', '.');
                $o->total_order = number_format($o->total_order, 0, '', '.');
            }
        }
        return response()->json( array(
            'order' => $order,
        ));
    }
}
