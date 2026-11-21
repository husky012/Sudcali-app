@if ($paginador->hasPages())
    <nav class="paginacion-pos" aria-label="Paginación">
        @if ($paginador->onFirstPage())
            <span aria-disabled="true">← Anterior</span>
        @else
            <a href="{{ $paginador->previousPageUrl() }}" rel="prev">← Anterior</a>
        @endif

        <span class="pagina-actual-pos">Página {{ $paginador->currentPage() }} de {{ $paginador->lastPage() }}</span>

        @if ($paginador->hasMorePages())
            <a href="{{ $paginador->nextPageUrl() }}" rel="next">Siguiente →</a>
        @else
            <span aria-disabled="true">Siguiente →</span>
        @endif
    </nav>
@endif
