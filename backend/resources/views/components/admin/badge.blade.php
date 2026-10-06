@props(['tone' => 'gray'])

<span {{ $attributes->class([
    'inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium whitespace-nowrap',
    'bg-[#e3f1ea] text-footer' => $tone === 'green',
    'bg-[#fde5e5] text-[#c0262f]' => $tone === 'red',
    'bg-[#fff3d6] text-[#9a6700]' => $tone === 'amber',
    'bg-[#eef0ee] text-slate' => $tone === 'gray',
]) }}>{{ $slot }}</span>
