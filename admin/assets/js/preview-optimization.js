/**
 * Preview Optimization for GREATER Admin Panel
 * Handles fast loading of images and videos with lazy loading and caching
 */

class PreviewOptimizer {
    constructor() {
        this.imageCache = new Map();
        this.loadingQueue = new Set();
        this.init();
    }

    init() {
        // Initialize lazy loading
        this.setupLazyLoading();
        
        // Setup preview modals
        this.setupPreviewModals();
        
        // Setup progressive loading
        this.setupProgressiveLoading();
    }

    /**
     * Setup lazy loading for images and videos
     */
    setupLazyLoading() {
        const options = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadMedia(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, options);

        // Observe all lazy images and videos
        document.querySelectorAll('[data-lazy-src]').forEach(el => {
            observer.observe(el);
        });
    }

    /**
     * Load media with optimization
     */
    async loadMedia(element) {
        const src = element.dataset.lazySrc;
        const type = element.dataset.type;
        
        if (this.loadingQueue.has(src)) {
            return; // Already loading
        }
        
        this.loadingQueue.add(src);
        
        try {
            if (type === 'image') {
                await this.loadImage(element, src);
            } else if (type === 'video') {
                await this.loadVideo(element, src);
            }
        } catch (error) {
            console.error('Error loading media:', error);
            this.showErrorPlaceholder(element);
        } finally {
            this.loadingQueue.delete(src);
        }
    }

    /**
     * Load image with thumbnail first, then full size
     */
    async loadImage(imgElement, src) {
        const fileName = src.split('/').pop();
        
        // Show loading spinner
        this.showLoadingSpinner(imgElement);
        
        try {
            // Load thumbnail first for quick preview
            const thumbnailSrc = `file.php?file=${fileName}&action=thumbnail`;
            await this.preloadImage(thumbnailSrc);
            
            imgElement.src = thumbnailSrc;
            imgElement.classList.add('thumbnail-loaded');
            
            // Then load full size image
            const fullSrc = `file.php?file=${fileName}&action=view`;
            await this.preloadImage(fullSrc);
            
            // Smooth transition to full image
            const tempImg = new Image();
            tempImg.onload = () => {
                imgElement.src = fullSrc;
                imgElement.classList.remove('thumbnail-loaded');
                imgElement.classList.add('full-loaded');
                this.hideLoadingSpinner(imgElement);
            };
            tempImg.src = fullSrc;
            
        } catch (error) {
            console.error('Error loading image:', error);
            this.showErrorPlaceholder(imgElement);
        }
    }

    /**
     * Load video with poster frame
     */
    async loadVideo(videoElement, src) {
        const fileName = src.split('/').pop();
        
        try {
            // Set video source
            videoElement.src = `file.php?file=${fileName}&action=view`;
            
            // Add loading attributes for better performance
            videoElement.setAttribute('preload', 'metadata');
            videoElement.setAttribute('playsinline', '');
            
            // Show loading state
            this.showLoadingSpinner(videoElement);
            
            videoElement.addEventListener('loadedmetadata', () => {
                this.hideLoadingSpinner(videoElement);
                videoElement.classList.add('video-loaded');
            }, { once: true });
            
        } catch (error) {
            console.error('Error loading video:', error);
            this.showErrorPlaceholder(videoElement);
        }
    }

    /**
     * Preload image and cache it
     */
    preloadImage(src) {
        return new Promise((resolve, reject) => {
            if (this.imageCache.has(src)) {
                resolve(this.imageCache.get(src));
                return;
            }

            const img = new Image();
            img.onload = () => {
                this.imageCache.set(src, img);
                resolve(img);
            };
            img.onerror = reject;
            img.src = src;
        });
    }

    /**
     * Setup preview modals for full-screen viewing
     */
    setupPreviewModals() {
        document.addEventListener('click', (e) => {
            const previewTrigger = e.target.closest('[data-preview]');
            if (previewTrigger) {
                e.preventDefault();
                this.showPreviewModal(previewTrigger);
            }
        });
    }

    /**
     * Show full-screen preview modal
     */
    showPreviewModal(trigger) {
        const fileName = trigger.dataset.preview;
        const type = trigger.dataset.type;
        
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'preview-modal';
        modal.innerHTML = `
            <div class="preview-modal-backdrop"></div>
            <div class="preview-modal-content">
                <div class="preview-modal-header">
                    <span class="preview-modal-title">${fileName}</span>
                    <button class="preview-modal-close">&times;</button>
                </div>
                <div class="preview-modal-body">
                    <div class="preview-loading">
                        <div class="spinner"></div>
                        <p>Loading...</p>
                    </div>
                </div>
                <div class="preview-modal-footer">
                    <a href="file.php?file=${fileName}&action=download" 
                       class="btn btn-primary" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Load content
        this.loadModalContent(modal, fileName, type);
        
        // Setup close handlers
        modal.querySelector('.preview-modal-close').onclick = () => this.closeModal(modal);
        modal.querySelector('.preview-modal-backdrop').onclick = () => this.closeModal(modal);
        
        // Escape key to close
        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                this.closeModal(modal);
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
        
        // Show modal
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });
    }

    /**
     * Load content for modal
     */
    async loadModalContent(modal, fileName, type) {
        const body = modal.querySelector('.preview-modal-body');
        
        try {
            if (type === 'image') {
                const img = document.createElement('img');
                img.className = 'preview-image';
                img.style.maxWidth = '100%';
                img.style.maxHeight = '80vh';
                img.style.objectFit = 'contain';
                
                // Load with progressive enhancement
                const thumbnailSrc = `file.php?file=${fileName}&action=thumbnail`;
                const fullSrc = `file.php?file=${fileName}&action=view`;
                
                // Show thumbnail first
                await this.preloadImage(thumbnailSrc);
                img.src = thumbnailSrc;
                body.innerHTML = '';
                body.appendChild(img);
                
                // Then upgrade to full image
                await this.preloadImage(fullSrc);
                img.src = fullSrc;
                
            } else if (type === 'video') {
                const video = document.createElement('video');
                video.className = 'preview-video';
                video.controls = true;
                video.style.maxWidth = '100%';
                video.style.maxHeight = '80vh';
                video.src = `file.php?file=${fileName}&action=view`;
                
                body.innerHTML = '';
                body.appendChild(video);
            }
        } catch (error) {
            body.innerHTML = '<p class="error">Error loading preview</p>';
        }
    }

    /**
     * Close modal
     */
    closeModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(modal);
        }, 300);
    }

    /**
     * Setup progressive loading for better UX
     */
    setupProgressiveLoading() {
        // Add CSS for smooth transitions
        const style = document.createElement('style');
        style.textContent = `
            .loading-spinner {
                display: inline-block;
                width: 20px;
                height: 20px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }
            
            @keyframes spin {
                0% { transform: translate(-50%, -50%) rotate(0deg); }
                100% { transform: translate(-50%, -50%) rotate(360deg); }
            }
            
            .media-container {
                position: relative;
                overflow: hidden;
            }
            
            .thumbnail-loaded {
                filter: blur(2px);
                transition: filter 0.3s ease;
            }
            
            .full-loaded {
                filter: none;
            }
            
            .preview-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .preview-modal.show {
                opacity: 1;
                visibility: visible;
            }
            
            .preview-modal-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
            }
            
            .preview-modal-content {
                position: relative;
                width: 90%;
                max-width: 1200px;
                margin: 2% auto;
                background: white;
                border-radius: 8px;
                overflow: hidden;
            }
            
            .preview-modal-header {
                padding: 1rem;
                background: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .preview-modal-body {
                padding: 1rem;
                text-align: center;
                min-height: 200px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .preview-modal-footer {
                padding: 1rem;
                background: #f8f9fa;
                border-top: 1px solid #dee2e6;
                text-align: right;
            }
            
            .preview-loading {
                text-align: center;
            }
            
            .preview-loading .spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 1rem;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Show loading spinner
     */
    showLoadingSpinner(element) {
        const container = element.closest('.media-container') || element.parentNode;
        if (!container.querySelector('.loading-spinner')) {
            const spinner = document.createElement('div');
            spinner.className = 'loading-spinner';
            container.style.position = 'relative';
            container.appendChild(spinner);
        }
    }

    /**
     * Hide loading spinner
     */
    hideLoadingSpinner(element) {
        const container = element.closest('.media-container') || element.parentNode;
        const spinner = container.querySelector('.loading-spinner');
        if (spinner) {
            spinner.remove();
        }
    }

    /**
     * Show error placeholder
     */
    showErrorPlaceholder(element) {
        element.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDJMMTMuMDkgOC4yNkwyMCA5TDEzLjA5IDE1Ljc0TDEyIDIyTDEwLjkxIDE1Ljc0TDQgOUwxMC45MSA4LjI2TDEyIDJaIiBzdHJva2U9IiNjY2MiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo=';
        this.hideLoadingSpinner(element);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PreviewOptimizer();
});

// Export for manual initialization
window.PreviewOptimizer = PreviewOptimizer;