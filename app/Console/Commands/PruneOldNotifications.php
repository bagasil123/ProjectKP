<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications older than 3 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Menghapus notifikasi lama...');
        $deletedCount = Notification::where('created_at', '<=', Carbon::now()->subDays(3))->delete();
        $this->info("Selesai. {$deletedCount} notifikasi telah dihapus.");
        return 0;
    }
}
