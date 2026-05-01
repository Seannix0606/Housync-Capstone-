@if(!empty($floorGroups))
    <div class="card-shell">
        <h5 class="mb-3">Availability by Floor</h5>
        <div class="d-flex flex-wrap gap-2">
            @foreach($floorGroups as $group)
                <a class="btn btn-sm btn-outline-primary" href="#floor-{{ $group['floor_key'] }}">
                    {{ $group['floor_label'] }} ({{ $group['available_count'] }} available)
                </a>
            @endforeach
        </div>
    </div>
@endif
