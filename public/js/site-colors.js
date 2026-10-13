(() => {
    // Keep existing utility names (including Alpine state classes) on the login palette.
    const red = {
        50: '#fff0f2', 100: '#fce2e6', 200: '#f1bac3', 300: '#df929e',
        400: '#c86b77', 500: '#c42e3e', 600: '#bd2637', 700: '#aa1f31',
        800: '#8f1d2d', 900: '#701c29', 950: '#431019',
    };
    const gold = {
        50: '#fffaf2', 100: '#fff2dd', 200: '#e5d0a7', 300: '#dbc59e',
        400: '#b28d4a', 500: '#946b2a', 600: '#806027', 700: '#705024',
        800: '#59401f', 900: '#46321c', 950: '#2c1e11',
    };
    const neutral = {
        50: '#fffdf9', 100: '#f7f2eb', 200: '#e5d8c4', 300: '#c9b99f',
        400: '#82705e', 500: '#756454', 600: '#655547', 700: '#594938',
        800: '#332a23', 900: '#2a211b', 950: '#1c1611',
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
                    emerald: gold,
                    amber: gold,
                    sky: gold,
                },
            },
        },
    };
})();
