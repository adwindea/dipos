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
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $order = Order::where('status', '=', 2)
        ->whereBetween('created_at', [$start_date, $end_date])
        // ->where('created_at', '<', $end_date)
        ->count();
        echo var_dump($order);
        // return response()->json( array(
        //     'order'  => $order->order
        // ));
    }
}
