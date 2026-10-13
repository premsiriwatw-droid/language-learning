(() => {
    // Keep existing utility names (including Alpine state classes) on the shared red and green palette.
    const red = {
        50: '#fff0f2', 100: '#fce2e6', 200: '#f1bac3', 300: '#df929e',
        400: '#c86b77', 500: '#c42e3e', 600: '#bd2637', 700: '#aa1f31',
        800: '#8f1d2d', 900: '#701c29', 950: '#431019',
    };
    const green = {
        50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
        400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
        800: '#065f46', 900: '#064e3b', 950: '#022c22',
    };
    const neutral = {
        50: '#fffdf9', 100: '#f0f4ee', 200: '#dfe8e2', 300: '#c8d6cc',
        400: '#74816e', 500: '#65746d', 600: '#4d695b', 700: '#3b574a',
        800: '#203932', 900: '#182f27', 950: '#102219',
    };

    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    gray: neutral,
                    blue: red,
                    indigo: red,
                    purple: red,
                    red,
                    rose: red,
                    emerald: green,
                    amber: green,
                    sky: green,
                },
            },
        },
    };
})();
