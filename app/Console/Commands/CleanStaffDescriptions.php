<?php

namespace App\Console\Commands;

use App\Models\Comprof\Datastaf;
use Illuminate\Console\Command;

class CleanStaffDescriptions extends Command
{
    protected $signature = 'staff:clean-descriptions';
    protected $description = 'Clean empty HTML tags from existing staff descriptions';

    public function handle()
    {
        $staffs = Datastaf::all();
        
        foreach ($staffs as $staff) {
            $description = trim($staff->description);
            
            if ($description === '<p><br></p>' || 
                $description === '<p></p>' || 
                $description === '<br>' || 
                $description === '&nbsp;' || 
                empty($description)) {
                $staff->description = '';
                $staff->save();
            }
        }
        
        $this->info('Successfully cleaned '.$staffs->count().' staff descriptions.');
    }
}