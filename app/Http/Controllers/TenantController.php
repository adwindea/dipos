<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant;

class TenantController extends Controller
{
    public function getTenantSetting(){
        $tenant = Tenant::find(Auth::user()->tenant_id);
        $tenant = [
            'name' => $tenant->name,
            'address' => $tenant->address,
            'phone' => $tenant->phone,
            'email' => $tenant->email,
            'logo' => $tenant->logo,
            'receipt_note' => $tenant->receipt_note,
        ];
        return response()->json([
            'tenant'  => $tenant,
        ]); 
    }

    public function saveTenantSetting(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required|numeric',
            'email' => 'required|email',
        ]);
        
        $tenant = Tenant::find(Auth::user()->tenant_id);

        $img = $request->input('logo');
        if(!empty($img)){
            $img = str_replace('data:image/png;base64,', '', $img);
            $img = str_replace('[removed]', '', $img);
            $img = str_replace(' ', '+', $img);
            $resource = base64_decode($img);
            $prefix = Str::random(8);
            $s3name = 'public/image/tenant/logo'.$tenant->sub.'.png';
            Storage::disk('local')->put($s3name, $resource);
            $logo = Storage::disk('local')->url($s3name);    
            $tenant->logo = $logo;
        }

        $tenant->name = $request->input('name');
        $tenant->address = $request->input('address');
        $tenant->phone = $request->input('phone');
        $tenant->email = $request->input('email');
        $tenant->receipt_note = $request->input('receipt_note');
        $tenant->save();

        return response()->json(
            [
                'success' => true,
            ],
            200
        );
    }
}
