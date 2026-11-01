{{--
    Round photo with the first letter behind it, which shows when there is no photo
    or the file is missing. Takes $name, and optionally $image, $sex, $size ('sm', 'xl') and $alt.
--}}
@php
    // The letter as it is read, so a matra or conjunct stays attached: "दे", "क्ष".
    preg_match('/^\X(?:(?<=\x{094D})\X)*/u', trim((string) $name), $firstLetter);
@endphp
<span @class([
    'lb-avatar',
    'lb-avatar--' . ($size ?? '') => ! empty($size),
    'lb-avatar--female' => ($sex ?? null) === 'female',
])>
    <span class="lb-avatar__letter" aria-hidden="true">{{ $firstLetter[0] ?? '?' }}</span>
    @if(! empty($image))
        <img src="{{ Storage::url($image) }}" alt="{{ $alt ?? '' }}" loading="lazy" decoding="async" onerror="this.remove()">
    @endif
</span>
