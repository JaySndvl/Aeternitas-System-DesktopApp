@props([
    'label',
    'name',
    'type' => 'text',
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'maxlength' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'rows' => null
])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    @if($type === 'textarea')
        <textarea 
            name="{{ $name }}" 
            id="{{ $name }}" 
            {{ $required ? 'required' : '' }}
            @if($rows) rows="{{ $rows }}" @endif
            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error($name) border-red-500 @enderror"
            placeholder="{{ $placeholder }}"
            {{ $attributes }}
        >{{ old($name, $value) }}</textarea>
    @else
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ old($name, $value) }}"
            {{ $required ? 'required' : '' }}
            @if($maxlength) maxlength="{{ $maxlength }}" @endif
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            @if($step) step="{{ $step }}" @endif
            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error($name) border-red-500 @enderror"
            placeholder="{{ $placeholder }}"
            {{ $attributes }}
        >
    @endif
    
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
