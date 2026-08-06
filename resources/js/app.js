function disableTransitionsTemporarily(callback) {
    const style = document.createElement('style');
    style.appendChild(
        document.createTextNode(
            '*,*::before,*::after{-webkit-transition:none!important;transition:none!important;animation:none!important}',
        ),
    );
    document.head.appendChild(style);

    callback();

    window.getComputedStyle(document.documentElement).getPropertyValue('opacity');

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            style.remove();
        });
    });
}

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        preference: 'system',
        isDark: false,

        init() {
            this.preference = localStorage.getItem('theme') || 'system';

            window
                .matchMedia('(prefers-color-scheme: dark)')
                .addEventListener('change', () => {
                    if (this.preference === 'system') {
                        this.updateState();
                    }
                });

            document.addEventListener('livewire:navigated', () => {
                this.applyToDom();
            });

            this.updateState();
        },

        setTheme(mode) {
            this.preference = mode;

            if (mode === 'system') {
                localStorage.removeItem('theme');
            } else {
                localStorage.setItem('theme', mode);
            }

            this.updateState();
        },

        toggle() {
            const nextMode = this.isDark ? 'light' : 'dark';
            this.setTheme(nextMode);
        },

        updateState() {
            const systemPrefersDark = window.matchMedia(
                '(prefers-color-scheme: dark)',
            ).matches;

            if (this.preference === 'system') {
                this.isDark = systemPrefersDark;
            } else {
                this.isDark = this.preference === 'dark';
            }

            this.applyToDom();
        },

        applyToDom() {
            disableTransitionsTemporarily(() => {
                this._syncDom();
            });
        },

        _syncDom() {
            document.documentElement.classList.toggle('dark', this.isDark);
            window.dispatchEvent(
                new CustomEvent('theme-changed', {
                    detail: {
                        preference: this.preference,
                        isDark: this.isDark,
                    },
                }),
            );
        },
    });
});
