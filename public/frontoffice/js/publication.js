// Add to your frontoffice/js/main.js or create a new publications.js

document.addEventListener('DOMContentLoaded', function() {
    // Lazy load images
    const lazyImages = [].slice.call(document.querySelectorAll('.publication-image'));
    
    if ('IntersectionObserver' in window) {
        let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.remove('lazy');
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });

        lazyImages.forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    }
    
    // Add animation class when cards come into view
    const cards = document.querySelectorAll('.publication-card-wrapper');
    
    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, { threshold: 0.1 });
    
    cards.forEach(card => {
        cardObserver.observe(card);
    });
});