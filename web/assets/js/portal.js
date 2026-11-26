/**
 * Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 * This file is part of GunGame AMXX 3++ - pur3gaming Edition.
 *
 * See LICENSE for the full license text.
 */

/* Keep anchor offsets synchronized with the responsive sticky header. */
(() => {
    'use strict';
    const header = document.getElementById('portal-header');
    const topButton = document.getElementById('back-to-top');
    const language = document.getElementById('portal-language');
    const updateHeader = () => document.documentElement.style.setProperty('--portal-header-height', `${header.offsetHeight + 12}px`);
    const updateTopButton = () => { topButton.hidden = window.scrollY < 300; };
    updateHeader();
    updateTopButton();
    window.addEventListener('resize', updateHeader);
    if ('ResizeObserver' in window) new ResizeObserver(updateHeader).observe(header);
    window.addEventListener('scroll', updateTopButton, {passive: true});
    topButton.addEventListener('click', () => window.scrollTo({top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'}));
    language.addEventListener('change', () => language.form.submit());
})();
