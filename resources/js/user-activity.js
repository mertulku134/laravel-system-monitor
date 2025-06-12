class UserActivityTracker {
    constructor() {
        this.events = [];
        this.startTime = Date.now();
        this.sessionId = this.generateSessionId();
        this.init();
    }

    init() {
        // Sayfa yükleme
        this.trackPageView();
        
        // Tıklama olayları
        document.addEventListener('click', this.trackClick.bind(this));
        
        // Form gönderimleri
        document.addEventListener('submit', this.trackFormSubmit.bind(this));
        
        // AJAX çağrıları
        this.trackAjaxCalls();
        
        // Scroll olayları
        this.trackScroll();
        
        // Hata yakalama
        window.addEventListener('error', this.trackError.bind(this));
        
        // Periyodik olarak verileri gönder
        setInterval(this.sendToServer.bind(this), 30000); // Her 30 saniyede bir
    }

    generateSessionId() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    trackPageView() {
        this.events.push({
            type: 'page_view',
            url: window.location.href,
            title: document.title,
            timestamp: Date.now()
        });
    }

    trackClick(event) {
        // Gereksiz tıklamaları filtrele
        if (event.target.tagName === 'HTML' || event.target.tagName === 'BODY') {
            return;
        }

        this.events.push({
            type: 'click',
            element: event.target.tagName,
            id: event.target.id,
            class: event.target.className,
            text: event.target.textContent?.trim().substring(0, 100),
            timestamp: Date.now()
        });
    }

    trackFormSubmit(event) {
        this.events.push({
            type: 'form_submit',
            formId: event.target.id,
            formAction: event.target.action,
            timestamp: Date.now()
        });
    }

    trackAjaxCalls() {
        const originalXHR = window.XMLHttpRequest;
        window.XMLHttpRequest = function() {
            const xhr = new originalXHR();
            const originalOpen = xhr.open;
            const originalSend = xhr.send;
            
            xhr.open = function() {
                this.method = arguments[0];
                this.url = arguments[1];
                originalOpen.apply(xhr, arguments);
            };
            
            xhr.send = function() {
                const startTime = Date.now();
                xhr.addEventListener('load', function() {
                    this.events.push({
                        type: 'ajax',
                        method: xhr.method,
                        url: xhr.url,
                        status: xhr.status,
                        duration: Date.now() - startTime,
                        timestamp: Date.now()
                    });
                }.bind(this));
                originalSend.apply(xhr, arguments);
            };
            
            return xhr;
        }.bind(this);
    }

    trackScroll() {
        let lastScroll = 0;
        let scrollTimeout;
        
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                const currentScroll = window.pageYOffset;
                if (Math.abs(currentScroll - lastScroll) > 100) {
                    this.events.push({
                        type: 'scroll',
                        depth: currentScroll,
                        maxDepth: document.documentElement.scrollHeight,
                        timestamp: Date.now()
                    });
                    lastScroll = currentScroll;
                }
            }, 150);
        });
    }

    trackError(error) {
        this.events.push({
            type: 'error',
            message: error.message,
            source: error.filename,
            line: error.lineno,
            column: error.colno,
            timestamp: Date.now()
        });
    }

    sendToServer() {
        if (this.events.length > 0) {
            const data = {
                events: this.events,
                sessionId: this.sessionId,
                pageLoadTime: Date.now() - this.startTime
            };

            fetch('/api/user-activity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            }).then(() => {
                this.events = [];
            }).catch(() => {
                // Hata durumunda verileri sakla ve sonra tekrar dene
                const storedEvents = JSON.parse(localStorage.getItem('userActivityEvents') || '[]');
                storedEvents.push(...this.events);
                localStorage.setItem('userActivityEvents', JSON.stringify(storedEvents));
                this.events = [];
            });
        }

        // Saklanan verileri kontrol et ve gönder
        const storedEvents = JSON.parse(localStorage.getItem('userActivityEvents') || '[]');
        if (storedEvents.length > 0) {
            fetch('/api/user-activity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    events: storedEvents,
                    sessionId: this.sessionId,
                    pageLoadTime: Date.now() - this.startTime
                })
            }).then(() => {
                localStorage.removeItem('userActivityEvents');
            });
        }
    }
}

// Tracker'ı başlat
window.userActivityTracker = new UserActivityTracker(); 