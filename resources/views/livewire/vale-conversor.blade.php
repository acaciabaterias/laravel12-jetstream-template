<div>
    {{-- This component acts purely as a listener orchestrating DB transactions invisibly. 
         Any UI messages (flash) are surfaced by the wrapper or the primary layout. --}}
    @if(session()->has('error'))
        <div class="fixed top-4 right-4 z-50 p-4 bg-red-100 text-red-700 rounded-md shadow-lg border border-red-200">
            {{ session('error') }}
        </div>
    @endif
</div>
