@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null])
<div class="field">
 <label for="{{ $name }}">{{ $label }} @if($required)<span class="required" aria-hidden="true">*</span>@endif</label>
 <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : old($name, $value) }}" @required($required) @if($hint || $errors->has($name)) aria-describedby="{{ $name }}-help" @endif @if($errors->has($name)) aria-invalid="true" @endif {{ $attributes }}>
 @if($errors->has($name))<small class="error" id="{{ $name }}-help">{{ $errors->first($name) }}</small>@elseif($hint)<small id="{{ $name }}-help">{{ $hint }}</small>@endif
</div>
