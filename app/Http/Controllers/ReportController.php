<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $orders = Order::where('status', '=', 2)
        ->where('tenant_id', Auth::user()->tenant_id)
        ->whereBetween('created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))])
        ->orderBy('id')
        ->get()
        ->map(function($order){
            return [
                'cogs' => $order->cogs,
                'id' => $order->id,
                'capital_price' => $order->capital_price,
            ];
        });
        if(!$orders){
            $orders->put(['cogs'=>0, 'capital_price'=>0, 'id'=>0]);
        }

        $order = collect([
            'cogs' => decToCur($orders->sum('cogs')) ,
            'order_count' => decToCur($orders->count()),
            'maxid' => $orders->max('id'),
            'minid' => $orders->min('id'),
            'capital_price' => decToCur($orders->sum('capital_price')),
        ]);

        $orderlog = OrderLog::where('saved', '=', 1)
        ->where('tenant_id', Auth::user()->tenant_id)
        ->whereBetween('order_id', [$order['minid'], $order['maxid']])
        ->selectRaw('sum(quantity) as products')
        ->first();
        $orderlog->products = decToCur($orderlog->products);

        return response()->json( array(
            'order'  => $order,
            'product' => $orderlog,
        ));
    }

    public function dashboardTransactionTable(Request $request){
        $date = $request->input('date');
        $order = Order::where('status', '=', 2)
        ->whereBetween('created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))])
        ->orderByDesc('id')
        ->get()->map(function($o){
            return [
                'charge' => decToCur($o->final_price-$o->cogs),
                'price_total' => decToCur($o->price_total),
                'discount' => decToCur($o->discount),
                'final_price' => decToCur($o->discount),
                'capital_price' => decToCur($o->capital_price),
                'COGS' => decToCur($o->cogs),
                'order_number' => $o->order_number
            ];
        });
        return response()->json( array(
            'trans'  => $order
        ));
    }

    public function dashboardProductTable(Request $request){
        $date = $request->input('date');
        $product = Product::with(['category', 
        'order_logs' => function($query){
            return $query->where('saved', true);
        }, 
        'order_logs.order' => function($query) use($date){
            return $query->whereBetween('created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))]);
        }])
        ->orderBy('name')
        ->get();
        dd($product);
        $product = OrderLog::join('orders', 'orders.id', '=', 'order_logs.order_id')
        ->join('products', 'products.id', '=', 'order_logs.product_id')
        ->join('categories', 'categories.id', '=', 'products.category_id')
        ->where('order_logs.saved', true)
        ->whereBetween('orders.created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))])
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
            ->whereBetween('orders.created_at', [$date['startDate'], date('Y-m-d', strtotime($date['endDate'].'+1 day'))]);

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
            ->whereBetween('orders.created_at', [$date['startDate'], date('Y-m-d', strtotime($date['endDate'].'+1 day'))])
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
        $productsold = [];
        for($i = $date['startDate']; $i < date('Y-m-d', strtotime($date['endDate'].'+1 day')); $i = date('Y-m-d', strtotime($i.'+1 day'))){
            array_push($cat, $i);
            $ornum = date('ymd', strtotime($i));
            $data = Order::where('status', '=', 2)
            ->where('order_number', 'like', $ornum.'%')
            ->selectRaw('count(id) as order_count, sum(cogs) as cogs, max(id) as maxid, min(id) as minid')
            ->first();
            $orderlog = OrderLog::where('saved', true)
            ->whereBetween('order_id', [$data->minid, $data->maxid])
            ->selectRaw('sum(quantity) as productsold')
            ->first();
            $cogsdata = floatval($data->cogs);
            $cogsorderdata = null;
            if($data->order_count > 0){
                $cogsorderdata = number_format($cogsdata/$data->order_count, 2, '.', '');
                $cogsorderdata = floatval($cogsorderdata);
            }
            $productsolddata = floatval($orderlog->productsold);
            array_push($order, $data->order_count);
            array_push($cogs, $cogsdata);
            array_push($cogsorder, $cogsorderdata);
            array_push($productsold, $productsolddata);
        }
        return response()->json( array(
            'cat'  => $cat,
            'order' => $order,
            'cogs' => $cogs,
            'cogsorder' => $cogsorder,
            'productsold' => $productsold,
        ));
    }
    public function salesReportData(Request $request){
        $date = $request->input('date');
        $order = Order::where('status', '=', 2)
        ->whereBetween('created_at', [$date['startDate'], date('Y-m-d', strtotime($date['endDate'].'+1 day'))])
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

    public function excelProductSales(Request $request){
        $date = $request->input('date');
        $product = OrderLog::join('orders', 'orders.id', '=', 'order_logs.order_id')
        ->join('products', 'products.id', '=', 'order_logs.product_id')
        ->join('categories', 'categories.id', '=', 'products.category_id')
        ->where('order_logs.saved', true)
        ->whereBetween('orders.created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))])
        ->orderBy('products.name')
        ->selectRaw('products.name as product_name, categories.name as category, sum(order_logs.quantity) as quantity, products.price as price, sum(order_logs.discount) as discount')
        ->groupBy('products.id')
        ->get();
        // $quantity_tot = 0;
        // $sell_price_tot = 0;
        // $final_price_tot = 0;
        // $discount_tot = 0;
        if(!empty($product)){
            foreach($product as $p){
                $sell_price = $p->price*$p->quantity;
                // $quantity_tot = $quantity_tot + $p->quantity;
                // $sell_price_tot = $sell_price_tot + $sell_price;
                // $final_price_tot = $final_price_tot + ($sell_price-$p->discount);
                // $discount_tot = $discount_tot + $p->discount;
                $p->sell_price = number_format($sell_price, 0, '.', '');
                $p->final_price = number_format($sell_price-$p->discount, 0, '.', '');
                $p->discount = number_format($p->discount, 0, '.', '');
                $p->quantity = number_format($p->quantity, 0, '.', '');
            }
        }
        // $total = new \stdClass();
        // $total->product_name = 'Total';
        // $total->sell_price = number_format($sell_price_tot, 0, '.', '');
        // $total->final_price = number_format($final_price_tot, 0, '.', '');
        // $total->discount = number_format($discount_tot, 0, '.', '');
        // $total->quantity = number_format($quantity_tot, 0, '.', '');
        // array_push($product, $total);
        return response()->json( array(
            'products'  => $product
        ));
    }

    public function excelSalesReport(Request $request){
        $date = $request->input('date');
        $order = Order::leftJoin('order_logs', 'orders.id', '=', 'order_logs.order_id')
        ->where('orders.status', '=', 2)
        ->whereBetween('orders.created_at', [$date['startDate'], date('Y-m-d', strtotime($date['endDate'].'+1 day'))])
        ->selectRaw('count(orders.id) as total_order, sum(orders.cogs) as COGS, date(orders.created_at) sales_date, sum(order_logs.quantity) as total_cup')
        ->orderBy('orders.created_at')
        ->groupByRaw('DATE(orders.created_at)')
        ->get();
        if(!empty($order)){
            foreach ($order as $o){
                $avg = 0;
                if($o->total_order > 0){
                    $avg = $o->COGS/$o->total_order;
                }
                $o->average = number_format($avg, 0, '.', '');
                $o->COGS = number_format($o->COGS, 0, '.', '');
                $o->total_order = number_format($o->total_order, 0, '.', '');
                $o->total_cup = number_format($o->total_cup, 0, '.', '');
            }
        }
        return response()->json( array(
            'order' => $order,
        ));
    }
}
