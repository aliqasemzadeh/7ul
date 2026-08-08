// for Flexilla Only
import './sidebar-plugin.js';
import { OffcanvasPlugin } from './plugins/offcanvas';

document.addEventListener('alpine:init', () => {
    Alpine.plugin(OffcanvasPlugin);
});
