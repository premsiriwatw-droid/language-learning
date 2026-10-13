(() => {
    const page = document.querySelector('.login-page');
    if (!page) return;

    const explorer = page.querySelector('[data-explorer]');
    const homeView = explorer?.querySelector('[data-explorer-home]');
    const outlines = [...(explorer?.querySelectorAll('[data-outline]') || [])];
    const triggers = [...page.querySelectorAll('[data-scene-trigger]')];
    const caption = page.querySelector('[data-scene-name]');
    const book = page.querySelector('[data-book]');
    const motionToggle = page.querySelector('[data-motion-toggle]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const sceneNames = {
        default: 'เลือกภาษา แล้วเริ่มบทใหม่ไปด้วยกัน',
        china: 'SHANGHAI, CHINA / SUNRISE',
        england: 'LONDON, ENGLAND / GOLDEN HOUR',
    };
    let selectedScene = 'default';
    let hoveredScene = null;
    let focusedScene = null;
    let paused = false;
    let bookInterval;
    let closeTimeout;
    let activeView = homeView;
    let returnTrigger;
    let transitionVersion = 0;
    let currentAnimation;
    let restoringFocus = false;

    const motionStopped = () => paused || reducedMotion.matches || document.hidden;

    const renderScene = () => {
        const scene = selectedScene !== 'default' ? selectedScene : hoveredScene || focusedScene || 'default';
        page.dataset.scene = scene;
        if (caption) caption.textContent = sceneNames[scene];
        triggers.forEach(trigger => {
            trigger.setAttribute('aria-expanded', String(trigger.dataset.sceneTrigger === selectedScene));
        });
    };

    const stopBook = () => {
        window.clearInterval(bookInterval);
        window.clearTimeout(closeTimeout);
        book?.classList.remove('is-open');
    };

    const finishAnimation = () => {
        if (currentAnimation?.playState === 'running') currentAnimation.finish();
    };

    const updateMotion = () => {
        const stopped = motionStopped();
        page.classList.toggle('motion-ready', !stopped);
        page.classList.toggle('is-motion-paused', stopped);
        if (motionToggle) {
            motionToggle.hidden = reducedMotion.matches;
            motionToggle.setAttribute('aria-pressed', String(paused));
            const icon = motionToggle.querySelector('[data-motion-icon]');
            const label = motionToggle.querySelector('[data-motion-label]');
            if (icon) icon.textContent = paused ? '▷' : 'Ⅱ';
            if (label) label.textContent = paused ? 'เปิดภาพเคลื่อนไหว' : 'หยุดภาพเคลื่อนไหว';
        }
        if (stopped) finishAnimation();
        stopBook();

        if (book && !stopped && selectedScene === 'default') {
            bookInterval = window.setInterval(() => {
                book.classList.add('is-open');
                closeTimeout = window.setTimeout(() => book.classList.remove('is-open'), 2600);
            }, 10000);
        }
    };

    const animateView = async (view, frames, duration) => {
        if (motionStopped() || typeof view.animate !== 'function') return;
        const animation = view.animate(frames, {
            duration,
            easing: 'cubic-bezier(.22, .7, .22, 1)',
            fill: 'both',
        });
        currentAnimation = animation;
        try {
            await animation.finished;
        } catch {
            // A newer navigation can cancel this transition without changing the form.
        } finally {
            animation.cancel();
            if (currentAnimation === animation) currentAnimation = null;
        }
    };

    const focusView = (view) => {
        if (view === homeView) {
            restoringFocus = true;
            returnTrigger?.focus({ preventScroll: true });
            restoringFocus = false;
        } else {
            view.querySelector('[data-outline-heading]')?.focus({ preventScroll: true });
        }
    };

    const switchView = async (scene, trigger) => {
        if (!explorer || !homeView) return;
        const nextView = scene === 'default' ? homeView : outlines.find(outline => outline.dataset.outline === scene);
        if (!nextView || scene === selectedScene) return;

        const version = ++transitionVersion;
        currentAnimation?.cancel();
        if (trigger) returnTrigger = trigger;
        selectedScene = scene;
        hoveredScene = null;
        focusedScene = null;
        renderScene();
        updateMotion();

        const opening = scene !== 'default';
        explorer.dataset.direction = opening ? 'open' : 'close';
        explorer.classList.add('is-switching');
        const outgoing = activeView;
        if (outgoing !== nextView) {
            await animateView(outgoing, [
                { opacity: 1, transform: 'translateX(0) rotate(0deg)' },
                { opacity: 0, transform: `translateX(${opening ? '-24px' : '24px'}) rotate(${opening ? '-.6deg' : '.6deg'})` },
            ], 190);
            if (version !== transitionVersion) return;
            outgoing.hidden = true;
            outgoing.inert = true;
            nextView.hidden = false;
            nextView.inert = false;
            activeView = nextView;
            focusView(nextView);
            await animateView(nextView, [
                { opacity: 0, transform: `translateX(${opening ? '30px' : '-30px'}) rotate(${opening ? '.6deg' : '-.6deg'})` },
                { opacity: 1, transform: 'translateX(0) rotate(0deg)' },
            ], 410);
            if (version !== transitionVersion) return;
        } else {
            focusView(nextView);
        }
        explorer.classList.remove('is-switching');
        delete explorer.dataset.direction;
    };

    triggers.forEach(trigger => {
        const scene = trigger.dataset.sceneTrigger;
        if (homeView && outlines.some(outline => outline.dataset.outline === scene)) trigger.disabled = false;
        trigger.addEventListener('pointerenter', event => {
            if (event.pointerType === 'touch' || explorer?.classList.contains('is-switching')) return;
            hoveredScene = scene;
            renderScene();
        });
        trigger.addEventListener('pointerleave', () => {
            if (hoveredScene === scene) hoveredScene = null;
            renderScene();
        });
        trigger.addEventListener('focus', () => {
            if (!restoringFocus && !explorer?.classList.contains('is-switching') && trigger.matches(':focus-visible')) {
                focusedScene = scene;
            }
            renderScene();
        });
        trigger.addEventListener('blur', () => {
            if (focusedScene === scene) focusedScene = null;
            renderScene();
        });
        trigger.addEventListener('click', () => switchView(scene, trigger));
    });

    explorer?.querySelectorAll('[data-outline-back]').forEach(button => {
        button.addEventListener('click', () => switchView('default'));
    });
    explorer?.addEventListener('keydown', event => {
        if (event.key === 'Escape' && selectedScene !== 'default') {
            event.preventDefault();
            switchView('default');
        }
    });
    motionToggle?.addEventListener('click', () => {
        paused = !paused;
        updateMotion();
    });
    reducedMotion.addEventListener('change', updateMotion);
    document.addEventListener('visibilitychange', updateMotion);
    window.addEventListener('pagehide', () => {
        stopBook();
        finishAnimation();
    });
    window.addEventListener('pageshow', updateMotion);
    renderScene();
    updateMotion();
})();
