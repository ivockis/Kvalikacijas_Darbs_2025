<?php

namespace Database\Seeders;

use App\Models\Tool;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tools = [
            ['name' => 'Hammer', 'comment' => 'Standard claw hammer'],
            ['name' => 'Saw', 'comment' => 'Hand saw for wood'],
            ['name' => 'Screwdriver Set', 'comment' => 'Assorted sizes'],
            ['name' => 'Drill', 'comment' => 'Cordless power drill'],
            ['name' => 'Pliers', 'comment' => 'Various types'],
            ['name' => 'Measuring Tape', 'comment' => '5-meter length'],
            ['name' => 'Wrench', 'comment' => 'Adjustable wrench'],
            ['name' => 'Wood Glue', 'comment' => 'Strong wood adhesive'],
            ['name' => 'Sandpaper', 'comment' => 'Assorted grits'],
            ['name' => 'Chisel Set', 'comment' => 'For fine woodworking'],
            ['name' => 'Router', 'comment' => 'For shaping wood edges'],
            ['name' => 'Soldering Iron', 'comment' => 'For electronics/jewelry'],
            ['name' => 'Needles', 'comment' => 'Knitting/sewing needles'],
            ['name' => 'Yarn', 'comment' => 'Various colors and types'],
            ['name' => 'Fabric Scissors', 'comment' => 'Sharp for textiles'],
            ['name' => 'Sewing Machine', 'comment' => 'Basic model'],
            ['name' => 'Paint Brushes', 'comment' => 'Acrylic/Oil set'],
            ['name' => 'Easel', 'comment' => 'Wooden painting easel'],
            ['name' => 'Pottery Wheel', 'comment' => 'Beginner friendly'],
            ['name' => 'Clay', 'comment' => 'Ceramic modeling clay'],
            ['name' => 'Jeweler\'s Pliers', 'comment' => 'Small and precise'],
            ['name' => 'Beads', 'comment' => 'Assorted for jewelry'],
            ['name' => 'Leather Punch', 'comment' => 'For leather working'],
            ['name' => 'Awl', 'comment' => 'For piercing materials'],
            ['name' => 'Craft Knife', 'comment' => 'Precision cutting'],
        ];

        foreach ($tools as $index => $toolData) {
            $approved = true;
            if ($index >= count($tools) - 3) { // Last 3 tools are unapproved
                $approved = false;
            }
            Tool::factory()->create(array_merge($toolData, ['approved' => $approved]));
        }
    }
}