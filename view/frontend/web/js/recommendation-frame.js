define([
  'jquery',
  'ideaflowbox_slider',
], function ($, Flowbox) {
  "use strict";

  fetch('/cyberkonsultant/frontend/recommendationframe', { method: 'POST' })
    .then((res) => res.json())
    .then((json) => {
      const frames = json.frames.data;
      frames.map((frame) => {
        frame.products = frame.products.map((p) => ({
          ...p,
          url: p.url + '?frame_id=' + frame.id + '&source=recommendation_frame&ts=' + (new Date()).getTime(),
        }));
        return frame;
      }).map((frame) => Flowbox.init(frame));
    })
    .catch(console.error);
});
