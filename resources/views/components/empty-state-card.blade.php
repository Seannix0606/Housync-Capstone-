@props([
    'icon' => 'fas fa-inbox',
    'title',
    'description' => null,
])

@once
@push('styles')
<style>
    .empty-state-card {
        text-align: center;
        padding: 2.5rem 1.75rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        max-width: 420px;
        margin-left: auto;
        margin-right: auto;
    }
    .empty-state-card--wide {
        max-width: none;
    }
    .empty-state-card__icon {
        width: 3.5rem;
        height: 3.5rem;
        margin: 0 auto 1rem;
        border-radius: 12px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .empty-state-card__icon i {
        font-size: 1.35rem;
        color: #64748b;
    }
    .empty-state-card__title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 0.5rem;
    }
    .empty-state-card__text {
        font-size: 0.9375rem;
        color: #64748b;
        margin: 0 0 1.25rem;
        line-height: 1.5;
    }
    .empty-state-card__cta {
        display: flex;
        justify-content: center;
    }
    body.dark-mode .empty-state-card {
        background: #1e293b;
        border-color: #334155;
    }
    body.dark-mode .empty-state-card__icon {
        background: #0f172a;
    }
    body.dark-mode .empty-state-card__icon i {
        color: #94a3b8;
    }
    body.dark-mode .empty-state-card__title {
        color: #f1f5f9;
    }
    body.dark-mode .empty-state-card__text {
        color: #94a3b8;
    }
</style>
@endpush
@endonce

<div {{ $attributes->class(['empty-state-card']) }}>
    <div class="empty-state-card__icon" aria-hidden="true">
        <i class="{{ $icon }}"></i>
    </div>
    <h3 class="empty-state-card__title">{{ $title }}</h3>
    @if(filled($description))
        <p class="empty-state-card__text">{{ $description }}</p>
    @endif
    @if(isset($action) && !$action->isEmpty())
        <div class="empty-state-card__cta">
            {{ $action }}
        </div>
    @endif
</div>
