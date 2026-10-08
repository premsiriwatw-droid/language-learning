(() => {
    const page = document.querySelector('.register-page');
    const story = page?.querySelector('.register-story');
    const intro = story?.querySelector('[data-register-intro]');
    if (!intro) return;

    const panels = [...story.querySelectorAll('[data-curriculum-panel]')];
    const triggers = [...intro.querySelectorAll('[data-scene-trigger]')];
    const gust = story.querySelector('.curriculum-gust');
    const form = page.querySelector('#register-form');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const animations = new Set();
    let current = intro;
    let latest = null;
    let revision = 0;
    let returnTo = null;

    const canAnimate = () => !reducedMotion.matches && page.classList.contains('motion-ready');

    const cancelAnimations = () => {
        animations.forEach(animation => animation.cancel());
        animations.clear();
    };

    const animate = async (element, frames, options) => {
        const animation = element.animate(frames, options);
        animations.add(animation);
        try {
            await animation.finished;
        } catch {
            // A newer selection or motion preference replaces this transition.
        } finally {
            animation.cancel();
            animations.delete(animation);
        }
    };

    const selectScene = scene => {
        if (scene === 'default') delete page.dataset.curriculumScene;
        else page.dataset.curriculumScene = scene;
        page.dispatchEvent(new CustomEvent('auth:scene-select', { detail: { scene } }));
    };

    const reveal = (request, moveFocus) => {
        [intro, ...panels].forEach(panel => {
            panel.hidden = panel !== request.panel;
        });
        current = request.panel;
        story.setAttribute('aria-labelledby', current === intro ? 'story-heading' : current.getAttribute('aria-labelledby'));
        triggers.forEach(trigger => trigger.setAttribute('aria-expanded', String(trigger.dataset.sceneTrigger === request.scene)));
        current.querySelector('.curriculum-scroll')?.scrollTo(0, 0);

        if (moveFocus && !form.contains(document.activeElement)) {
            const destination = current === intro ? returnTo : current.querySelector('[data-curriculum-back]');
            destination?.focus({ preventScroll: true });
        }
        // Restoring focus can trigger the shared backdrop preview; keep this selection authoritative.
        selectScene(request.scene);
    };

    const settle = () => {
        const focusIsInPanel = current.contains(document.activeElement);
        revision++;
        cancelAnimations();
        if (latest) reveal(latest, focusIsInPanel);
        latest = null;
    };

    const replacePanel = async (panel, scene) => {
        const request = { panel, scene };
        const thisRevision = ++revision;
        cancelAnimations();
        latest = request;
        selectScene(scene);

        if (!canAnimate()) {
            reveal(request, true);
            latest = null;
            return;
        }

        void animate(gust, [
            { transform: 'translateX(-110%) rotate(-5deg)', opacity: 0 },
            { opacity: .85, offset: .4 },
            { transform: 'translateX(110%) rotate(3deg)', opacity: 0 },
        ], { duration: 760, easing: 'cubic-bezier(.2,.65,.25,1)' });

        await animate(current, [
            { transform: 'translateX(0) rotate(0)', opacity: 1, filter: 'blur(0)' },
            { transform: 'translateX(-28px) rotate(-.8deg)', opacity: 0, filter: 'blur(3px)' },
        ], { duration: 200, easing: 'ease-in', fill: 'forwards' });
        if (thisRevision !== revision) return;

        reveal(request, true);
        await animate(panel, [
            { transform: 'translateX(38px) rotate(.6deg)', opacity: 0, filter: 'blur(3px)' },
            { transform: 'translateX(0) rotate(0)', opacity: 1, filter: 'blur(0)' },
        ], { duration: 430, easing: 'cubic-bezier(.18,.75,.22,1)' });
        if (thisRevision !== revision) return;

        current = panel;
        cancelAnimations();
        latest = null;
    };

    triggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const scene = trigger.dataset.sceneTrigger;
            const panel = panels.find(candidate => candidate.dataset.curriculumPanel === scene);
            if (!panel) return;
            returnTo = trigger;
            void replacePanel(panel, scene);
        });
    });

    panels.forEach(panel => {
        panel.querySelector('[data-curriculum-back]')?.addEventListener('click', () => {
            void replacePanel(intro, 'default');
        });
    });

    page.querySelector('[data-motion-toggle]')?.addEventListener('click', () => {
        if (!canAnimate()) settle();
    });
    reducedMotion.addEventListener('change', () => {
        if (reducedMotion.matches) settle();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) settle();
    });
    window.addEventListener('pagehide', settle);
})();
