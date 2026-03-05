@if($units->count() > 0)
    <div class="properties-grid">
        @foreach($units as $unit)
            @php
                $property = $unit->property;
                $galleryImages = $unit->images ?? $property->gallery_images ?? [];
            @endphp
            <div class="property-card">
                <a href="{{ route('property.show', ($property->slug ?? $property->id) . '-unit-' . $unit->id) }}" class="property-image-link">
                    @if(count($galleryImages) > 0)
                        <div class="property-image-carousel" data-carousel-id="carousel-unit-{{ $unit->id }}">
                            <div class="carousel-container">
                                @foreach($galleryImages as $index => $img)
                                    <div class="carousel-slide {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ $img }}" alt="{{ $property->name ?? 'Unit' }} - Image {{ $index + 1 }}" class="property-image" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                            @if(count($galleryImages) > 1)
                                <div class="carousel-controls">
                                    <button class="carousel-btn carousel-prev" onclick="event.preventDefault(); slideCarousel('carousel-unit-{{ $unit->id }}', -1)">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button class="carousel-btn carousel-next" onclick="event.preventDefault(); slideCarousel('carousel-unit-{{ $unit->id }}', 1)">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="carousel-indicators">
                                    @foreach($galleryImages as $index => $img)
                                        <span class="carousel-dot {{ $index === 0 ? 'active' : '' }}" onclick="event.preventDefault(); goToSlide('carousel-unit-{{ $unit->id }}', {{ $index }})"></span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="property-image-placeholder">
                            <div>
                                <i class="fas fa-home fa-3x mb-2"></i>
                                <div>No Image Available</div>
                            </div>
                        </div>
                    @endif
                </a>

                <div class="property-content">
                    <span class="property-type">{{ ucfirst($unit->unit_type ?? $property->property_type ?? 'Unit') }}</span>
                    
                    <h3 class="property-title">
                        {{ $property->name ?? 'Property' }} - Unit {{ $unit->unit_number ?? $unit->id }}
                    </h3>
                    
                    @if($property && $property->address)
                        <div class="property-address">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ Str::limit($property->address, 40) }}</span>
                        </div>
                    @endif

                    <div class="property-features">
                        @if($unit->bedrooms)
                            <div class="property-feature">
                                <i class="fas fa-bed"></i>
                                <span>{{ $unit->bedrooms }} Bed</span>
                            </div>
                        @endif
                        @if($unit->bathrooms)
                            <div class="property-feature">
                                <i class="fas fa-bath"></i>
                                <span>{{ $unit->bathrooms }} Bath</span>
                            </div>
                        @endif
                        @if($unit->floor_area)
                            <div class="property-feature">
                                <i class="fas fa-ruler-combined"></i>
                                <span>{{ number_format($unit->floor_area) }} m²</span>
                            </div>
                        @endif
                    </div>

                    <div class="property-price">
                        ₱{{ number_format($unit->rent_amount ?? 0, 2) }}
                        <small style="font-size: 0.875rem; font-weight: 400; color: #64748b;">/month</small>
                    </div>

                    <span class="property-availability {{ $unit->status ?? 'available' }}">
                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                        {{ ucfirst($unit->status ?? 'Available') }}
                    </span>

                    <!-- View Details Button -->
                    <div class="property-actions" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                        <a href="{{ route('property.show', ($property->slug ?? $property->id) . '-unit-' . $unit->id) }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state" style="text-align: center; padding: 3rem; background: white; border-radius: 12px;">
        <i class="fas fa-search" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
        <h3 style="color: #64748b; margin-bottom: 0.5rem;">No Units Found</h3>
        <p style="color: #94a3b8;">Try adjusting your filters to see more results, or check back later for new listings.</p>
    </div>
@endif

