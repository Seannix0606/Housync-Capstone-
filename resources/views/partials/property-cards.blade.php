@if($properties->count() > 0)
    <div class="properties-grid">
        @foreach($properties as $property)
            <div class="property-card">
                <a href="{{ route('property.show', $property->slug ?? $property->id) }}" class="property-image-link">
                    @php
                        $coverImage = $property->cover_image_url;
                        $gallerySource = $property->gallery_urls ?? [];

                        $galleryImages = [];
                        if ($coverImage) {
                            $galleryImages[] = $coverImage;
                        }
                        foreach ($gallerySource as $img) {
                            if ($img && $img !== $coverImage) {
                                $galleryImages[] = $img;
                            }
                        }
                    @endphp
                    @if(count($galleryImages) > 0)
                        <div class="property-image-carousel" data-carousel-id="carousel-{{ $property->id }}">
                            <div class="carousel-container">
                                @foreach($galleryImages as $index => $img)
                                    <div class="carousel-slide {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ $img }}" alt="{{ $property->name ?? 'Property' }} - Image {{ $index + 1 }}" class="property-image" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                            @if(count($galleryImages) > 1)
                                <div class="carousel-controls">
                                    <button class="carousel-btn carousel-prev" onclick="event.preventDefault(); slideCarousel('carousel-{{ $property->id }}', -1)">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button class="carousel-btn carousel-next" onclick="event.preventDefault(); slideCarousel('carousel-{{ $property->id }}', 1)">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="carousel-indicators">
                                    @foreach($galleryImages as $index => $img)
                                        <span class="carousel-dot {{ $index === 0 ? 'active' : '' }}" onclick="event.preventDefault(); goToSlide('carousel-{{ $property->id }}', {{ $index }})"></span>
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
                    <span class="property-type">{{ ucfirst($property->property_type ?? 'property') }}</span>
                    
                    <h3 class="property-title">
                        <a href="{{ route('property.show', $property->slug ?? $property->id) }}" class="property-title-link">{{ $property->name ?? 'Untitled Property' }}</a>
                    </h3>
                    
                    @if($property->address)
                        <div class="property-address">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ Str::limit(trim(($property->address ?? '').' '.($property->city ?? '')), 60) }}</span>
                        </div>
                    @endif

                    <div class="property-features">
                        <div class="property-feature">
                            <i class="fas fa-door-open"></i>
                            <span>Available units: {{ $property->available_units_count ?? 0 }}</span>
                        </div>
                    </div>

                    <div class="property-price">
                        @if(!is_null($property->min_available_rent))
                            From ₱{{ number_format((float) $property->min_available_rent, 2) }}
                            <small style="font-size: 0.875rem; font-weight: 400; color: #64748b;">/month</small>
                        @else
                            <span style="font-size: 1rem; color: #64748b;">Price on inquiry</span>
                        @endif
                    </div>

                    <span class="property-availability {{ ($property->available_units_count ?? 0) > 0 ? 'available' : 'occupied' }}">
                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                        {{ ($property->available_units_count ?? 0) > 0 ? 'Available' : 'Unavailable' }}
                    </span>

                    <!-- View Details Button -->
                    <div class="property-actions" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                        <a href="{{ route('property.show', $property->slug ?? $property->id) }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-eye me-1"></i> View Property
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>No Properties Found</h3>
        <p>Try adjusting your filters to see more listings.</p>
    </div>
@endif

