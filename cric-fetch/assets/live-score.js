jQuery(document).ready(function ($) {

    function loadLiveScores() {
        const track = $('.live-track');

        // Cache old content temporarily
        const oldContent = track.html();

        $.ajax({
            url: lcs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'lcs_get_live_cricket',
                security: lcs_ajax.nonce
            },
            beforeSend: function () {
                // Add a subtle opacity fade but keep layout fixed
                track.css({ opacity: 1 });
            },
            success: function (response) {
                // Replace cards silently (no "refreshing" message)
                track.html(response);

                // Fade back in smoothly
                track.css({ opacity: 1, transition: "opacity 1s ease" });

                // Reinitialize slider buttons after update
                initializeSlider();
            },
            error: function () {
                // Restore previous content if error occurs
                track.html(oldContent);
                track.css({ opacity: 1 });
                console.error("Error updating live scores");
            }
        });
    }

    function initializeSlider() {
        const track = document.querySelector(".live-track");
        const prev = document.querySelector(".slider-btn.prev");
        const next = document.querySelector(".slider-btn.next");

        if (track && prev && next) {
            const cards = track.querySelectorAll(".live-card");
            const visibleSlides = 4;
            const cardWidth = cards[0]?.offsetWidth + 20 || 300;
            let currentIndex = 0;

            function updateSlider() {
                track.scrollTo({
                    left: currentIndex * cardWidth,
                    behavior: "smooth"
                });
            }

            next.onclick = () => {
                if (currentIndex < cards.length - visibleSlides) currentIndex++;
                updateSlider();
            };

            prev.onclick = () => {
                if (currentIndex > 0) currentIndex--;
                updateSlider();
            };
        }
        
        $('.live-track').owlCarousel({
    loop: false, // like infinite: false
    margin: 5, // space between slides (optional)
    dots: false,
    nav: true, // shows prev/next arrows
    smartSpeed: 300,
    responsive: {
      0: {
        items: 1,
      },
      767: {
        items: 1,
      },
      768: {
        items: 2,
      },
      1023: {
        items: 2,
      },
      1024: {
        items: 3,
      },
      1180: {
        items: 3,
      },
      1200: {
        items: 4,
      },
      1440: {
        items: 4,
      }
    },
  });
    }

    // Initialize once on page load
    initializeSlider();

    // Auto-refresh every 1 minute (no layout shift)
    loadLiveScores();
    setInterval(loadLiveScores, 60000);
    
});