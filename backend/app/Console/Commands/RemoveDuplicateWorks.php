<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateWorks extends Command
{
    protected $signature = 'works:remove-duplicates {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Remove duplicate works keeping only one per title and type';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        // Find duplicates (title + type combination)
        $duplicates = DB::table('works')
            ->select('title', 'type', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
            ->whereNull('deleted_at')
            ->groupBy('title', 'type')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        
        $totalRemoved = 0;
        
        foreach ($duplicates as $dup) {
            // Get IDs to delete (all except the minimum one)
            $toDelete = DB::table('works')
                ->where('title', $dup->title)
                ->where('type', $dup->type)
                ->whereNull('deleted_at')
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');
            
            if ($dryRun) {
                $this->line("Would delete {$dup->cnt} duplicates of '{$dup->title}' ({$dup->type})");
            } else {
                DB::table('works')
                    ->whereIn('id', $toDelete)
                    ->update(['deleted_at' => now()]);
            }
            
            $totalRemoved += $toDelete->count();
        }
        
        if ($dryRun) {
            $this->info("Total that would be removed: {$totalRemoved}");
        } else {
            $this->info("Removed {$totalRemoved} duplicate works");
        }
        
        // Show remaining counts
        $this->info("Remaining works by type:");
        $counts = DB::table('works')
            ->select('type', DB::raw('COUNT(*) as cnt'))
            ->whereNull('deleted_at')
            ->groupBy('type')
            ->orderBy('type')
            ->get();
        
        foreach ($counts as $c) {
            $this->line("  {$c->type}: {$c->cnt}");
        }
        
        return Command::SUCCESS;
    }
}