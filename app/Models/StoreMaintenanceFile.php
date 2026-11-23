<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Um anexo de uma manutenção: folha de serviço ou fotografia. */
class StoreMaintenanceFile extends Model
{
    public const TIPOS = [
        'folha' => ['label' => 'Folha de serviço', 'icon' => 'fa-file-alt'],
        'foto' => ['label' => 'Fotografia', 'icon' => 'fa-camera'],
    ];

    protected $fillable = ['record_id', 'tipo', 'path', 'nome', 'tamanho', 'created_by'];

    public function record()
    {
        return $this->belongsTo(StoreMaintenanceRecord::class, 'record_id');
    }

    public function isFoto(): bool
    {
        return $this->tipo === 'foto';
    }

    /** Tamanho em KB/MB, para se mostrar ao lado do nome. */
    public function tamanhoLegivel(): string
    {
        $bytes = (int) $this->tamanho;

        if ($bytes <= 0) {
            return '';
        }

        return $bytes >= 1048576
            ? round($bytes / 1048576, 1) . ' MB'
            : max(1, round($bytes / 1024)) . ' KB';
    }
}
