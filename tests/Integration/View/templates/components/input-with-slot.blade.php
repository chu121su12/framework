@props([
    'input'
])

<div>
    <input type="text" {{ $input->attributes->class_('input') }} />
</div>
