<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //For creating Supplier

        $users = [
            [
                'name' => 'Santosh Singh',
                'email' => 'santosh@example.com',
            ],
            [
                'name' => 'Rajesh Singh',
                'email' => 'rajesh@example.com',
            ],
            [
                'name' => 'Nikhil Singh',
                'email' => 'nikhil@example.com',
            ],

        ];

        foreach($users as $user){

            $newUser = \App\Models\User::factory()->create($user);
            $newSupplier = \App\Models\Supplier::create([
                'user_id' => $newUser->id,
                'sid' =>'S'.$newUser->id,
                'business_name' =>'AAA'.$newUser->id,
            ]);

            $newAddress = \App\Models\Address::create([
                'supplier_id' => $newSupplier->id,
                'fname' => 'Santosh'.$newSupplier->id,
                'lname' => 'Singh',
                'contacts' => '8527117535',
                'line1' => 'Okhla',
                'line2' => 'phase 2',
                'district' => 'SOUTH WEST DELHI',
                'state' => 'Delhi',
                'country' => 'india',
                'pincode' => '435435',
                'district_id' => 120,
                'state_id' => 6,
            ]);

            $newAddress->print = $this->calculatePrint($newAddress);
            $newAddress->save();
            
        }
    }

    public function calculatePrint($address){
        $seperator = " ,";
        $print = $address->fname.' '.$address->lname;
        $print .= $address->contacts.$seperator;
        $print .= $address->line1.$seperator;
        $print .= $address->line2.$seperator;
        $print .= $address->district.$seperator;
        $print .= $address->state.$seperator;
        $print .= $address->country.$seperator;
        $print .= $address->pincode;
        return $print;
    }
}
