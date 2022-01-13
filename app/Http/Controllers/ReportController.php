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
        $orders = Order::with('order_logs')
        ->where('status', '=', 2)
        ->where('tenant_id', Auth::user()->tenant_id)
        ->whereBetween('created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))])
        ->orderBy('id')
        ->get()
        ->map(function($order){
            return [
                'cogs' => $order->cogs,
                'id' => $order->id,
                'capital_price' => $order->capital_price,
                'products' => $order->order_logs->sum('quantity'),
            ];
        });
        if(!$orders){
            $orders->put(['cogs'=>0, 'capital_price'=>0, 'id'=>0, 'products'=>0]);
        }

        $order = collect([
            'cogs' => decToCur($orders->sum('cogs')) ,
            'order_count' => decToCur($orders->count()),
            'maxid' => $orders->max('id'),
            'minid' => $orders->min('id'),
            'capital_price' => decToCur($orders->sum('capital_price')),
            'products' => decToCur($orders->sum('products')),
        ]);

        // $orderlog = OrderLog::where('saved', '=', 1)
        // ->where('tenant_id', Auth::user()->tenant_id)
        // ->whereBetween('order_id', [$order['minid'], $order['maxid']])
        // ->selectRaw('sum(quantity) as products')
        // ->first();
        // $orderlog->products = decToCur($orderlog->products);

        return response()->json( array(
            'order'  => $order,
            // 'product' => $orderlog,
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
        $products = Product::with(['category', 
        'order_logs' => function($query) use($date){
            return $query->where('saved', true)
            ->whereHas('order', function($query) use($date){
                return $query->whereBetween('created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))]);
            });
        }])
        ->orderBy('name')
        ->get()->map(function($product){
            $sell_price = $product->price*$product->order_logs->sum('quantity');
            $final_price = $sell_price-$product->order_logs->sum('discount');
            return [
                'product_name' => $product->name,
                'category' => $product->category->name,
                'quantity' => decToCur($product->order_logs->sum('quantity')),
                'sell_price' => decToCur($sell_price),
                'final_price' => decToCur($final_price),
                'discount' => decToCur($product->order_logs->sum('discount')),
            ];
        });
        return response()->json( array(
            'products'  => $products
        ));
    }

    public function dashboardSalesChart(Request $request){
        $date = $request->input('date');

        $pie = [];
        $bar = [];
        $productlabel = [];

        $productSold = OrderLog::whereHas('order', function($query) use($date){
            return $query->whereBetween('created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))]);
        })
        ->get()
        ->sum('quantity');

        $products =  Product::with(['order_logs' => function($query) use($date){
            return $query->where('saved', true)
            ->whereHas('order', function($query) use($date){
                return $query->whereBetween('created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))]);
            });
        }])
        ->orderBy('name')
        ->get()->map(function($product) use($productSold){
            $percent = $product->order_logs->sum('quantity')/$productSold*100;
            $pieobj = new \stdClass();
            $pieobj->name = $product->name;
            $pieobj->y = $percent;
            $quantity = floatval($product->order_logs->sum('quantity'));
            return [
                'pieobj' => $pieobj,
                'quantity' => $quantity,
                'productlabel' => $product->name,
            ];
        });
        if($products){
            foreach($products as $product){
                array_push($pie, $product['pieobj']);
                array_push($productlabel, $product['productlabel']);
                array_push($bar, $product['quantity']);    
            }
        }

        $catpie = [];
        $catbar = [];
        $catlabel = [];
        
        $categories = Category::with(['products.order_logs' => function($query) use($date){
            return $query->where('saved', true)
            ->whereHas('order', function($query) use($date){
                return $query->whereBetween('created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))]);
            });
        }])
        ->orderBy('name')
        ->get()->map(function($category) use($productSold){
            $quantity = $category->products->sum(function($product){
                return $product->order_logs->sum('quantity');
            });
            $percent = $quantity/$productSold*100;
            $pieobj = new \stdClass();
            $pieobj->name = $category->name;
            $pieobj->y = $percent;
            $quantity = floatval($quantity);
            return [
                'pieobj' => $pieobj,
                'quantity' => $quantity,
                'catlabel' => $category->name,
            ];
        });
        if($categories){
            foreach($categories as $category){
                array_push($catpie, $category['pieobj']);
                array_push($catlabel, $category['catlabel']);
                array_push($catbar, $category['quantity']);    
            }
        }

        return response()->json( array(
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

        $products =  Product::with(['category', 'order_logs' => function($query) use($date){
            return $query->where('saved', true)
            ->whereHas('order', function($query) use($date){
                return $query->whereBetween('created_at', [$date['startDate'], date('Y-m-d', strtotime(date('Y-m-d', strtotime($date['endDate'].'+1 day'))))]);
            });
        }])
        ->orderBy('name')
        ->get()->map(function($product){
            return [
                'product_name' => $product->name,
                'category' => $product->category->name,
                'quantity' => $product->order_logs->sum('quantity'),
                'price' => $product->price,
                'discount' => $product->order_logs->sum('discount'),
                'sell_price' => $product->price*$product->order_logs->sum('quantity'),
                'final_price' => ($product->price*$product->order_logs->sum('quantity'))-$product->order_logs->sum('discount'),
            ];  
        });
        return response()->json( array(
            'products'  => $products
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
