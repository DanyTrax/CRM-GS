<div class="p-4 bg-white rounded-lg shadow">
    <div class="mb-4">
        <h3 class="text-lg font-semibold mb-2">Asunto:</h3>
        <p class="text-gray-700">{{ $template->subject }}</p>
    </div>
    
    <div class="mb-4">
        <h3 class="text-lg font-semibold mb-2">Cuerpo del Email:</h3>
        <div class="border border-gray-300 rounded p-4 bg-gray-50">
            {!! $template->body !!}
        </div>
    </div>
    
    @if($template->variables && count($template->variables) > 0)
    <div class="mt-4">
        <h3 class="text-lg font-semibold mb-2">Variables Disponibles:</h3>
        <ul class="list-disc list-inside text-sm text-gray-600">
            @foreach($template->variables as $variable)
            <li>{{ $variable }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
