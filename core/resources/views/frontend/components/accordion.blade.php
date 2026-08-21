<div class="accordion-card">
    <button type="button" class="accordion-header" aria-expanded="true" aria-controls="tutorials-content" id="tutorials-toggle">
        <span class="accordion-header-left">
            <span class="accordion-icon">
                <i class="ti ti-player-play"></i>
            </span>
            <span class="accordion-title">Tutorials & Guides</span>
        </span>
        <i class="ti ti-chevron-up accordion-caret"></i>
    </button>

    <div class="accordion-body" id="tutorials-content" role="region" aria-labelledby="tutorials-toggle">
        <div class="tutorial-list">
            @forelse($tutorials ?? [] as $tutorial)
            <a href="{{ $tutorial->url ?: '#' }}" class="tutorial-item" @if($tutorial->url && $tutorial->url !== '#') target="_blank" rel="noopener" @endif>
                <span class="tutorial-icon"><i class="ti ti-article"></i></span>
                <span class="tutorial-text">{{ $tutorial->title }}</span>
            </a>
            @empty
            <p class="text-muted mb-0">No tutorials available.</p>
            @endforelse
        </div>
    </div>
</div>