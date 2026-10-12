@props(['action', 'confirm' => 'Hapus data ini?', 'label' => 'Hapus'])

@can('admin')
    <form action="{{ $action }}" method="POST" data-confirm="{{ $confirm }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="admin-icon-btn hover:text-[#c0262f]" aria-label="{{ $label }}" title="Hapus">
            <x-admin.icon name="trash" width="16" height="16" />
        </button>
    </form>
@endcan
