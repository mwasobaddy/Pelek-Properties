<picture class="responsive-image {{ $class }}">
    {{-- WebP version --}}
    <source
        type="image/webp"
        srcset="{{ $getWebpSrcset() }}"
        sizes="{{ $sizes }}"
    >
    {{-- Original format version --}}
    <source
        type="{{ $image->metadata['mime_type'] ?? 'image/jpeg' }}"
        srcset="{{ $getSrcset() }}"
        sizes="{{ $sizes }}"
    >
    {{-- Fallback image --}}
    <img
        src="{{ Storage::disk('public')->url($image->image_path) }}"
        alt="{{ $alt }}"
        class="{{ $class }}"
        loading="lazy"
    >
</picture>
