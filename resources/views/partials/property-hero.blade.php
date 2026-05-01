<div data-testid="property-hero">
    @if(!empty($propertyHero['has_hero_image']) && !empty($propertyHero['hero_image_url']))
        <img
            src="{{ $propertyHero['hero_image_url'] }}"
            alt="{{ $propertyHero['hero_alt_text'] ?? ($property->name ?? 'Property image') }}"
            class="property-image-main mb-4"
            data-testid="property-hero-image"
        >
    @else
        <div class="property-image-placeholder mb-4" data-testid="property-hero-placeholder">
            <div class="text-center">
                <i class="fas fa-home fa-5x mb-3"></i>
                <h4 class="mb-0">No Image Available</h4>
            </div>
        </div>
    @endif
</div>
