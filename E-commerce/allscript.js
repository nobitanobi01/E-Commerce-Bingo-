// Helper functions
function $id(id) {
    return document.getElementById(id);
}

// Slider
function responsiveSlider() {
    const slider = $id("slider");
    const slideList = $id("slideWrap");
    const prev = $id("prev");
    const next = $id("next");

    if (!slider || !slideList || !prev || !next) return;

    let count = 1;
    let items = slideList.querySelectorAll("li").length;
    let sliderWidth = slider.offsetWidth;

    // Set initial width of ul
    slideList.style.width = sliderWidth * items + "px";

    window.addEventListener("resize", () => {
        sliderWidth = slider.offsetWidth;
        slideList.style.width = sliderWidth * items + "px";
        slideList.style.left = -(count - 1) * sliderWidth + "px";
    });

    const moveToSlide = () => {
        slideList.style.left = -(count - 1) * sliderWidth + "px";
    };

    const nextSlide = () => {
        if (count < items) {
            count++;
        } else {
            count = 1;
        }
        moveToSlide();
    };

    const prevSlide = () => {
        if (count > 1) {
            count--;
        } else {
            count = items;
        }
        moveToSlide();
    };

    next.addEventListener("click", (e) => {
        e.preventDefault();
        nextSlide();
    });

    prev.addEventListener("click", (e) => {
        e.preventDefault();
        prevSlide();
    });

    setInterval(nextSlide, 3000);
}

window.onload = responsiveSlider;


//Card Section
// Make all images clickable in card section and open in new tab
document.querySelectorAll('.container-wrapper .card img').forEach(image => {
    image.addEventListener('click', () => {
        const link = image.getAttribute('data-link');
        if (link) {
            window.open(link, '_blank');
        }
    });
});


// Mini_Slider
// Handle image click to open in new tab
document.querySelectorAll('.mini-slider img').forEach(img => {
    img.addEventListener('click', () => {
        const link = img.getAttribute('data-link');
        if (link) {
            window.open(link, '_blank');
        }
    });
});

// Handle slider navigation
document.querySelectorAll('.mini-slider').forEach(sliderSection => {
    const slider = sliderSection.querySelector('.slider');
    const slides = slider.querySelectorAll('.slide');
    let currentSlide = 0;

    const updateSlider = () => {
        slider.style.transform = `translateX(-${currentSlide * 100}%)`;
    };

    const nextButton = sliderSection.querySelector('.next-btn');
    const prevButton = sliderSection.querySelector('.prev-btn');

    nextButton.addEventListener('click', () => {
        currentSlide = (currentSlide + 1) % slides.length;
        updateSlider();
        nextSlide();
    });

    prevButton.addEventListener('click', () => {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        updateSlider();
    });

     /*// Auto-slide every 2 seconds
     setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        updateSlider();
    }, 5000); */

    // Initialize slider position
    updateSlider();
});



// Show button when scrolled down
window.onscroll = function () {
    const btn = document.getElementById("backToTop");
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
        btn.style.display = "block";
    } else {
        btn.style.display = "none";
    }
};

// Scroll to top when button is clicked
document.getElementById("backToTop").addEventListener("click", function () {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});