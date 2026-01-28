<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\EmailTemplateResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;
    
    public function getTitle(): string | Htmlable
    {
        $parentId = request()->get('parent');
        if ($parentId) {
            $parent = \App\Models\EmailTemplate::find($parentId);
            if ($parent) {
                return "Crear Variación: {$parent->name}";
            }
        }
        return parent::getTitle();
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $parentId = request()->get('parent');
        if ($parentId) {
            $parent = \App\Models\EmailTemplate::find($parentId);
            if ($parent) {
                $data['is_variation'] = true;
                $data['parent_template_id'] = $parent->id;
                $data['type'] = $parent->type;
                $data['recipient_type'] = $parent->recipient_type;
                // Copiar contenido como base
                $data['subject'] = $parent->subject . ' (Variación)';
                $data['body'] = $parent->body;
                $data['variables'] = $parent->variables;
            }
        }
        
        return $data;
    }
}
