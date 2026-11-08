@props(['label', 'value', 'icon', 'tone' => 'blue', 'note' => ''])
<article {{ $attributes->class(['stat-card']) }}>
    <div class="stat-top">
        <span>{{ $label }}</span>
        <span class="stat-icon {{ $tone }}">
            <i aria-hidden="true" class="{{ $icon }}"></i>
        </span>
    </div>
    <strong class="stat-number">{{ $value }}</strong>
    <div class="stat-note">{{ $note }}</div>
</article>
