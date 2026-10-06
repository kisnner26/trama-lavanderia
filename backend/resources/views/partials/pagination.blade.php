@if ($records->hasPages())
<nav class="pagination" aria-label="páginas del registro">
    @if ($records->previousPageUrl())<a href="{{ $records->previousPageUrl() }}">← anterior</a>@endif
    <span>página {{ $records->currentPage() }} de {{ $records->lastPage() }}</span>
    @if ($records->nextPageUrl())<a href="{{ $records->nextPageUrl() }}">siguiente →</a>@endif
</nav>
@endif
