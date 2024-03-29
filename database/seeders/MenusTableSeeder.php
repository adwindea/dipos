<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class MenusTableSeeder extends Seeder
{
    private $menuId = null;
    private $dropdownId = array();
    private $dropdown = false;
    private $sequence = 1;
    private $joinData = array();
    private $adminRole = null;
    private $userRole = null;

    public function join($roles, $menusId){
        $roles = explode(',', $roles);
        foreach($roles as $role){
            array_push($this->joinData, array('role_name' => $role, 'menus_id' => $menusId));
        }
    }

    /*
        Function assigns menu elements to roles
        Must by use on end of this seeder
    */
    public function joinAllByTransaction(){
        DB::beginTransaction();
        foreach($this->joinData as $data){
            DB::table('menu_role')->insert([
                'role_name' => $data['role_name'],
                'menus_id' => $data['menus_id'],
            ]);
        }
        DB::commit();
    }

    public function insertLink($roles, $name, $href, $icon = null){
        if($this->dropdown === false){
            DB::table('menus')->insert([
                'slug' => 'link',
                'name' => $name,
                'icon' => $icon,
                'href' => $href,
                'menu_id' => $this->menuId,
                'sequence' => $this->sequence
            ]);
        }else{
            DB::table('menus')->insert([
                'slug' => 'link',
                'name' => $name,
                'icon' => $icon,
                'href' => $href,
                'menu_id' => $this->menuId,
                'parent_id' => $this->dropdownId[count($this->dropdownId) - 1],
                'sequence' => $this->sequence
            ]);
        }
        $this->sequence++;
        $lastId = DB::getPdo()->lastInsertId();
        $this->join($roles, $lastId);
        $permission = Permission::where('name', '=', $name)->get();
        if(empty($permission)){
            $permission = Permission::create(['name' => 'visit ' . $name]);
        }
        $roles = explode(',', $roles);
        if(in_array('user', $roles)){
            $this->userRole->givePermissionTo($permission);
        }
        if(in_array('admin', $roles)){
            $this->adminRole->givePermissionTo($permission);
        }
        return $lastId;
    }

    public function insertTitle($roles, $name){
        DB::table('menus')->insert([
            'slug' => 'title',
            'name' => $name,
            'menu_id' => $this->menuId,
            'sequence' => $this->sequence
        ]);
        $this->sequence++;
        $lastId = DB::getPdo()->lastInsertId();
        $this->join($roles, $lastId);
        return $lastId;
    }

    public function beginDropdown($roles, $name, $href='', $icon=''){
        if(count($this->dropdownId)){
            $parentId = $this->dropdownId[count($this->dropdownId) - 1];
        }else{
            $parentId = null;
        }
        DB::table('menus')->insert([
            'slug' => 'dropdown',
            'name' => $name,
            'icon' => $icon,
            'menu_id' => $this->menuId,
            'sequence' => $this->sequence,
            'parent_id' => $parentId,
            'href' => $href
        ]);
        $lastId = DB::getPdo()->lastInsertId();
        array_push($this->dropdownId, $lastId);
        $this->dropdown = true;
        $this->sequence++;
        $this->join($roles, $lastId);
        return $lastId;
    }

    public function endDropdown(){
        $this->dropdown = false;
        array_pop( $this->dropdownId );
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Get roles */
        $this->adminRole = Role::where('name' , '=' , 'admin' )->first();
        $this->userRole = Role::where('name', '=', 'user' )->first();
        $dropdownId = array();
        /* Create Sidebar menu */
        DB::table('menulist')->insert([
            'name' => 'sidebar menu'
        ]);
        $this->menuId = DB::getPdo()->lastInsertId();  //set menuId
        /* guest menu */
        $this->insertLink('admin', 'Dashboard', '/dashboard', 'cil-speedometer');
        $this->insertLink('admin', 'Report', '/report/sales', 'cil-description');
        $this->insertLink('guest', 'Login', '/login', 'cil-account-logout');
        $this->insertLink('user,admin', 'Order', '/order/create', 'cil-dinner');
        $this->insertLink('user,admin', 'Transaction', '/order', 'cil-cart');
        $this->insertLink('admin', 'Promotion', '/promotion', 'cil-tag');

        $this->beginDropdown('admin', 'Management', '/', 'cil-briefcase');
            $this->insertLink('admin', 'Tenant', '/tenant', 'cil-cog');
            $this->insertLink('admin', 'Users', '/users', 'cil-user');
        $this->endDropdown();


        $this->beginDropdown('admin', 'Data', '/', 'cil-list');
            $this->insertLink('admin', 'Raw Materials', '/rawmat', 'cil-gift');
            $this->insertLink('admin', 'Categories', '/category', 'cil-lan');
            $this->insertLink('admin', 'Products', '/product', 'cil-fastfood');
        $this->endDropdown();

        /* Create top menu */
        // DB::table('menulist')->insert([
        //     'name' => 'top_menu'
        // ]);
        // $this->menuId = DB::getPdo()->lastInsertId();  //set menuId
        // $this->beginDropdown('guest,user,admin', 'Pages');
        //     $this->insertLink('guest,user,admin', 'Dashboard',    '/');
        //     $this->insertLink('user,admin', 'Notes',              '/notes');
        //     $this->insertLink('admin', 'Users',                   '/users');
        // $this->endDropdown();
        // $this->beginDropdown('admin', 'Settings');
        //     $this->insertLink('admin', 'Edit menu',               '/menu');
        //     $this->insertLink('admin', 'Edit roles',              '/roles');
        //     $this->insertLink('admin', 'Media',                   '/media');
        //     $this->insertLink('admin', 'BREAD',                   '/bread');
        //     $this->insertLink('admin', 'E-mail',                  '/email');
        // $this->endDropdown();

        $this->joinAllByTransaction(); ///   <===== Must by use on end of this seeder
    }
}
