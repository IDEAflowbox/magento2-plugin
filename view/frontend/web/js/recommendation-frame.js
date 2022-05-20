define([
    'jquery',
    'mustache',
    'glider',
], function ($, mustache, Glider) {
    "use strict";

    try {
        fetch('/cyberkonsultant/frontend/recommendationframe')
            .then((res) => res.json())
            .then((json) => {
                const frames = json.frames.data;

                frames.map((frame) => {
                    try {
                        const el = document.evaluate(frame.xpath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                        const products = frame.products.map((p) => ({ ...p, url: p.url + '?source=recommendation_frame&ts=' + (new Date()).getTime() }));
                        $(el).append(mustache.render(frame.html, { products: products }));
                    } catch (e) {
                        console.log(e);
                    }
                });
            })
            .then(() => {
                const element = document.querySelector('.cyberkonsultant--glider');
                if (!element) {
                    return;
                }

                new Glider(element, {
                    slidesToShow: 4,
                    slidesToScroll: 4,
                    draggable: false,
                    dots: '.cyberkonsultant--glider-dots',
                    arrows: { prev: '.glider-prev', next: '.glider-next' },
                });
            })
            .catch(console.error);
    } catch (e) {
        console.error(e);
    }
});
