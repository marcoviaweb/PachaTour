<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixReviewableTypes extends Command
{
    protected $signature = 'fix:reviewable-types';
    protected $description = 'Fix reviewable_type references in reviews table';

    public function handle()
    {
        $this->info('🔍 Verificando tipos de reviewable en reviews...');
        
        // 1. Ver qué tipos hay actualmente
        $this->newLine();
        $this->info('📊 Tipos actuales en la tabla reviews:');
        
        $reviews = DB::table('reviews')->select('id', 'reviewable_type', 'reviewable_id')->get();
        
        foreach ($reviews as $review) {
            $this->line("ID: {$review->id} | Type: {$review->reviewable_type} | Reviewable ID: {$review->reviewable_id}");
        }
        
        // 2. Actualizar tipos obsoletos
        $this->newLine();
        $this->info('🔄 Actualizando tipos obsoletos...');
        
        $updates = [
            'App\\Models\\Attraction' => 'App\\Features\\Attractions\\Models\\Attraction',
            'App\\Models\\Tour' => 'App\\Features\\Tours\\Models\\Tour',
            'App\\Models\\Department' => 'App\\Features\\Departments\\Models\\Department',
        ];
        
        foreach ($updates as $oldType => $newType) {
            $count = DB::table('reviews')
                ->where('reviewable_type', $oldType)
                ->update(['reviewable_type' => $newType]);
                
            if ($count > 0) {
                $this->info("✅ Actualizadas {$count} reseñas: {$oldType} → {$newType}");
            }
        }
        
        // 3. Verificar resultado
        $this->newLine();
        $this->info('📊 Tipos después de la actualización:');
        
        $reviewsAfter = DB::table('reviews')->select('id', 'reviewable_type', 'reviewable_id')->get();
        
        foreach ($reviewsAfter as $review) {
            $this->line("ID: {$review->id} | Type: {$review->reviewable_type} | Reviewable ID: {$review->reviewable_id}");
        }
        
        $this->newLine();
        $this->info('✅ Actualización completada');
    }
}