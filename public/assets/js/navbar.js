/**
 * Overlay navigation: hamburger toggle and in-page smooth scrolling.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('.toggle');
        var menu = document.querySelector('.menu-items');

        if (toggle && menu) {
            initToggle(toggle, menu);
        }

        initSmoothScroll(toggle, menu);
    });

    function initToggle(toggle, menu) {
        toggle.addEventListener('click', function () {
            toggle.classList.toggle('active');

            if (toggle.classList.contains('active')) {
                menu.classList.add('open');
                menu.classList.remove('close');
                return;
            }

            menu.classList.add('close');
            menu.classList.remove('open');

            // Let the panel slide off-screen, then park it above the viewport
            // ready for the next open without animating back through the page.
            setTimeout(function () {
                menu.classList.add('nav-opacity');
                menu.classList.remove('close');

                setTimeout(function () {
                    menu.classList.remove('nav-opacity');
                }, 500);
            }, 500);
        });
    }

    /**
     * Intercept only links pointing at a section of the current page.
     *
     * Cross-page links such as "/#works" are left alone so the browser
     * navigates normally — passing one to querySelector() throws, which
     * previously left those links dead on the contact page.
     */
    function initSmoothScroll(toggle, menu) {
        document.querySelectorAll('.nav-item, .menu-item').forEach(function (link) {
            link.addEventListener('click', function (event) {
                var href = link.getAttribute('href') || '';

                if (href.charAt(0) !== '#') {
                    return;
                }

                var target = document.querySelector(href);

                if (!target) {
                    return;
                }

                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                closeMenu(toggle, menu);
            });
        });
    }

    function closeMenu(toggle, menu) {
        if (!toggle || !menu) {
            return;
        }

        toggle.classList.remove('active');
        menu.classList.add('closeTop');
        menu.classList.remove('open');

        setTimeout(function () {
            menu.classList.remove('closeTop');
        }, 500);
    }
}());
