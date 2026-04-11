{{-- Full-screen image lightbox for attachment viewing - z-index must be higher than ticket modal (z-50) --}}
<div id="imageLightbox" class="fixed inset-0 hidden bg-black bg-opacity-90 flex items-center justify-center" style="z-index: 9999;">
    <div class="relative w-full h-full flex items-center justify-center">
        {{-- Close button - top left X --}}
        <button id="lightboxClose"
            class="absolute top-4 right-4 z-10 text-white hover:text-gray-300 bg-black bg-opacity-60 hover:bg-opacity-80 rounded-full w-10 h-10 flex items-center justify-center transition-colors"
            aria-label="Close lightbox">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Previous button --}}
        <button id="lightboxPrev"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-10 text-white hover:text-gray-300 bg-black bg-opacity-50 hover:bg-opacity-70 rounded-full w-12 h-12 flex items-center justify-center transition-colors"
            aria-label="Previous image">&larr;</button>

        {{-- Image --}}
        <img id="lightboxImage" src="" alt="Attachment preview"
            class="max-w-[90vw] max-h-[90vh] object-contain select-none">

        {{-- Next button --}}
        <button id="lightboxNext"
            class="absolute right-4 top-1/2 -translate-y-1/2 z-10 text-white hover:text-gray-300 bg-black bg-opacity-50 hover:bg-opacity-70 rounded-full w-12 h-12 flex items-center justify-center transition-colors"
            aria-label="Next image">&rarr;</button>
    </div>
</div>
