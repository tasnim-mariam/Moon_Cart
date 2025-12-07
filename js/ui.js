// ==========================================
// MoonCart - UI Enhancements
// Modals, animations, and UI interactions
// ==========================================

// ========== Modal System ==========
class Modal {
    constructor(modalId) {
        this.modal = document.getElementById(modalId);
        this.closeBtn = this.modal?.querySelector(".modal-close");
        this.init();
    }

    init() {
        if (!this.modal) return;

        // Close button
        this.closeBtn?.addEventListener("click", () => this.close());

        // Click outside to close
        this.modal.addEventListener("click", (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // ESC key to close
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && this.isOpen()) {
                this.close();
            }
        });
    }

    open() {
        this.modal.style.display = "flex";
        document.body.style.overflow = "hidden";
        setTimeout(() => this.modal.classList.add("active"), 10);
    }

    close() {
        this.modal.classList.remove("active");
        setTimeout(() => {
            this.modal.style.display = "none";
            document.body.style.overflow = "";
        }, 300);
    }

    isOpen() {
        return this.modal.style.display === "flex";
    }
}

// ========== Image Gallery/Lightbox ==========
function initializeImageGallery() {
    const images = document.querySelectorAll("[data-lightbox]");

    if (images.length === 0) return;

    // Create lightbox
    const lightbox = document.createElement("div");
    lightbox.className = "lightbox";
    lightbox.innerHTML = `
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <img src="" alt="">
            <div class="lightbox-controls">
                <button class="lightbox-prev">&#10094;</button>
                <button class="lightbox-next">&#10095;</button>
            </div>
        </div>
    `;
    document.body.appendChild(lightbox);

    const lightboxImg = lightbox.querySelector("img");
    const closeBtn = lightbox.querySelector(".lightbox-close");
    const prevBtn = lightbox.querySelector(".lightbox-prev");
    const nextBtn = lightbox.querySelector(".lightbox-next");

    let currentIndex = 0;
    const imageArray = Array.from(images);

    function showImage(index) {
        currentIndex = index;
        lightboxImg.src = imageArray[index].src;
        lightbox.style.display = "flex";
        document.body.style.overflow = "hidden";
    }

    function closeLightbox() {
        lightbox.style.display = "none";
        document.body.style.overflow = "";
    }

    function showNext() {
        currentIndex = (currentIndex + 1) % imageArray.length;
        lightboxImg.src = imageArray[currentIndex].src;
    }

    function showPrev() {
        currentIndex =
            (currentIndex - 1 + imageArray.length) % imageArray.length;
        lightboxImg.src = imageArray[currentIndex].src;
    }

    // Event listeners
    images.forEach((img, index) => {
        img.addEventListener("click", () => showImage(index));
    });

    closeBtn.addEventListener("click", closeLightbox);
    prevBtn.addEventListener("click", showPrev);
    nextBtn.addEventListener("click", showNext);
    lightbox.addEventListener("click", (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    // Keyboard navigation
    document.addEventListener("keydown", (e) => {
        if (lightbox.style.display === "flex") {
            if (e.key === "ArrowRight") showNext();
            if (e.key === "ArrowLeft") showPrev();
            if (e.key === "Escape") closeLightbox();
        }
    });
}

// ========== Tooltip System ==========
function initializeTooltips() {
    const elements = document.querySelectorAll("[data-tooltip]");

    elements.forEach((element) => {
        const tooltip = document.createElement("div");
        tooltip.className = "tooltip";
        tooltip.textContent = element.dataset.tooltip;
        document.body.appendChild(tooltip);

        element.addEventListener("mouseenter", (e) => {
            const rect = element.getBoundingClientRect();
            tooltip.style.left = `${rect.left + rect.width / 2}px`;
            tooltip.style.top = `${rect.top - 35}px`;
            tooltip.classList.add("show");
        });

        element.addEventListener("mouseleave", () => {
            tooltip.classList.remove("show");
        });
    });
}

// ========== Accordion ==========
function initializeAccordions() {
    const accordions = document.querySelectorAll(".accordion-item");

    accordions.forEach((accordion) => {
        const header = accordion.querySelector(".accordion-header");
        const content = accordion.querySelector(".accordion-content");

        header.addEventListener("click", () => {
            const isActive = accordion.classList.contains("active");

            // Close all accordions
            accordions.forEach((item) => {
                item.classList.remove("active");
                item.querySelector(".accordion-content").style.maxHeight = null;
            });

            // Open clicked accordion if it wasn't active
            if (!isActive) {
                accordion.classList.add("active");
                content.style.maxHeight = content.scrollHeight + "px";
            }
        });
    });
}

// ========== Tabs ==========
function initializeTabs() {
    const tabGroups = document.querySelectorAll(".tabs");

    tabGroups.forEach((group) => {
        const tabs = group.querySelectorAll(".tab");
        const panels = group.querySelectorAll(".tab-panel");

        tabs.forEach((tab) => {
            tab.addEventListener("click", () => {
                const target = tab.dataset.tab;

                // Remove active class from all tabs and panels
                tabs.forEach((t) => t.classList.remove("active"));
                panels.forEach((p) => p.classList.remove("active"));

                // Add active class to clicked tab and corresponding panel
                tab.classList.add("active");
                const targetPanel = group.querySelector(
                    `[data-panel="${target}"]`
                );
                if (targetPanel) {
                    targetPanel.classList.add("active");
                }
            });
        });
    });
}

// ========== Countdown Timer ==========
function startCountdown(endDate, elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const countdown = setInterval(() => {
        const now = new Date().getTime();
        const distance = new Date(endDate).getTime() - now;

        if (distance < 0) {
            clearInterval(countdown);
            element.innerHTML = "<span>Offer Expired!</span>";
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
        );
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        element.innerHTML = `
            <div class="countdown-item">
                <span class="countdown-value">${days}</span>
                <span class="countdown-label">Days</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value">${hours}</span>
                <span class="countdown-label">Hours</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value">${minutes}</span>
                <span class="countdown-label">Minutes</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value">${seconds}</span>
                <span class="countdown-label">Seconds</span>
            </div>
        `;
    }, 1000);
}

// ========== Lazy Loading Images ==========
function initializeLazyLoading() {
    const images = document.querySelectorAll("img[data-src]");

    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute("data-src");
                observer.unobserve(img);
            }
        });
    });

    images.forEach((img) => imageObserver.observe(img));
}

// ========== Parallax Effect ==========
function initializeParallax() {
    const parallaxElements = document.querySelectorAll("[data-parallax]");

    window.addEventListener("scroll", () => {
        parallaxElements.forEach((element) => {
            const speed = element.dataset.parallax || 0.5;
            const yPos = -(window.pageYOffset * speed);
            element.style.transform = `translateY(${yPos}px)`;
        });
    });
}

// ========== Product Quick View ==========
function initializeQuickView() {
    const quickViewButtons = document.querySelectorAll(".quick-view-btn");

    quickViewButtons.forEach((button) => {
        button.addEventListener("click", (e) => {
            e.preventDefault();
            const productCard = button.closest(".product-card");
            const productData = {
                id: productCard.dataset.id,
                name: productCard.querySelector(".product-name").textContent,
                price: productCard.querySelector(".product-price").textContent,
                image: productCard.querySelector(".product-image img").src,
                description:
                    productCard.querySelector(".product-description")
                        ?.textContent || "No description available",
                category:
                    productCard.querySelector(".product-category")
                        ?.textContent || "General",
            };

            showQuickViewModal(productData);
        });
    });
}

function showQuickViewModal(product) {
    const modal = document.createElement("div");
    modal.className = "modal";
    modal.id = "quick-view-modal";
    modal.innerHTML = `
        <div class="modal-content quick-view">
            <span class="modal-close">&times;</span>
            <div class="quick-view-content">
                <div class="quick-view-image">
                    <img src="${product.image}" alt="${product.name}">
                </div>
                <div class="quick-view-info">
                    <span class="product-category">${product.category}</span>
                    <h2 class="product-name">${product.name}</h2>
                    <div class="product-price">${product.price}</div>
                    <p class="product-description">${product.description}</p>
                    <button class="btn btn-primary" onclick="addToCart({
                        id: '${product.id}',
                        name: '${product.name}',
                        price: ${product.price.replace("$", "")},
                        image: '${product.image}',
                        category: '${product.category}'
                    }); MoonCart.closeModal('quick-view-modal');">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    MoonCart.openModal("quick-view-modal");

    // Setup close button
    modal.querySelector(".modal-close").addEventListener("click", () => {
        MoonCart.closeModal("quick-view-modal");
        setTimeout(() => modal.remove(), 300);
    });
}

// ========== Rating System ==========
function initializeRatings() {
    const ratingElements = document.querySelectorAll(".rating-input");

    ratingElements.forEach((element) => {
        const stars = element.querySelectorAll(".star");
        const input = element.querySelector("input");

        stars.forEach((star, index) => {
            star.addEventListener("click", () => {
                input.value = index + 1;
                updateStars(stars, index);
            });

            star.addEventListener("mouseenter", () => {
                updateStars(stars, index);
            });
        });

        element.addEventListener("mouseleave", () => {
            updateStars(stars, input.value - 1);
        });
    });
}

function updateStars(stars, index) {
    stars.forEach((star, i) => {
        if (i <= index) {
            star.classList.add("active");
        } else {
            star.classList.remove("active");
        }
    });
}

// ========== Form Field Animation ==========
function initializeFormAnimations() {
    const formGroups = document.querySelectorAll(".form-group");

    formGroups.forEach((group) => {
        const input = group.querySelector("input, textarea, select");
        const label = group.querySelector("label");

        if (!input || !label) return;

        input.addEventListener("focus", () => {
            label.classList.add("active");
        });

        input.addEventListener("blur", () => {
            if (!input.value) {
                label.classList.remove("active");
            }
        });

        // Check on page load
        if (input.value) {
            label.classList.add("active");
        }
    });
}

// ========== Copy to Clipboard ==========
function copyToClipboard(text, message = "Copied to clipboard!") {
    navigator.clipboard
        .writeText(text)
        .then(() => {
            MoonCart.showNotification(message, "success");
        })
        .catch(() => {
            MoonCart.showNotification("Failed to copy", "error");
        });
}

// ========== Share Functionality ==========
function shareProduct(product) {
    if (navigator.share) {
        navigator
            .share({
                title: product.name,
                text: `Check out ${product.name} on MoonCart!`,
                url: window.location.href,
            })
            .catch((err) => console.log("Error sharing:", err));
    } else {
        // Fallback: copy link
        copyToClipboard(window.location.href, "Link copied to clipboard!");
    }
}

// ========== Initialize all UI enhancements ==========
document.addEventListener("DOMContentLoaded", function () {
    initializeImageGallery();
    initializeTooltips();
    initializeAccordions();
    initializeTabs();
    initializeLazyLoading();
    initializeParallax();
    initializeQuickView();
    initializeRatings();
    initializeFormAnimations();

    // Initialize filter tabs if on products page
    if (window.location.pathname.includes("products.html")) {
        initializeFilterTabs();
    }
});

// Export functions
window.UIHelpers = {
    Modal,
    startCountdown,
    copyToClipboard,
    shareProduct,
    showQuickViewModal,
};
