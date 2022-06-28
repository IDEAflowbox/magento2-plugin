define([
    'jquery',
    'ideaflowbox_slider',
], function ($) {
    "use strict";

    fetch('/cyberkonsultant/frontend/recommendationframe')
        .then((res) => res.json())
        .then((json) => {
            const frames = json.frames.data;
            frames.map((frame) => window.Flowbox.init(frame));
        })
        .catch(console.error);
});
