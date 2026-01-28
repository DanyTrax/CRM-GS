<div class="p-4 bg-white rounded-lg shadow">
    <div class="mb-4">
        <h3 class="text-lg font-semibold mb-2">Tipo de Mensaje:</h3>
        <p class="text-gray-700">{{ $message->message_type }}</p>
    </div>
    
    @if($message->subject)
    <div class="mb-4">
        <h3 class="text-lg font-semibold mb-2">Asunto:</h3>
        <p class="text-gray-700">{{ $message->subject }}</p>
    </div>
    @endif
    
    <div class="mb-4">
        <h3 class="text-lg font-semibold mb-2">Cuerpo del Mensaje:</h3>
        <div class="border border-gray-300 rounded p-4 bg-gray-50">
            {!! $message->body !!}
        </div>
    </div>
    
    @if($message->error_message)
    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
        <h3 class="text-lg font-semibold mb-2 text-red-800">Error:</h3>
        <p class="text-red-700">{{ $message->error_message }}</p>
    </div>
    @endif
</div>
