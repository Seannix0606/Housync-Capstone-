@foreach($floorGroups as $group)
    <div class="card-shell" id="floor-{{ $group['floor_key'] }}">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">{{ $group['floor_label'] }}</h5>
            <span class="badge bg-success">{{ $group['available_count'] }} available</span>
        </div>
        <div class="row">
            @foreach($group['units'] as $unit)
                @include('partials.property-unit-card', ['unit' => $unit, 'displayTerm' => $displayTerm])
            @endforeach
        </div>
    </div>
@endforeach
