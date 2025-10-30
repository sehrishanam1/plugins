<?php
function bdcrictime_featured_photos_shortcode() {
    ob_start(); ?>
    
    <div class="featured-photos-widget">
        <div class="widget-header">
            <h3>Featured Photos</h3>
        </div>
        <div id="featured-photos-list" class="photos-list"></div>
        <button id="see-all-btn" class="see-all-btn">See All</button>
    </div>

    <!-- Modal -->
    <div id="photoModal" class="photo-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-slider">
                <div id="slider-container"></div>
                <button class="prev-btn">&#10094;</button>
                <button class="next-btn">&#10095;</button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const apiURL = 'https://api.bdcrictime.com/api/v2/public/all-images?limit=100';
            const listContainer = document.getElementById('featured-photos-list');
            const modal = document.getElementById('photoModal');
            const sliderContainer = document.getElementById('slider-container');
            const closeModal = document.querySelector('.close');
            const prevBtn = document.querySelector('.prev-btn');
            const nextBtn = document.querySelector('.next-btn');
            let images = [];
            let currentIndex = 0;

            fetch(apiURL)
                .then(res => res.json())
                .then(data => {
                    images = data.imageList || [];
                    listContainer.innerHTML = images.slice(0, 4).map((item, index) => `
                        <div class="photo-item">
                            <img src="${item.image}" alt="${item.caption}">
                            <button class="expand-icon" data-index="${index}"></button>
                            <div class="photo-caption">${item.caption}</div>
                            <div class="photo-meta"> ${new Date(item.image_post_date).toDateString()}</div>
                        </div>
                    `).join('');
                });

            document.body.addEventListener('click', function(e) {
                if (e.target.classList.contains('expand-icon')) {
                    currentIndex = parseInt(e.target.dataset.index);
                    openModal(currentIndex);
                }
            });

            function openModal(index) {
                modal.style.display = 'block';
                renderSlide(index);
            }

            function renderSlide(index) {
                sliderContainer.innerHTML = `<img src="${images[index].image}" alt="${images[index].caption}">`;
            }

            closeModal.onclick = () => modal.style.display = 'none';
            window.onclick = e => { if (e.target == modal) modal.style.display = 'none'; }

            prevBtn.onclick = () => {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                renderSlide(currentIndex);
            }
            nextBtn.onclick = () => {
                currentIndex = (currentIndex + 1) % images.length;
                renderSlide(currentIndex);
            }
        });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('featured_photos', 'bdcrictime_featured_photos_shortcode');
?>
