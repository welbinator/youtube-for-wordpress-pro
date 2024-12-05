document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('video-modal');
    const iframe = document.getElementById('video-iframe');
    const closeModal = document.getElementById('video-modal-close');

    // Open modal on thumbnail click
    document.querySelectorAll('.video-thumbnail, .video-list-thumbnail').forEach(item => {
        item.addEventListener('click', function (event) {
            event.preventDefault();
            const videoUrl = this.getAttribute('data-video-url');

            if (videoUrl) {
                // Extract video ID from the URL
                const videoId = videoUrl.split('v=')[1]?.split('&')[0] || videoUrl.split('/embed/')[1];
                const embedUrl = `https://www.youtube.com/embed/${videoId}?pip=1&playsinline=1`;

                // Set iframe source
                iframe.src = embedUrl;
                modal.style.display = 'flex';
            }
        });
    });

    // Close modal
    closeModal.addEventListener('click', function () {
        modal.style.display = 'none';
        iframe.src = ''; // Stop video playback
    });

    // Close modal when clicking outside the content
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            iframe.src = ''; // Stop video playback
        }
    });
});
