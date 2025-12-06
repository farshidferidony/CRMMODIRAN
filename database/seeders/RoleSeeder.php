<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $roles = [
            ['name' => 'sales_expert',       'title' => 'کارشناس فروش'],
            ['name' => 'sales_supervisor',   'title' => 'سرپرست فروش'],
            ['name' => 'sales_manager',      'title' => 'مدیر فروش'],
            ['name' => 'purchase_expert',    'title' => 'کارشناس خرید'],
            ['name' => 'purchase_supervisor','title' => 'سرپرست خرید'],
            ['name' => 'purchase_manager',   'title' => 'مدیر خرید'],
            ['name' => 'commerce_manager',   'title' => 'مدیر بازرگانی'],
            ['name' => 'finance_expert',     'title' => 'کارشناس حسابداری'],
            ['name' => 'finance_manager',    'title' => 'مدیر حسابداری'],
            ['name' => 'logistics_expert',   'title' => 'کارشناس لجستیک'],
            ['name' => 'logistics_manager',  'title' => 'مدیر لجستیک'],
            ['name' => 'it_manager',         'title' => 'مدیر انفورماتیک'],
            ['name' => 'ceo',                'title' => 'مدیر عامل'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r['name']], ['title' => $r['title']]);
        }
    }
}


