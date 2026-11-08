@props(['title', 'close' => 'closeForm', 'wide' => false])
<div class="modal-layer" x-data x-init="$nextTick(() => $refs.dialog.focus())" x-on:keydown.escape.window="$wire.{{ $close }}()" x-trap.inert.noscroll="true"
    role="presentation">
    <div class="modal-backdrop" wire:click="{{ $close }}"></div>
    <section tabindex="-1" x-ref="dialog" @class(['modal-dialog', 'modal-wide' => $wide]) role="dialog" aria-modal="true" aria-labelledby="dialog-title">
        <div class="modal-header">
            <div>
                <span class="eyebrow">DATA APLIKASI</span>
                <h2 id="dialog-title">{{ $title }}</h2>
            </div>
            <button type="button" class="icon-button" wire:click="{{ $close }}" aria-label="Tutup modal">
                <i aria-hidden="true" class="ri-close-line"></i>
            </button>
        </div>
        {{ $slot }}
    </section>
</div>
