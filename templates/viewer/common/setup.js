

define(['common/viewer','common/keybindings'],
    function (viewer,keybindings) {
        keybindings(viewer);
        return {
            viewer: viewer
        }
    });
