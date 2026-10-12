@props(['name', 'label', 'options' => []])

<select name="{{ $name }}" aria-label="{{ $label }}" class="admin-input w-auto min-w-[150px] pr-8" data-autosubmit>
    <option value="">{{ $label }}</option>
    @foreach ($options as $value => $text)
        <option value="{{ $value }}" @selected((string) request($name) === (string) $value)>{{ $text }}</option>
    @endforeach
</select>
