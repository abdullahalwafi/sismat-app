<nav class="pagination" aria-label="Navigasi halaman">
    <span>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }}
        data</span>
    <button type="button" class="page-button" wire:click="previousPage" @disabled($paginator->onFirstPage()) aria-label="Halaman sebelumnya">
        <i aria-hidden="true" class="ri-arrow-left-s-line"></i>
    </button>
    @foreach (range(max(1, $paginator->currentPage() - 1), min($paginator->lastPage(), $paginator->currentPage() + 1)) as $page)
        <button type="button" wire:key="page-{{ $page }}" wire:click="gotoPage({{ $page }})" @class([
            'page-button',
            'current' => $paginator->currentPage() === $page,
        ])
            @if ($paginator->currentPage() === $page) aria-current="page" @endif>{{ $page }}
        </button>
    @endforeach
    <button type="button" class="page-button" wire:click="nextPage" @disabled(!$paginator->hasMorePages()) aria-label="Halaman berikutnya">
        <i aria-hidden="true" class="ri-arrow-right-s-line"></i>
    </button>
</nav>
