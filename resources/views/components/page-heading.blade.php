@props(['title', 'description' => ''])
<div class="page-heading">
    <div>
        <div class="eyebrow">SISMAT / {{ $title }}</div>
        <h1>{{ $title }}</h1>
        @if ($description)
            <p>{{ $description }}</p>
        @endif
    </div>
    <div class="heading-actions">{{ $slot }}</div>
</div>
