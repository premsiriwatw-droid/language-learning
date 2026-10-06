(() => {
    const world = document.querySelector('[data-language-world]');
    const toggle = document.querySelector('[data-motion-toggle]');
    if (!world || !toggle) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
    let paused = false;
    let frame;

    const updateMotion = () => {
        const stopped = paused || reducedMotion.matches || document.hidden;
        world.classList.toggle('is-motion-enabled', !stopped);
        world.classList.toggle('is-motion-paused', stopped);
        toggle.hidden = reducedMotion.matches;
        toggle.setAttribute('aria-pressed', String(paused));
        toggle.querySelector('[data-motion-icon]').textContent = paused ? '▷' : 'Ⅱ';
        toggle.querySelector('[data-motion-label]').textContent = paused ? 'เปิดภาพเคลื่อนไหว' : 'หยุดภาพเคลื่อนไหว';
        if (stopped) {
            cancelAnimationFrame(frame);
            world.style.removeProperty('--scene-x');
            world.style.removeProperty('--scene-y');
        }
    };

    toggle.addEventListener('click', () => {
        paused = !paused;
        updateMotion();
    });
    reducedMotion.addEventListener('change', updateMotion);
    document.addEventListener('visibilitychange', updateMotion);
    world.addEventListener('pointermove', (event) => {
        if (paused || reducedMotion.matches || !finePointer.matches) return;
        cancelAnimationFrame(frame);
        const bounds = world.getBoundingClientRect();
        const x = ((event.clientX - bounds.left) / bounds.width - 0.5) * 12;
        const y = ((event.clientY - bounds.top) / bounds.height - 0.5) * 8;
        frame = requestAnimationFrame(() => {
            world.style.setProperty('--scene-x', `${x.toFixed(1)}px`);
            world.style.setProperty('--scene-y', `${y.toFixed(1)}px`);
        });
    });
    world.addEventListener('pointerleave', () => {
        cancelAnimationFrame(frame);
        world.style.removeProperty('--scene-x');
        world.style.removeProperty('--scene-y');
    });
    updateMotion();
})();
