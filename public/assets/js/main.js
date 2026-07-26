/**
 * Site behaviour: custom cursor, sticky chrome, live clock, scroll animations.
 *
 * Depends on GSAP + ScrollTrigger and SplitType, both loaded before this file.
 * Every lookup is guarded so a page that omits a section (the contact page has
 * no portfolio grid, for instance) does not throw and abort the rest.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initCursor();
        initBackToTop();
        initStickyChrome();
        initClock();
        initTestimonialHoverPause();
        initRotatingWords();
        initScrollAnimations();
    });

    /* ── Custom cursor ──────────────────────────────────────────────────── */

    function initCursor() {
        var cursor = document.querySelector('.cursor');

        if (!cursor) {
            return;
        }

        document.addEventListener('mousemove', function (event) {
            cursor.style.left = event.clientX + 'px';
            cursor.style.top = event.clientY + 'px';
        });

        function grow() {
            cursor.style.transform = 'scale(2) translate(-25%, -25%)';
        }

        function shrink() {
            cursor.style.transform = 'scale(1) translate(-50%, -50%)';
        }

        document.querySelectorAll('h1, a').forEach(function (element) {
            element.addEventListener('mouseenter', grow);
            element.addEventListener('mouseleave', shrink);
        });
    }

    /* ── Back-to-top button ─────────────────────────────────────────────── */

    function initBackToTop() {
        var button = document.getElementById('bodyTop');

        if (!button) {
            return;
        }

        button.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ── Sticky header + back-to-top visibility ─────────────────────────── */

    function initStickyChrome() {
        var header = document.getElementById('header');
        var button = document.getElementById('bodyTop');

        if (!header && !button) {
            return;
        }

        // Scroll fires constantly; only touch the DOM when the state flips.
        var isSticky = null;

        function sync() {
            var shouldStick = window.scrollY > 200;

            if (shouldStick === isSticky) {
                return;
            }

            isSticky = shouldStick;

            if (header) {
                header.classList.toggle('sticky', shouldStick);
            }

            if (button) {
                button.classList.toggle('sticky', shouldStick);
            }
        }

        window.addEventListener('scroll', sync, { passive: true });
        sync();
    }

    /* ── Live clock ─────────────────────────────────────────────────────── */

    function initClock() {
        var output = document.getElementById('time');

        if (!output) {
            return;
        }

        var formatter = new Intl.DateTimeFormat('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
            timeZone: output.dataset.timezone || undefined
        });

        function tick() {
            // Match the original "H : MM AM" spacing.
            output.textContent = formatter.format(new Date()).replace(':', ' : ');
        }

        tick();
        setInterval(tick, 1000);
    }

    /* ── Testimonial marquee ────────────────────────────────────────────── */

    function initTestimonialHoverPause() {
        document.querySelectorAll('.testimonial-row').forEach(function (row) {
            row.addEventListener('mouseenter', function () {
                row.style.animationPlayState = 'paused';
            });

            row.addEventListener('mouseleave', function () {
                row.style.animationPlayState = 'running';
            });
        });
    }

    /* ── Rotating hero words ────────────────────────────────────────────── */

    function initRotatingWords() {
        var words = document.querySelectorAll('.word');

        if (words.length === 0) {
            return;
        }

        words.forEach(function (word) {
            var letters = word.textContent.split('');
            word.textContent = '';

            letters.forEach(function (letter) {
                var span = document.createElement('span');
                span.textContent = letter;
                span.className = 'letter';
                word.append(span);
            });
        });

        var currentIndex = 0;
        var maxIndex = words.length - 1;
        words[currentIndex].style.opacity = '1';

        function rotate() {
            var current = words[currentIndex];
            var next = currentIndex === maxIndex ? words[0] : words[currentIndex + 1];

            Array.from(current.children).forEach(function (letter, i) {
                setTimeout(function () {
                    letter.className = 'letter out';
                }, i * 80);
            });

            next.style.opacity = '1';

            Array.from(next.children).forEach(function (letter, i) {
                letter.className = 'letter behind';
                setTimeout(function () {
                    letter.className = 'letter in';
                }, 340 + i * 80);
            });

            currentIndex = currentIndex === maxIndex ? 0 : currentIndex + 1;
        }

        rotate();
        setInterval(rotate, 3000);
    }

    /* ── Scroll-triggered animation ─────────────────────────────────────── */

    function initScrollAnimations() {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
            return;
        }

        gsap.registerPlugin(ScrollTrigger);

        revealSectionHeadings();
        staggerIn('.portfolio-card', { opacity: 0, y: 50 }, { opacity: 1, y: 0 }, 0.1);
        staggerIn('.experience-item', { opacity: 0, y: 30 }, { opacity: 1, y: 0 }, 0.1);
        staggerIn('.stack-item', { opacity: 0, x: 30 }, { opacity: 1, x: 0 }, 0.2);
        animateFooter();
    }

    /**
     * Split each section heading into characters and slide them up on scroll.
     */
    function revealSectionHeadings() {
        if (typeof SplitType === 'undefined') {
            return;
        }

        var selectors = [
            '#text-work',
            '#text-about',
            '#text-service',
            '#text-testimonial',
            '#text-experience',
            '#text-stack'
        ];

        selectors.forEach(function (selector) {
            if (!document.querySelector(selector)) {
                return;
            }

            new SplitType(selector);

            gsap.to(selector + ' .char', {
                y: 0,
                stagger: 0.15,
                delay: 0.2,
                duration: 0.1,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: selector,
                    start: 'bottom bottom',
                    end: 'center 80%',
                    scrub: 0.2
                }
            });
        });
    }

    /**
     * Fade a set of cards in one after another as they enter the viewport.
     */
    function staggerIn(selector, from, to, stagger) {
        var items = gsap.utils.toArray(selector);

        if (items.length === 0) {
            return;
        }

        items.forEach(function (item, index) {
            gsap.fromTo(item, from, Object.assign({}, to, {
                duration: 1,
                delay: index * stagger,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: item,
                    start: 'top bottom-=100',
                    end: 'bottom center',
                    toggleActions: 'play none none reverse'
                }
            }));
        });
    }

    function animateFooter() {
        if (!document.querySelector('.footer-section')) {
            return;
        }

        var trigger = {
            trigger: '.footer-section',
            start: 'center 90%',
            end: 'center 70%',
            scrub: 1
        };

        gsap.to('.brand-text', { scrollTrigger: trigger, y: 0, duration: 1, ease: 'power2.out' });
        gsap.to('.green-circle', { scrollTrigger: trigger, scale: 2, duration: 1, ease: 'power2.out' });
    }
}());
