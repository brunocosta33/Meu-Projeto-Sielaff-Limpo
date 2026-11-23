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

    protected $fillable = ['record_id', 'tipo', 'path', 'thumb_path', 'nome', 'tamanho', 'created_by'];

    public function record()
    {
        return $this->belongsTo(StoreMaintenanceRecord::class, 'record_id');
    }

    public function isFoto(): bool
    {
        return $this->tipo === 'foto';
    }

    public function extensao(): string
    {
        return mb_strtolower(pathinfo($this->nome, PATHINFO_EXTENSION));
    }

    /** É uma imagem que o browser mostra? (o HEIC do iPhone não conta) */
    public function isImagem(): bool
    {
        return in_array($this->extensao(), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    /** O ícone e a cor do tipo de ficheiro, para se perceber o que é sem abrir. */
    public function iconeConfig(): array
    {
        return match ($this->extensao()) {
            'pdf' => ['icon' => 'fa-file-pdf', 'cor' => '#c62828'],
            'doc', 'docx' => ['icon' => 'fa-file-word', 'cor' => '#1565c0'],
            'jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif' => ['icon' => 'fa-file-image', 'cor' => '#6d28d9'],
            default => ['icon' => 'fa-file', 'cor' => '#64748b'],
        };
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
