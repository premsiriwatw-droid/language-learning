(() => {
    const page = document.querySelector('.login-page');
    if (!page) return;

    const triggers = [...page.querySelectorAll('[data-scene-trigger]')];
    const caption = page.querySelector('[data-scene-name]');
    const book = page.querySelector('[data-book]');
    const motionToggle = page.querySelector('[data-motion-toggle]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const sceneNames = {
        default: 'ONE WORD. ENDLESS POSSIBILITIES.',
        china: 'SHANGHAI, CHINA / SUNRISE',
        england: 'LONDON, ENGLAND / GOLDEN HOUR',
    };
    let selectedScene = 'default';
    let hoveredScene = null;
    let focusedScene = null;
    let paused = false;
    let bookInterval;
    let closeTimeout;

    const renderScene = () => {
        const scene = hoveredScene || focusedScene || selectedScene;
        page.dataset.scene = scene;
        caption.textContent = sceneNames[scene];
        triggers.forEach(trigger => {
            trigger.setAttribute('aria-pressed', String(trigger.dataset.sceneTrigger === selectedScene));
        });
    };

    triggers.forEach(trigger => {
        const scene = trigger.dataset.sceneTrigger;
        trigger.addEventListener('pointerenter', event => {
            if (event.pointerType === 'touch') return;
            hoveredScene = scene;
            renderScene();
        });
        trigger.addEventListener('pointerleave', () => {
            hoveredScene = null;
            renderScene();
        });
        trigger.addEventListener('focus', () => {
            // Keyboard focus previews a scene; pointer clicks select it below.
            if (trigger.matches(':focus-visible')) focusedScene = scene;
            renderScene();
        });
        trigger.addEventListener('blur', () => {
            focusedScene = null;
            renderScene();
        });
        trigger.addEventListener('click', () => {
            selectedScene = selectedScene === scene ? 'default' : scene;
            renderScene();
        });
    });

    const stopBook = () => {
        window.clearInterval(bookInterval);
        window.clearTimeout(closeTimeout);
        book.classList.remove('is-open');
    };

    const updateMotion = () => {
        const stopped = paused || reducedMotion.matches || document.hidden;
        page.classList.toggle('motion-ready', !stopped);
        page.classList.toggle('is-motion-paused', stopped);
        motionToggle.hidden = reducedMotion.matches;
        motionToggle.setAttribute('aria-pressed', String(paused));
        motionToggle.querySelector('[data-motion-icon]').textContent = paused ? '▷' : 'Ⅱ';
        motionToggle.querySelector('[data-motion-label]').textContent = paused ? 'เปิดภาพเคลื่อนไหว' : 'หยุดภาพเคลื่อนไหว';
        stopBook();

        if (!stopped) {
            bookInterval = window.setInterval(() => {
                book.classList.add('is-open');
                closeTimeout = window.setTimeout(() => book.classList.remove('is-open'), 2600);
            }, 10000);
        }
    };

    motionToggle.addEventListener('click', () => {
        paused = !paused;
        updateMotion();
    });
    reducedMotion.addEventListener('change', updateMotion);
    document.addEventListener('visibilitychange', updateMotion);
    window.addEventListener('pagehide', stopBook);
    window.addEventListener('pageshow', updateMotion);
    renderScene();
    updateMotion();
})();
