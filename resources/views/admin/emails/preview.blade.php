@extends('layouts.app')

@section('title', 'Vista Previa de Correo')
@section('page-title', 'Editar Correo')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-envelope"></i> Interceptor de Correo - Edición WYSIWYG
        </h5>
    </div>
    <div class="card-body">
        <form id="emailEditForm">
            <div class="mb-3">
                <label class="form-label">Para:</label>
                <input type="email" class="form-control" value="{{ $emailLog->to }}" readonly>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Asunto:</label>
                <input type="text" class="form-control" id="emailSubject" value="{{ $emailLog->subject }}" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Contenido (HTML):</label>
                <!-- Editor WYSIWYG usando ContentEditable (Vanilla JS) -->
                <div class="border rounded p-3 mb-2" id="emailEditor" contenteditable="true" style="min-height: 400px; background: white;">
                    {!! $emailLog->body !!}
                </div>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Puedes editar el contenido directamente. Los cambios se guardarán antes de enviar.
                </small>
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" onclick="updateEmailContent()">
                    <i class="bi bi-save"></i> Guardar Cambios
                </button>
                <button type="button" class="btn btn-success" onclick="sendEmail()">
                    <i class="bi bi-send"></i> Enviar Correo
                </button>
                <a href="{{ route('admin.emails.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Toolbar básico para el editor -->
<div class="card mt-3">
    <div class="card-body">
        <div class="btn-toolbar" role="toolbar" aria-label="Editor de texto">
            <div class="btn-group me-2" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('bold')" title="Negrita">
                    <i class="bi bi-type-bold"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('italic')" title="Cursiva">
                    <i class="bi bi-type-italic"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('underline')" title="Subrayado">
                    <i class="bi bi-type-underline"></i>
                </button>
            </div>
            <div class="btn-group me-2" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertLink()" title="Insertar Enlace">
                    <i class="bi bi-link-45deg"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertImage()" title="Insertar Imagen">
                    <i class="bi bi-image"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const emailLogId = {{ $emailLog->id }};
    const editor = document.getElementById('emailEditor');
    
    function formatText(command) {
        document.execCommand(command, false, null);
        editor.focus();
    }
    
    function insertLink() {
        const url = prompt('Ingresa la URL:');
        if (url) {
            document.execCommand('createLink', false, url);
        }
    }
    
    function insertImage() {
        const url = prompt('Ingresa la URL de la imagen:');
        if (url) {
            document.execCommand('insertImage', false, url);
        }
    }
    
    async function updateEmailContent() {
        const body = editor.innerHTML;
        const subject = document.getElementById('emailSubject').value;
        
        try {
            const response = await fetchJSON(`/admin/emails/${emailLogId}/update-content`, {
                method: 'PUT',
                body: JSON.stringify({ body, subject }),
            });
            
            if (response.success) {
                alert('Cambios guardados correctamente');
            } else {
                alert('Error al guardar: ' + response.message);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
    
    async function sendEmail() {
        if (!confirm('¿Estás seguro de enviar este correo?')) {
            return;
        }
        
        // Guardar cambios primero
        await updateEmailContent();
        
        try {
            const response = await fetchJSON(`/admin/emails/${emailLogId}/send`, {
                method: 'POST',
            });
            
            if (response.success) {
                alert('Correo enviado a la cola de envío');
                window.location.href = '/admin/emails';
            } else {
                alert('Error al enviar: ' + response.message);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
</script>
@endpush
