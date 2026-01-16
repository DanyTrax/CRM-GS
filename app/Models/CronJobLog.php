<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronJobLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_name',
        'status',
        'output',
        'error',
        'started_at',
        'completed_at',
        'duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Registra el inicio de un job
     */
    public static function start(string $jobName): self
    {
        return static::create([
            'job_name' => $jobName,
            'status' => 'success',
            'started_at' => now(),
        ]);
    }

    /**
     * Finaliza un job con éxito
     */
    public function finish(string $output = null): void
    {
        $this->update([
            'status' => 'success',
            'output' => $output,
            'completed_at' => now(),
            'duration_seconds' => $this->started_at->diffInSeconds(now()),
        ]);
    }

    /**
     * Finaliza un job con error
     */
    public function fail(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error' => $error,
            'completed_at' => now(),
            'duration_seconds' => $this->started_at->diffInSeconds(now()),
        ]);
    }
}
