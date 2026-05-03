<div class="unit-card-col mb-3 px-2" data-testid="unit-card">
    <div class="card h-100 unit-card">
        @if($unit['image_url'])
            <img src="{{ $unit['image_url'] }}" alt="{{ $unit['label'] }}" class="card-img-top unit-image" data-testid="unit-card-image">
        @else
            <div class="unit-image-placeholder d-flex align-items-center justify-content-center text-white" data-testid="unit-card-placeholder">
                <div class="text-center">
                    <i class="fas fa-home fa-2x mb-1"></i>
                    <div>No image</div>
                </div>
            </div>
        @endif

        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <h6 class="card-title mb-0">{{ $unit['label'] }}</h6>
                <span class="badge bg-success">{{ $unit['status'] }}</span>
            </div>

            <div class="text-muted small mb-2">{{ $unit['unit_type'] }}</div>
            <div class="small text-muted mb-2">
                @if(!is_null($unit['bedrooms'])) <span>{{ $unit['bedrooms'] }} bed</span> @endif
                @if(!is_null($unit['bathrooms'])) <span> • {{ $unit['bathrooms'] }} bath</span> @endif
                @if(!is_null($unit['floor_area'])) <span> • {{ number_format((float) $unit['floor_area']) }} m²</span> @endif
            </div>

            <div class="fw-bold text-primary mb-3">{{ $unit['rent_display'] }}</div>

            <div class="d-grid gap-2">
                <a class="btn btn-primary btn-sm" href="{{ $unit['details_url'] }}">
                    View {{ $displayTerm }}
                </a>
                @auth
                    @if(Auth::user()->role === 'tenant')
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#applyUnitModal{{ $unit['id'] }}">
                            Apply to this {{ $displayTerm }}
                        </button>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</div>
