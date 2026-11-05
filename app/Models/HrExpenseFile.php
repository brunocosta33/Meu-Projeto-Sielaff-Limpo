<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Comprovativo de uma despesa: fotografia, PDF, folha de cálculo… */
class HrExpenseFile extends Model
{
    protected $fillable = ['hr_expense_id', 'path', 'nome', 'mime', 'tamanho', 'uploaded_by'];

    public function expense()
    {
        return $this->belongsTo(HrExpense::class, 'hr_expense_id');
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime === 'application/pdf';
    }

    /** Ícone conforme o tipo de ficheiro, para a lista. */
    public function icon(): string
    {
        if ($this->isImage()) {
            return 'fa-file-image';
        }
        if ($this->isPdf()) {
            return 'fa-file-pdf';
        }

        return match (strtolower(pathinfo($this->nome, PATHINFO_EXTENSION))) {
            'xls', 'xlsx', 'csv' => 'fa-file-excel',
            'doc', 'docx' => 'fa-file-word',
            default => 'fa-file',
        };
    }

    public function sizeLabel(): string
    {
        $kb = $this->tamanho / 1024;

        return $kb >= 1024 ? round($kb / 1024, 1) . ' MB' : max(1, round($kb)) . ' KB';
    }
}
